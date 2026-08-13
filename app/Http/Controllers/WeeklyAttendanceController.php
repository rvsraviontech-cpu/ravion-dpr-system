<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\AttendanceStatus;
use App\Models\Labour;
use App\Models\LabourAttendance;
use App\Models\LabourAttendanceDetail;
use App\Models\Project;
use App\Models\WorkingStatus;
use App\Support\ProjectAccess;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class WeeklyAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeManager();

        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek(Carbon::SUNDAY)
            : now()->startOfWeek(Carbon::SUNDAY);

        $projectId = $request->filled('project_id') ? $request->integer('project_id') : null;
        $project = $projectId ? Project::query()->findOrFail($projectId) : null;

        if ($project) {
            ProjectAccess::authorize((int) $project->id);
        }

        $days = collect(range(0, 6))->map(fn (int $offset) => $weekStart->copy()->addDays($offset));
        $labours = collect();
        $existingByDate = collect();
        $lockedDates = [];

        if ($project) {
            $attendances = LabourAttendance::query()
                ->where('project_id', $project->id)
                ->whereBetween('attendance_date', [$weekStart->toDateString(), $weekStart->copy()->addDays(6)->toDateString()])
                ->where('is_active', true)
                ->with(['details.labour.labourGroup', 'details.attendanceStatus'])
                ->get()
                ->keyBy(fn (LabourAttendance $a) => $a->attendance_date->toDateString());

            foreach ($attendances as $dateKey => $attendance) {
                $existingByDate[$dateKey] = $attendance->details->keyBy('labour_id');
                if (in_array($attendance->status, ['submitted', 'approved'], true)) {
                    $lockedDates[$dateKey] = $attendance->display_status;
                }
            }

            $historicLabourIds = $attendances->flatMap(fn ($attendance) => $attendance->details->pluck('labour_id'))->unique();

            $labours = Labour::query()
                ->active()
                ->whereNotIn('employment_status', ['exited', 'suspended'])
                ->where(function (Builder $query) use ($project, $historicLabourIds): void {
                    $query->where('current_project_id', $project->id)
                        ->orWhereIn('id', $historicLabourIds);
                })
                ->with(['labourGroup', 'designationRole', 'defaultShift'])
                ->get()
                ->sortBy([
                    fn (Labour $l) => $l->labourGroup?->sort_order ?? 999999,
                    fn (Labour $l) => $l->labourGroup?->name ?? 'ZZZ Un-grouped',
                    fn (Labour $l) => $l->full_name,
                ])
                ->values();
        }

        return view('weekly-attendance.index', [
            'projects' => ProjectAccess::availableProjects(),
            'project' => $project,
            'weekStart' => $weekStart,
            'days' => $days,
            'labours' => $labours,
            'existingByDate' => $existingByDate,
            'lockedDates' => $lockedDates,
            'statuses' => AttendanceStatus::query()->active()->ordered()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'week_start' => ['required', 'date'],
            'attendance' => ['nullable', 'array'],
            'attendance.*.*' => ['nullable', 'integer', 'exists:attendance_statuses,id'],
        ]);

        $projectId = (int) $validated['project_id'];
        ProjectAccess::authorize($projectId);
        $weekStart = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::SUNDAY);
        $submitted = collect($validated['attendance'] ?? []);

        $statuses = AttendanceStatus::query()->active()->get()->keyBy('id');
        $working = WorkingStatus::query()->active()->get();
        $defaultWorking = $working->first(fn ($s) => in_array(strtoupper((string) $s->code), ['W', 'WORKING'], true))
            ?? $working->first(fn ($s) => str_contains(strtoupper((string) $s->name), 'WORKING'));
        $partialWorking = $working->first(fn ($s) => str_contains(strtoupper((string) $s->code), 'PART'))
            ?? $working->first(fn ($s) => str_contains(strtoupper((string) $s->name), 'PART'));

        $labourIds = $submitted->flatMap(fn ($rows) => array_keys((array) $rows))->map(fn ($id) => (int) $id)->unique();
        $labours = Labour::query()->whereIn('id', $labourIds)->with('defaultShift')->get()->keyBy('id');
        $changedDays = 0;

        DB::transaction(function () use ($submitted, $weekStart, $projectId, $statuses, $defaultWorking, $partialWorking, $labours, &$changedDays): void {
            foreach (range(0, 6) as $offset) {
                $date = $weekStart->copy()->addDays($offset);
                if ($date->isFuture()) {
                    continue;
                }

                $dateKey = $date->toDateString();
                $dayRows = collect((array) $submitted->get($dateKey, []))->filter();
                if ($dayRows->isEmpty()) {
                    continue;
                }

                $attendance = LabourAttendance::query()
                    ->where('project_id', $projectId)
                    ->whereDate('attendance_date', $dateKey)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if ($attendance && in_array($attendance->status, ['submitted', 'approved'], true)) {
                    continue;
                }

                if (! $attendance) {
                    $attendance = LabourAttendance::create([
                        'attendance_number' => $this->generateAttendanceNumber($dateKey),
                        'project_id' => $projectId,
                        'attendance_date' => $dateKey,
                        'shift_id' => null,
                        'status' => 'draft',
                        'recorded_by' => auth()->id(),
                        'remarks' => 'Weekly attendance entered by Admin/PMO.',
                        'is_active' => true,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                }

                foreach ($dayRows as $labourId => $statusId) {
                    $labour = $labours->get((int) $labourId);
                    $status = $statuses->get((int) $statusId);
                    if (! $labour || ! $status) {
                        continue;
                    }

                    $existing = LabourAttendanceDetail::withTrashed()
                        ->where('labour_attendance_id', $attendance->id)
                        ->where('labour_id', $labour->id)
                        ->first();

                    if ($existing?->trashed()) {
                        $existing->restore();
                    }

                    $detail = $existing ?: new LabourAttendanceDetail([
                        'labour_attendance_id' => $attendance->id,
                        'labour_id' => $labour->id,
                        'created_by' => auth()->id(),
                    ]);

                    if (! $existing) {
                        foreach (LabourAttendanceDetail::snapshotFromLabour($labour) as $column => $value) {
                            $detail->{$column} = $value;
                        }
                    }

                    $rules = $this->statusDefaults($status, $labour, $defaultWorking?->id, $partialWorking?->id);
                    $detail->fill([
                        'attendance_status_id' => $status->id,
                        'working_status_id' => $rules['working_status_id'],
                        'check_in_time' => $rules['check_in_time'],
                        'check_out_time' => $rules['check_out_time'],
                        'normal_hours' => $rules['normal_hours'],
                        'ot_hours' => 0,
                        'attendance_source' => 'system',
                        'remarks' => $detail->remarks,
                        'is_active' => true,
                        'updated_by' => auth()->id(),
                    ]);
                    $detail->save();
                }

                $attendance->recalculateSummary();
                $changedDays++;

                AuditHelper::log('Weekly Labour Attendance', 'Updated', LabourAttendance::class, $attendance->id,
                    "Weekly bulk attendance updated '{$attendance->attendance_number}'.", null,
                    ['attendance_date' => $dateKey, 'project_id' => $projectId, 'total_labours' => $attendance->total_labours]);
            }
        });

        return redirect()->route('weekly-attendance.index', [
            'project_id' => $projectId,
            'week_start' => $weekStart->toDateString(),
        ])->with('success', "Weekly attendance saved. {$changedDays} day(s) updated. Submitted/Approved days were left locked.");
    }

    private function statusDefaults(AttendanceStatus $status, Labour $labour, ?int $workingId, ?int $partialId): array
    {
        $code = strtoupper(trim((string) ($status->code ?: $status->short_name ?: $status->name)));
        $shift = $labour->defaultShift;
        $normal = (float) ($shift?->normal_hours ?: $labour->normal_shift_hours ?: 8);
        $start = $shift?->start_time_value ?: '09:00';
        $end = $shift?->end_time_value ?: '18:00';

        if (in_array($code, ['P', 'PRESENT'], true) || (bool) $status->counts_as_present && ! str_contains($code, 'HALF')) {
            return ['working_status_id' => $workingId, 'check_in_time' => $start, 'check_out_time' => $end,
                'normal_hours' => $status->allows_normal_hours ? $normal : 0];
        }

        if (in_array($code, ['HD', 'HALF_DAY', 'HALFDAY', 'HALF DAY'], true) || str_contains($code, 'HALF')) {
            return ['working_status_id' => $partialId ?: $workingId, 'check_in_time' => $start, 'check_out_time' => null,
                'normal_hours' => $status->allows_normal_hours ? round($normal / 2, 2) : 0];
        }

        return ['working_status_id' => null, 'check_in_time' => null, 'check_out_time' => null, 'normal_hours' => 0];
    }

    private function generateAttendanceNumber(string $date): string
    {
        $prefix = 'LAT-' . Carbon::parse($date)->format('Ym') . '-';
        $last = LabourAttendance::withTrashed()->where('attendance_number', 'like', "{$prefix}%")
            ->lockForUpdate()->orderByDesc('attendance_number')->value('attendance_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        do {
            $number = $prefix . str_pad((string) $next++, 4, '0', STR_PAD_LEFT);
        } while (LabourAttendance::withTrashed()->where('attendance_number', $number)->exists());
        return $number;
    }

    private function authorizeManager(): void
    {
        $role = auth()->user()?->role?->name;
        abort_unless(in_array($role, ['Admin', 'PMO'], true), 403,
            'Weekly Attendance is available only to Admin and PMO users.');
    }
}
