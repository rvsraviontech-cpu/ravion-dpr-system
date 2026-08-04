<x-rds.card :padding="false">

    <div class="w-full overflow-x-auto">

        <table class="w-full min-w-[1250px] table-fixed divide-y divide-gray-200">

            <colgroup>
                <col class="w-[55px]">
                <col class="w-[260px]">
                <col class="w-[115px]">
                <col class="w-[235px]">
                <col class="w-[135px]">
                <col class="w-[165px]">
                <col class="w-[115px]">
                <col class="w-[275px]">
            </colgroup>

            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        #
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Labour
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Change
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Project
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Attendance Date
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Requested By
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Status
                    </th>

                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Actions
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">

                @forelse($attendanceCorrections as $correction)

                    @php
                        /*
                         * The controller currently provides details_count but does
                         * not eager-load correction details for the index table.
                         * loadMissing keeps this replacement file self-contained.
                         */
                        $correction->loadMissing([
                            'details.labour.designationRole',
                        ]);

                        $status = strtolower(
                            (string) $correction->status
                        );

                        $statusVariant = match ($status) {
                            'draft' => 'secondary',
                            'submitted' => 'warning',
                            'approved' => 'success',
                            'applied' => 'success',
                            'rejected' => 'danger',
                            default => 'secondary',
                        };

                        $statusLabel = match ($status) {
                            'draft' => 'Draft',
                            'submitted' => 'Submitted',
                            'approved' => 'Approved',
                            'applied' => 'Applied',
                            'rejected' => 'Rejected',
                            default => ucfirst(
                                $status ?: 'Unknown'
                            ),
                        };

                        $attendance = $correction->labourAttendance
                            ?? $correction->attendance
                            ?? null;

                        $project = $correction->project
                            ?? $attendance?->project
                            ?? null;

                        $requestedBy = $correction->createdBy
                            ?? null;

                        $activeDetails = $correction
                            ->details
                            ->where('is_active', true)
                            ->values();

                        $firstDetail = $activeDetails->first();

                        $affectedLabour = $firstDetail?->labour;

                        $changesCount = $correction->details_count
                            ?? $activeDetails->count();

                        $moreCount = max(
                            (int) $changesCount - 1,
                            0
                        );

                        $actionType = strtolower(
                            (string) (
                                $firstDetail?->action_type
                                ?? ''
                            )
                        );

                        $actionVariant = match ($actionType) {
                            'add' => 'success',
                            'modify' => 'warning',
                            'remove' => 'danger',
                            default => 'secondary',
                        };

                        $actionLabel = match ($actionType) {
                            'add' => 'Added',
                            'modify' => 'Modified',
                            'remove' => 'Removed',
                            default => 'Changed',
                        };
                    @endphp

                    <tr class="align-middle hover:bg-gray-50">

                        <td class="px-3 py-4 text-sm text-gray-500">
                            {{ $attendanceCorrections->firstItem() + $loop->index }}
                        </td>

                        <td class="px-3 py-4">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $affectedLabour?->full_name
                                    ?? $affectedLabour?->name
                                    ?? 'Affected Labour' }}
                            </div>

                            @if($affectedLabour?->designationRole?->name)
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $affectedLabour->designationRole->name }}
                                </div>
                            @endif

                            @if($moreCount > 0)
                                <div class="mt-1 text-xs font-semibold text-blue-600">
                                    +{{ $moreCount }} more
                                    {{ $moreCount === 1 ? 'labour' : 'labourers' }}
                                </div>
                            @endif

                            <div class="mt-2 space-y-0.5 text-[11px] text-gray-400">
                                <div>
                                    {{ $correction->correction_number ?? 'No correction number' }}
                                </div>

                                <div>
                                    {{ $attendance?->attendance_number ?? 'No attendance number' }}
                                </div>
                            </div>
                        </td>

                        <td class="px-3 py-4">
                            <x-rds.badge :variant="$actionVariant">
                                {{ $actionLabel }}
                            </x-rds.badge>

                            @if($changesCount > 1)
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $changesCount }} total changes
                                </div>
                            @endif
                        </td>

                        <td class="px-3 py-4 text-sm text-gray-700">
                            <div class="font-medium text-gray-900">
                                {{ $project?->project_name ?? '—' }}
                            </div>

                            @if($project?->project_code)
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $project->project_code }}
                                </div>
                            @endif

                            @if($attendance?->shift?->name)
                                <div class="mt-1 text-xs text-gray-500">
                                    Shift: {{ $attendance->shift->name }}
                                </div>
                            @endif
                        </td>

                        <td class="px-3 py-4 text-sm text-gray-700">
                            {{ $correction->attendance_date?->format('d M Y')
                                ?? $attendance?->attendance_date?->format('d M Y')
                                ?? '—' }}
                        </td>

                        <td class="px-3 py-4 text-sm text-gray-700">
                            <div class="font-medium text-gray-900">
                                {{ $requestedBy?->name ?? '—' }}
                            </div>

                            @if($requestedBy?->email)
                                <div class="mt-1 truncate text-xs text-gray-500">
                                    {{ $requestedBy->email }}
                                </div>
                            @endif
                        </td>

                        <td class="px-3 py-4 text-center">
                            <x-rds.badge :variant="$statusVariant">
                                {{ $statusLabel }}
                            </x-rds.badge>
                        </td>

                        <td class="px-3 py-4 text-right">
                            <div class="flex flex-wrap items-center justify-end gap-1.5">

                                @if(auth()->user()->hasPermission('attendance_corrections.view'))
                                    <x-rds.button
                                        href="{{ route('attendance-corrections.show', $correction) }}"
                                        variant="secondary"
                                        size="sm"
                                        class="!px-3 !py-1.5 !text-xs"
                                    >
                                        View
                                    </x-rds.button>
                                @endif

                                @if(
                                    auth()->user()->hasPermission('attendance_corrections.edit')
                                    && in_array(
                                        $status,
                                        ['draft', 'rejected'],
                                        true
                                    )
                                )
                                    <x-rds.button
                                        href="{{ route('attendance-corrections.edit', $correction) }}"
                                        variant="secondary"
                                        size="sm"
                                        class="!px-3 !py-1.5 !text-xs"
                                    >
                                        Edit
                                    </x-rds.button>
                                @endif

                                @if(
                                    auth()->user()->hasPermission('attendance_corrections.submit')
                                    && $status === 'draft'
                                )
                                    <form
                                        id="submit-correction-{{ $correction->id }}"
                                        method="POST"
                                        action="{{ route('attendance-corrections.submit', $correction) }}"
                                        class="inline-block"
                                        onsubmit="return confirm('Submit this attendance correction for approval?');"
                                    >
                                        @csrf
                                    </form>

                                    <button
                                        type="submit"
                                        form="submit-correction-{{ $correction->id }}"
                                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300"
                                    >
                                        Submit
                                    </button>
                                @endif

                                @if(
                                    auth()->user()->hasPermission('attendance_corrections.approve')
                                    && $status === 'submitted'
                                )
                                    <form
                                        id="approve-correction-{{ $correction->id }}"
                                        method="POST"
                                        action="{{ route('attendance-corrections.approve', $correction) }}"
                                        class="inline-block"
                                        onsubmit="return confirm('Approve this attendance correction request?');"
                                    >
                                        @csrf
                                    </form>

                                    <button
                                        type="submit"
                                        form="approve-correction-{{ $correction->id }}"
                                        class="inline-flex items-center justify-center rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
                                    >
                                        Approve
                                    </button>

                                    <form
                                        id="reject-correction-{{ $correction->id }}"
                                        method="POST"
                                        action="{{ route('attendance-corrections.reject', $correction) }}"
                                        class="inline-block"
                                        onsubmit="return confirm('Reject this attendance correction request?');"
                                    >
                                        @csrf

                                        <input
                                            type="hidden"
                                            name="rejection_reason"
                                            value="Rejected from Attendance Corrections list."
                                        >
                                    </form>

                                    <button
                                        type="submit"
                                        form="reject-correction-{{ $correction->id }}"
                                        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
                                    >
                                        Reject
                                    </button>
                                @endif

                                @if(
                                    auth()->user()->hasPermission('attendance_corrections.apply')
                                    && $status === 'approved'
                                )
                                    <form
                                        id="apply-correction-{{ $correction->id }}"
                                        method="POST"
                                        action="{{ route('attendance-corrections.apply', $correction) }}"
                                        class="inline-block"
                                        onsubmit="return confirm('Apply this correction to the approved attendance sheet?');"
                                    >
                                        @csrf
                                    </form>

                                    <button
                                        type="submit"
                                        form="apply-correction-{{ $correction->id }}"
                                        class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300"
                                    >
                                        Apply
                                    </button>
                                @endif

                                @if(
                                    auth()->user()->hasPermission('attendance_corrections.delete')
                                    && in_array(
                                        $status,
                                        ['draft', 'rejected'],
                                        true
                                    )
                                )
                                    <form
                                        id="delete-correction-{{ $correction->id }}"
                                        method="POST"
                                        action="{{ route('attendance-corrections.destroy', $correction) }}"
                                        class="inline-block"
                                        onsubmit="return confirm('Delete this attendance correction?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                    <button
                                        type="submit"
                                        form="delete-correction-{{ $correction->id }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 shadow-sm transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-200"
                                    >
                                        Delete
                                    </button>
                                @endif

                            </div>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <div class="text-sm font-medium text-gray-700">
                                No attendance correction requests found.
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                Adjust the filters or create the first attendance correction request.
                            </div>

                            @if(auth()->user()->hasPermission('attendance_corrections.create'))
                                <div class="mt-4">
                                    <x-rds.button
                                        href="{{ route('attendance-corrections.create') }}"
                                        variant="primary"
                                        size="sm"
                                    >
                                        New Attendance Correction
                                    </x-rds.button>
                                </div>
                            @endif
                        </td>
                    </tr>

                @endforelse

            </tbody>
        </table>
    </div>

    @if($attendanceCorrections->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $attendanceCorrections->withQueryString()->links() }}
        </div>
    @endif

</x-rds.card>
