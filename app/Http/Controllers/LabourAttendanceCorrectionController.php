<?php

namespace App\Http\Controllers;

use App\Models\LabourAttendance;
use App\Models\Project;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabourAttendanceCorrectionController extends Controller
{
    /**
     * Display attendance sheets that may require correction.
     *
     * Corrections are performed through the existing Labour Attendance
     * reopen, edit, resubmit and approval workflow. This controller does
     * not create a duplicate attendance transaction.
     */
    public function index(Request $request): View
    {
        $query = LabourAttendance::query()
            ->with([
                'project',
                'shift',
                'recordedBy',
                'submittedBy',
                'approvedBy',
                'rejectedBy',
                'reopenedBy',
            ])
            ->withCount('details')
            ->where('is_active', true);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(
                function (Builder $builder) use ($search): void {
                    $builder
                        ->where(
                            'attendance_number',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'project',
                            function (Builder $projectQuery) use ($search): void {
                                $projectQuery
                                    ->where(
                                        'project_code',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'project_name',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('project_id')) {
            $query->where(
                'project_id',
                $request->integer('project_id')
            );
        }

        if ($request->filled('shift_id')) {
            $query->where(
                'shift_id',
                $request->integer('shift_id')
            );
        }

        if ($request->filled('attendance_date')) {
            $query->whereDate(
                'attendance_date',
                $request->input('attendance_date')
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'attendance_date',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'attendance_date',
                '<=',
                $request->input('date_to')
            );
        }

        /*
         * By default, show attendance sheets relevant to corrections.
         *
         * approved:
         *     May be reopened by an authorized PMO/Admin user.
         *
         * reopened:
         *     Currently being corrected by the engineer.
         *
         * rejected:
         *     Requires correction before resubmission.
         *
         * submitted:
         *     Correction has been submitted and awaits PMO review.
         */
        $allowedStatuses = [
            'approved',
            'reopened',
            'rejected',
            'submitted',
        ];

        if (
            $request->filled('status')
            && in_array(
                $request->input('status'),
                $allowedStatuses,
                true
            )
        ) {
            $query->where(
                'status',
                $request->input('status')
            );
        } else {
            $query->whereIn(
                'status',
                $allowedStatuses
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Result
        |--------------------------------------------------------------------------
        */

        $labourAttendances = $query
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(
                config('rds.pagination.per_page', 15)
            )
            ->withQueryString();

        return view(
            'labour-attendance-corrections.index',
            [
                'labourAttendances' => $labourAttendances,

                'projects' => Project::query()
                    ->active()
                    ->orderBy('project_name')
                    ->get(),

                'shifts' => Shift::query()
                    ->active()
                    ->ordered()
                    ->get(),

                'statuses' => [
                    'approved' => 'Approved',
                    'reopened' => 'Reopened for Correction',
                    'rejected' => 'Rejected',
                    'submitted' => 'Submitted for Review',
                ],
            ]
        );
    }
}