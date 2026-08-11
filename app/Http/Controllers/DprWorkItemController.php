<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Models\ActivityMapping;
use App\Models\Contractor;
use App\Models\DprWorkItem;
use App\Models\DprWorkPhoto;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectRoom;
use App\Models\ProjectSubspace;
use App\Models\ProjectUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class DprWorkItemController extends Controller
{
    private const PHOTO_TYPES = [
        'Before Work',
        'During Work',
        'Completed Work',
        'Quality Inspection',
        'Measurement',
        'Hidden Work',
        'Other',
    ];

    public function index(Request $request): View
    {
        $query = DprWorkItem::query()
            ->with($this->relationships());

        if ($this->isEngineer()) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }

        if ($request->filled('work_date')) {
            $query->whereDate('work_date', $request->input('work_date'));
        }

        if ($request->filled('link_status')) {
            if ($request->input('link_status') === 'linked') {
                $query->whereNotNull('dpr_id');
            } elseif ($request->input('link_status') === 'unlinked') {
                $query->whereNull('dpr_id');
            }
        }

        if ($request->filled('user_id') && ! $this->isEngineer()) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('remarks', 'like', "%{$search}%")
                    ->orWhereHas('project', fn (Builder $q) =>
                        $q->where('project_name', 'like', "%{$search}%"))
                    ->orWhereHas('activity', fn (Builder $q) =>
                        $q->where('activity_name', 'like', "%{$search}%"))
                    ->orWhereHas('activityMapping', fn (Builder $q) =>
                        $q->where('activity_name', 'like', "%{$search}%"))
                    ->orWhereHas('contractor', fn (Builder $q) =>
                        $q->where('contractor_name', 'like', "%{$search}%"));
            });
        }

        $workItems = $query
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $projects = $this->availableProjects();

        $engineers = $this->isEngineer()
            ? collect([auth()->user()])
            : \App\Models\User::query()
                ->whereHas('role', fn (Builder $q) => $q->where('name', 'Engineer'))
                ->orderBy('name')
                ->get();

        return view('work-done.index', compact(
            'workItems',
            'projects',
            'engineers'
        ));
    }

    public function create(): View
    {
        return view('work-done.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateWorkItem($request);

        $this->ensureProjectAccess((int) $validated['project_id']);
        $this->validateRelationships($validated);

        $storedPaths = [];

        try {
            $workItem = DB::transaction(function () use (
                $request,
                $validated,
                &$storedPaths
            ): DprWorkItem {
                $workItem = DprWorkItem::create([
                    'dpr_id' => null,
                    'project_id' => (int) $validated['project_id'],
                    'user_id' => auth()->id(),
                    'work_date' => $validated['work_date'],
                    'activity_id' => (int) $validated['activity_id'],
                    'activity_mapping_id' => $validated['activity_mapping_id'] ?? null,
                    'project_block_id' => $validated['project_block_id'] ?? null,
                    'project_floor_id' => $validated['project_floor_id'] ?? null,
                    'project_unit_id' => $validated['project_unit_id'] ?? null,
                    'project_room_id' => $validated['project_room_id'] ?? null,
                    'project_subspace_id' => $validated['project_subspace_id'] ?? null,
                    'contractor_id' => $validated['contractor_id'] ?? null,
                    'quantity_completed' => $validated['quantity_completed'],
                    'remarks' => $this->nullableTrim($validated['remarks'] ?? null),
                    'status' => 'Draft',
                ]);

                $this->storePhotos($request, $workItem, $storedPaths);

                $workItem->load($this->relationships());

                AuditHelper::log(
                    'Work Done',
                    'Created',
                    'DprWorkItem',
                    $workItem->id,
                    'Standalone Work Done entry created.',
                    null,
                    $this->auditValues($workItem)
                );

                return $workItem;
            });

            return redirect()
                ->route('work-done.show', $workItem)
                ->with('success', 'Work Done entry created successfully.');
        } catch (ValidationException $exception) {
            $this->deletePaths($storedPaths);
            throw $exception;
        } catch (Throwable $exception) {
            $this->deletePaths($storedPaths);
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to create the Work Done entry.');
        }
    }

    public function show(DprWorkItem $workDone): View
    {
        $workDone->load($this->relationships());
        $this->ensureWorkItemAccess($workDone);

        return view('work-done.show', compact('workDone'));
    }

    public function edit(DprWorkItem $workDone): View
    {
        $workDone->load($this->relationships());

        $this->ensureWorkItemAccess($workDone);
        $this->ensureEditable($workDone);

        return view(
            'work-done.edit',
            array_merge(compact('workDone'), $this->formData())
        );
    }

    public function update(
        Request $request,
        DprWorkItem $workDone
    ): RedirectResponse {
        $workDone->load($this->relationships());

        $this->ensureWorkItemAccess($workDone);
        $this->ensureEditable($workDone);

        $validated = $this->validateWorkItem($request);

        $this->ensureProjectAccess((int) $validated['project_id']);
        $this->validateRelationships($validated);

        $storedPaths = [];
        $deleteAfterCommit = [];

        try {
            DB::transaction(function () use (
                $request,
                $validated,
                $workDone,
                &$storedPaths,
                &$deleteAfterCommit
            ): void {
                $oldValues = $this->auditValues($workDone);

                $removeIds = collect($validated['remove_photo_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->unique();

                if ($removeIds->isNotEmpty()) {
                    foreach (
                        $workDone->photos()->whereIn('id', $removeIds)->get()
                        as $photo
                    ) {
                        $deleteAfterCommit[] = $photo->file_path;
                        $photo->delete();
                    }
                }

                $workDone->update([
                    'project_id' => (int) $validated['project_id'],
                    'work_date' => $validated['work_date'],
                    'activity_id' => (int) $validated['activity_id'],
                    'activity_mapping_id' => $validated['activity_mapping_id'] ?? null,
                    'project_block_id' => $validated['project_block_id'] ?? null,
                    'project_floor_id' => $validated['project_floor_id'] ?? null,
                    'project_unit_id' => $validated['project_unit_id'] ?? null,
                    'project_room_id' => $validated['project_room_id'] ?? null,
                    'project_subspace_id' => $validated['project_subspace_id'] ?? null,
                    'contractor_id' => $validated['contractor_id'] ?? null,
                    'quantity_completed' => $validated['quantity_completed'],
                    'remarks' => $this->nullableTrim($validated['remarks'] ?? null),
                ]);

                $this->storePhotos($request, $workDone, $storedPaths);

                $workDone->load($this->relationships());

                AuditHelper::log(
                    'Work Done',
                    'Updated',
                    'DprWorkItem',
                    $workDone->id,
                    'Standalone Work Done entry updated.',
                    $oldValues,
                    $this->auditValues($workDone)
                );
            });

            $this->deletePaths($deleteAfterCommit);

            return redirect()
                ->route('work-done.show', $workDone)
                ->with('success', 'Work Done entry updated successfully.');
        } catch (ValidationException $exception) {
            $this->deletePaths($storedPaths);
            throw $exception;
        } catch (Throwable $exception) {
            $this->deletePaths($storedPaths);
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to update the Work Done entry.');
        }
    }

    public function destroy(DprWorkItem $workDone): RedirectResponse
    {
        $workDone->load($this->relationships());

        $this->ensureWorkItemAccess($workDone);
        $this->ensureEditable($workDone);

        $oldValues = $this->auditValues($workDone);
        $paths = $workDone->photos->pluck('file_path')->filter()->all();
        $id = $workDone->id;

        DB::transaction(function () use ($workDone, $oldValues, $id) {
            $workDone->delete();

            AuditHelper::log(
                'Work Done',
                'Deleted',
                'DprWorkItem',
                $id,
                'Standalone Work Done entry deleted.',
                $oldValues,
                null
            );
        });

        $this->deletePaths($paths);

        return redirect()
            ->route('work-done.index')
            ->with('success', 'Work Done entry deleted successfully.');
    }

    public function destroyPhoto(
        DprWorkItem $workDone,
        DprWorkPhoto $photo
    ): RedirectResponse {
        $this->ensureWorkItemAccess($workDone);
        $this->ensureEditable($workDone);

        abort_unless(
            (int) $photo->dpr_work_item_id === (int) $workDone->id,
            404
        );

        $path = $photo->file_path;
        $photo->delete();
        $this->deletePaths([$path]);

        return back()->with('success', 'Work Done photo removed successfully.');
    }

    private function validateWorkItem(Request $request): array
    {
        return $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'work_date' => ['required', 'date'],
            'activity_id' => ['required', 'integer', 'exists:activities,id'],
            'activity_mapping_id' => ['nullable', 'integer', 'exists:activity_mappings,id'],
            'project_block_id' => ['nullable', 'integer', 'exists:project_blocks,id'],
            'project_floor_id' => ['nullable', 'integer', 'exists:project_floors,id'],
            'project_unit_id' => ['nullable', 'integer', 'exists:project_units,id'],
            'project_room_id' => ['nullable', 'integer', 'exists:project_rooms,id'],
            'project_subspace_id' => ['nullable', 'integer', 'exists:project_subspaces,id'],
            'contractor_id' => ['nullable', 'integer', 'exists:contractors,id'],
            'quantity_completed' => ['required', 'numeric', 'gt:0'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:30'],
            'photos.*.file' => [
                'nullable', 'file', 'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
            'photos.*.photo_type' => [
                'nullable',
                'string',
                'in:' . implode(',', self::PHOTO_TYPES),
            ],
            'photos.*.caption' => ['nullable', 'string', 'max:500'],
            'remove_photo_ids' => ['nullable', 'array'],
            'remove_photo_ids.*' => ['integer', 'exists:dpr_work_photos,id'],
        ]);
    }

    private function validateRelationships(array $validated): void
    {
        $errors = [];
        $projectId = (int) $validated['project_id'];

        if (! empty($validated['project_block_id'])
            && ! ProjectBlock::whereKey($validated['project_block_id'])
                ->where('project_id', $projectId)
                ->exists()
        ) {
            $errors['project_block_id'][] =
                'The selected Block does not belong to the selected Project.';
        }

        if (! empty($validated['project_floor_id'])) {
            $query = ProjectFloor::whereKey($validated['project_floor_id'])
                ->where('project_id', $projectId);

            if (! empty($validated['project_block_id'])) {
                $query->where('project_block_id', $validated['project_block_id']);
            }

            if (! $query->exists()) {
                $errors['project_floor_id'][] =
                    'The selected Floor does not belong to the selected Project/Block.';
            }
        }

        if (! empty($validated['project_unit_id'])) {
            $query = ProjectUnit::whereKey($validated['project_unit_id'])
                ->where('project_id', $projectId);

            if (! empty($validated['project_floor_id'])) {
                $query->where('project_floor_id', $validated['project_floor_id']);
            }

            if (! $query->exists()) {
                $errors['project_unit_id'][] =
                    'The selected Unit does not belong to the selected Project/Floor.';
            }
        }

        if (! empty($validated['project_room_id'])) {
            $query = ProjectRoom::whereKey($validated['project_room_id']);

            if (! empty($validated['project_unit_id'])) {
                $query->where('project_unit_id', $validated['project_unit_id']);
            }

            if (! $query->exists()) {
                $errors['project_room_id'][] =
                    'The selected Room does not belong to the selected Unit.';
            }
        }

        if (! empty($validated['project_subspace_id'])) {
            $query = ProjectSubspace::whereKey($validated['project_subspace_id']);

            if (! empty($validated['project_room_id'])) {
                $query->where('project_room_id', $validated['project_room_id']);
            }

            if (! $query->exists()) {
                $errors['project_subspace_id'][] =
                    'The selected Sub-space does not belong to the selected Room.';
            }
        }

        if (! empty($validated['activity_mapping_id'])) {
            $mapping = ActivityMapping::find($validated['activity_mapping_id']);

            if ($mapping?->activity_id
                && (int) $mapping->activity_id !== (int) $validated['activity_id']
            ) {
                $errors['activity_mapping_id'][] =
                    'The selected Activity Mapping does not match the selected Activity.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function formData(): array
    {
        return [
            'projects' => $this->availableProjects(),
            'activities' => Activity::where('is_active', true)
                ->orderBy('activity_name')->get(),
            'activityMappings' => ActivityMapping::with('division')
                ->where('is_active', true)
                ->orderBy('activity_name')->get(),
            'activityDivisions' => ActivityDivision::where('is_active', true)
                ->orderBy('sequence')->get(),
            'contractors' => Contractor::where('status', 1)
                ->orderBy('contractor_name')->get(),
            'projectBlocks' => ProjectBlock::where('is_active', true)
                ->orderBy('name')->get(),
            'projectFloors' => ProjectFloor::where('is_active', true)
                ->orderBy('sequence')->orderBy('name')->get(),
            'projectUnits' => ProjectUnit::where('is_active', true)
                ->orderBy('name')->get(),
            'projectRooms' => ProjectRoom::where('is_active', true)
                ->orderBy('name')->get(),
            'projectSubspaces' => ProjectSubspace::where('is_active', true)
                ->orderBy('name')->get(),
            'photoTypes' => self::PHOTO_TYPES,
        ];
    }

    private function availableProjects()
    {
        if ($this->isEngineer()) {
            return auth()->user()->projects()
                ->whereNotIn('projects.status', ['Completed', 'Handed Over', 'Closed'])
                ->orderBy('project_name')
                ->get();
        }

        return Project::whereNotIn('status', ['Completed', 'Handed Over', 'Closed'])
            ->orderBy('project_name')
            ->get();
    }

    private function relationships(): array
    {
        return [
            'project',
            'dpr.project',
            'dpr.user',
            'engineer',
            'activity',
            'activityMapping.division',
            'contractor',
            'block',
            'floor',
            'unit',
            'room',
            'subspace',
            'photos.uploader',
        ];
    }

    private function storePhotos(
        Request $request,
        DprWorkItem $workItem,
        array &$storedPaths
    ): void {
        $rows = $request->input('photos', []);
        $files = $request->file('photos', []);

        if (! is_array($files)) {
            return;
        }

        $workItem->loadMissing(['project', 'engineer', 'activity', 'activityMapping']);

        $sequence = $workItem->photos()->count() + 1;

        foreach ($files as $index => $fileData) {
            $file = is_array($fileData) ? ($fileData['file'] ?? null) : null;

            if (! $file) {
                continue;
            }

            $metadata = $rows[$index] ?? [];
            $type = $this->normalizePhotoType($metadata['photo_type'] ?? 'Completed Work');
            $caption = $this->nullableTrim($metadata['caption'] ?? null);

            $extension = strtolower(
                $file->getClientOriginalExtension()
                ?: $file->extension()
                ?: 'jpg'
            );

            $filename = implode('-', [
                $this->filenamePart($workItem->project?->project_name ?? 'Project', 50),
                $this->filenamePart($workItem->activity_name ?? 'Work', 60),
                $this->filenamePart($type, 35),
                $this->filenamePart($workItem->engineer?->name ?? auth()->user()?->name ?? 'Engineer', 40),
                $workItem->work_date?->format('Ymd') ?? now()->format('Ymd'),
                now()->format('His'),
                str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
            ]) . '.' . $extension;

            $path = $file->storeAs(
                'work-done/project-' . $workItem->project_id . '/work-item-' . $workItem->id,
                $filename,
                'public'
            );

            $storedPaths[] = $path;

            $workItem->photos()->create([
                'uploaded_by' => auth()->id(),
                'photo_type' => $type,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'caption' => $caption,
                'sort_order' => $sequence,
            ]);

            $sequence++;
        }
    }

    private function auditValues(DprWorkItem $workItem): array
    {
        $workItem->loadMissing($this->relationships());

        return [
            'id' => $workItem->id,
            'dpr_id' => $workItem->dpr_id,
            'project_id' => $workItem->project_id,
            'user_id' => $workItem->user_id,
            'work_date' => $workItem->work_date?->format('Y-m-d'),
            'activity_id' => $workItem->activity_id,
            'activity_name' => $workItem->activity_name,
            'activity_mapping_id' => $workItem->activity_mapping_id,
            'location_path' => $workItem->location_path,
            'quantity_completed' => $workItem->quantity_completed,
            'unit' => $workItem->unit_name,
            'remarks' => $workItem->remarks,
        ];
    }

    private function ensureProjectAccess(int $projectId): void
    {
        if (! $this->isEngineer()) {
            return;
        }

        abort_unless(
            auth()->user()->projects()
                ->where('projects.id', $projectId)
                ->exists(),
            403,
            'You are not assigned to this project.'
        );
    }

    private function ensureWorkItemAccess(DprWorkItem $workItem): void
    {
        if (! $this->isEngineer()) {
            return;
        }

        abort_unless(
            (int) $workItem->user_id === (int) auth()->id(),
            403,
            'Unauthorized Work Done access.'
        );
    }

    private function ensureEditable(DprWorkItem $workItem): void
    {
        abort_if(
            $workItem->dpr_id !== null,
            403,
            'This Work Done entry is already linked to a DPR and cannot be edited here.'
        );
    }

    private function isEngineer(): bool
    {
        return auth()->user()?->role?->name === 'Engineer';
    }

    private function normalizePhotoType(mixed $type): string
    {
        $type = trim((string) $type);

        return in_array($type, self::PHOTO_TYPES, true)
            ? $type
            : 'Other';
    }

    private function filenamePart(string $value, int $max): string
    {
        $slug = Str::slug(Str::limit(trim($value), $max, ''), '-');

        return $slug !== '' ? $slug : 'NA';
    }

    private function deletePaths(array $paths): void
    {
        $paths = collect($paths)->filter()->unique()->values()->all();

        if ($paths) {
            Storage::disk('public')->delete($paths);
        }
    }

    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
