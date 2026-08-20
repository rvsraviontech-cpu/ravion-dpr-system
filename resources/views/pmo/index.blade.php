@extends('layouts.app')

@section('content')
<div class="space-y-4">

    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">PMO Action Desk</h1>
            <p class="text-sm text-gray-500">Only items currently requiring PMO action are shown here.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if(Route::has('pmo.dprs'))
                <a href="{{ route('pmo.dprs') }}"
                   class="inline-flex min-h-9 items-center border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    DPR Queue
                </a>
            @endif

            @if(Route::has('pmo-exception-dashboard.index'))
                <a href="{{ route('pmo-exception-dashboard.index') }}"
                   class="inline-flex min-h-9 items-center border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Exception Dashboard
                </a>
            @endif
        </div>
    </div>

    @php
        $materialActions =
            ($counts['material_received'] ?? 0) +
            ($counts['material_verification'] ?? 0) +
            ($counts['material_consumed'] ?? 0) +
            ($counts['material_requirements'] ?? 0);

        $fmtQty = function ($value) {
            return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
        };
    @endphp

    {{-- Compact summary --}}
    <div class="hidden overflow-x-auto border border-gray-300 bg-white md:block">
        <table class="w-full border-collapse text-sm">
            <tbody>
                <tr>
                    <td class="border-r border-gray-300 px-3 py-2 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $counts['total_actions'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500">Total Actions</div>
                    </td>
                    <td class="border-r border-gray-300 px-3 py-2 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $counts['attendance_pending'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500">Attendance</div>
                    </td>
                    <td class="border-r border-gray-300 px-3 py-2 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $materialActions }}</div>
                        <div class="text-xs text-gray-500">Materials</div>
                    </td>
                    <td class="border-r border-gray-300 px-3 py-2 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $counts['dpr_pending'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500">DPR</div>
                    </td>
                    <td class="border-r border-gray-300 px-3 py-2 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $counts['mapping_pending'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500">Mapping</div>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ ($counts['open_issues'] ?? 0) + ($counts['tomorrow_plans'] ?? 0) }}</div>
                        <div class="text-xs text-gray-500">Other Actions</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Desktop tables --}}
    <div class="hidden space-y-4 md:block">

        @if(($counts['attendance_pending'] ?? 0) > 0)
            <section>
                <div class="flex items-center justify-between bg-gray-100 px-2 py-1.5">
                    <div class="text-sm font-bold text-gray-900">Labour Attendance</div>
                    <div class="flex items-center gap-3 text-xs">
                        <span>{{ $counts['attendance_pending'] }} Pending</span>
                        <a href="{{ route('labour-attendances.index') }}" class="font-medium text-blue-700 hover:underline">View All</a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-xs">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="border border-slate-700 px-2 py-1 text-left">Sno</th>
                                <th class="border border-slate-700 px-2 py-1 text-left">Date</th>
                                <th class="border border-slate-700 px-2 py-1 text-left">Project</th>
                                <th class="border border-slate-700 px-2 py-1 text-left">Engineer</th>
                                <th class="border border-slate-700 px-2 py-1 text-left">City</th>
                                <th class="border border-slate-700 px-2 py-1 text-left">Total</th>
                                <th class="border border-slate-700 px-2 py-1 text-left">Present</th>
                                <th class="border border-slate-700 px-2 py-1 text-left">Absent</th>
                                <th class="border border-slate-700 px-2 py-1 text-left">Status</th>
                                <th class="border border-slate-700 px-2 py-1 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($labourAttendance as $row)
                                <tr class="odd:bg-white even:bg-gray-50">
                                    <td class="border border-gray-300 px-2 py-1">{{ $loop->iteration }}</td>
                                    <td class="border border-gray-300 px-2 py-1 whitespace-nowrap">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                    <td class="border border-gray-300 px-2 py-1 font-medium">{{ $row->project_name }}</td>
                                    <td class="border border-gray-300 px-2 py-1">{{ $row->engineer_name ?? '-' }}</td>
                                    <td class="border border-gray-300 px-2 py-1">{{ $row->location ?? '-' }}</td>
                                    <td class="border border-gray-300 px-2 py-1">{{ $row->total_labours }}</td>
                                    <td class="border border-gray-300 px-2 py-1">{{ $row->present_count }}</td>
                                    <td class="border border-gray-300 px-2 py-1">{{ $row->absent_count }}</td>
                                    <td class="border border-gray-300 px-2 py-1">{{ ucfirst($row->status) }}</td>
                                    <td class="border border-gray-300 px-2 py-1">
                                        <a href="{{ route('labour-attendances.show', $row->id) }}" class="font-medium text-blue-700 hover:underline">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if(($counts['attendance_corrections'] ?? 0) > 0)
            <section>
                <div class="flex items-center justify-between bg-gray-100 px-2 py-1.5">
                    <div class="text-sm font-bold text-gray-900">Attendance Corrections</div>
                    <div class="flex items-center gap-3 text-xs">
                        <span>{{ $counts['attendance_corrections'] }} Pending</span>
                        <a href="{{ route('attendance-corrections.index') }}" class="font-medium text-blue-700 hover:underline">View All</a>
                    </div>
                </div>

                <table class="w-full border-collapse text-xs">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="border border-slate-700 px-2 py-1 text-left">Sno</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Date</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Project</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Correction No.</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Status</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendanceCorrections as $row)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1">{{ $loop->iteration }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td class="border border-gray-300 px-2 py-1 font-medium">{{ $row->project_name }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->correction_number }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ ucfirst($row->status) }}</td>
                                <td class="border border-gray-300 px-2 py-1">
                                    <a href="{{ route('attendance-corrections.show', $row->id) }}" class="font-medium text-blue-700 hover:underline">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if(($counts['material_received'] ?? 0) > 0)
            <section>
                <div class="flex items-center justify-between bg-gray-100 px-2 py-1.5">
                    <div class="text-sm font-bold text-gray-900">Materials Received</div>
                    <div class="flex items-center gap-3 text-xs">
                        <span>{{ $counts['material_received'] }} Pending</span>
                        <a href="{{ route('material-received.index') }}" class="font-medium text-blue-700 hover:underline">View All</a>
                    </div>
                </div>

                <table class="w-full border-collapse text-xs">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="border border-slate-700 px-2 py-1 text-left">Sno</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Date</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Project</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Material</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Quantity</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Engineer</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Status</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materialReceived as $row)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1">{{ $loop->iteration }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td class="border border-gray-300 px-2 py-1 font-medium">{{ $row->project_name }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->material_name ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $fmtQty($row->quantity_received) }} {{ $row->unit }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->engineer_name ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->status }}</td>
                                <td class="border border-gray-300 px-2 py-1">
                                    <a href="{{ route('material-received.show', $row->id) }}" class="font-medium text-blue-700 hover:underline">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if(($counts['material_verification'] ?? 0) > 0)
            <section>
                <div class="flex items-center justify-between bg-gray-100 px-2 py-1.5">
                    <div class="text-sm font-bold text-gray-900">Material Verification</div>
                    <div class="flex items-center gap-3 text-xs">
                        <span>{{ $counts['material_verification'] }} Pending</span>
                        <a href="{{ route('material-verifications.index') }}" class="font-medium text-blue-700 hover:underline">View All</a>
                    </div>
                </div>

                <table class="w-full border-collapse text-xs">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="border border-slate-700 px-2 py-1 text-left">Sno</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Date</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Project</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Material</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Received Qty</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Status</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materialVerification as $row)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1">{{ $loop->iteration }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td class="border border-gray-300 px-2 py-1 font-medium">{{ $row->project_name }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->material_name ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $fmtQty($row->received_quantity) }} {{ $row->unit }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->status ?: 'Pending' }}</td>
                                <td class="border border-gray-300 px-2 py-1">
                                    <a href="{{ route('material-verifications.show', $row->material_received_id) }}" class="font-medium text-blue-700 hover:underline">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if(($counts['material_consumed'] ?? 0) > 0)
            <section>
                <div class="flex items-center justify-between bg-gray-100 px-2 py-1.5">
                    <div class="text-sm font-bold text-gray-900">Materials Consumed</div>
                    <div class="flex items-center gap-3 text-xs">
                        <span>{{ $counts['material_consumed'] }} Pending</span>
                        <a href="{{ route('material-consumed.index') }}" class="font-medium text-blue-700 hover:underline">View All</a>
                    </div>
                </div>

                <table class="w-full border-collapse text-xs">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="border border-slate-700 px-2 py-1 text-left">Sno</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Date</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Project</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Material</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Quantity</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Engineer</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Status</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materialConsumed as $row)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1">{{ $loop->iteration }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td class="border border-gray-300 px-2 py-1 font-medium">{{ $row->project_name }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->material_name ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $fmtQty($row->quantity_consumed) }} {{ $row->unit }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->engineer_name ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->status }}</td>
                                <td class="border border-gray-300 px-2 py-1">
                                    <a href="{{ route('material-consumed.show', $row->id) }}" class="font-medium text-blue-700 hover:underline">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if(($counts['material_requirements'] ?? 0) > 0)
            <section>
                <div class="flex items-center justify-between bg-gray-100 px-2 py-1.5">
                    <div class="text-sm font-bold text-gray-900">Materials Required</div>
                    <div class="flex items-center gap-3 text-xs">
                        <span>{{ $counts['material_requirements'] }} Pending</span>
                        <a href="{{ route('material-requirements.index') }}" class="font-medium text-blue-700 hover:underline">View All</a>
                    </div>
                </div>

                <table class="w-full border-collapse text-xs">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="border border-slate-700 px-2 py-1 text-left">Sno</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Required Date</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Project</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Material</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Quantity</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Priority</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Status</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materialRequirements as $row)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1">{{ $loop->iteration }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td class="border border-gray-300 px-2 py-1 font-medium">{{ $row->project_name }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->material_name ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $fmtQty($row->required_quantity) }} {{ $row->unit }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->priority ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->status }}</td>
                                <td class="border border-gray-300 px-2 py-1">
                                    <a href="{{ route('material-requirements.show', $row->id) }}" class="font-medium text-blue-700 hover:underline">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if(($counts['dpr_pending'] ?? 0) > 0)
            <section>
                <div class="flex items-center justify-between bg-gray-100 px-2 py-1.5">
                    <div class="text-sm font-bold text-gray-900">DPRs</div>
                    <div class="flex items-center gap-3 text-xs">
                        <span>{{ $counts['dpr_pending'] }} Pending</span>
                        <a href="{{ route('pmo.dprs') }}" class="font-medium text-blue-700 hover:underline">View All</a>
                    </div>
                </div>

                <table class="w-full border-collapse text-xs">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="border border-slate-700 px-2 py-1 text-left">Sno</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Date</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Project</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Engineer</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">City</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Status</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dprPending as $row)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1">{{ $loop->iteration }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td class="border border-gray-300 px-2 py-1 font-medium">{{ $row->project_name }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->engineer_name ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->location ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->status }}</td>
                                <td class="border border-gray-300 px-2 py-1">
                                    <a href="{{ route('dprs.show', $row->id) }}" class="font-medium text-blue-700 hover:underline">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if(($counts['mapping_pending'] ?? 0) > 0)
            <section>
                <div class="flex items-center justify-between bg-gray-100 px-2 py-1.5">
                    <div class="text-sm font-bold text-gray-900">Mapping Pending</div>
                    <div class="flex items-center gap-3 text-xs">
                        <span>{{ $counts['mapping_pending'] }} Pending</span>
                        <a href="{{ route('mapping-pending-queue.index') }}" class="font-medium text-blue-700 hover:underline">View All</a>
                    </div>
                </div>

                <table class="w-full border-collapse text-xs">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="border border-slate-700 px-2 py-1 text-left">Sno</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Date</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Project</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Activity</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Engineer</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Status</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mappingPending as $row)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1">{{ $loop->iteration }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d/m/Y') : '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1 font-medium">{{ $row->project_name }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->activity_name ?? 'Not mapped' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->engineer_name ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->status }}</td>
                                <td class="border border-gray-300 px-2 py-1">
                                    <a href="{{ route('mapping-pending-queue.edit', $row->id) }}" class="font-medium text-blue-700 hover:underline">Map</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if(($counts['open_issues'] ?? 0) > 0)
            <section>
                <div class="flex items-center justify-between bg-gray-100 px-2 py-1.5">
                    <div class="text-sm font-bold text-gray-900">Escalated Site Issues</div>
                    <div class="flex items-center gap-3 text-xs">
                        <span>{{ $counts['open_issues'] }} Pending</span>
                        <a href="{{ route('site-issues.index') }}" class="font-medium text-blue-700 hover:underline">View All</a>
                    </div>
                </div>

                <table class="w-full border-collapse text-xs">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="border border-slate-700 px-2 py-1 text-left">Sno</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Date</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Project</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Issue</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Priority</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Target</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Status</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siteIssues as $row)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1">{{ $loop->iteration }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td class="border border-gray-300 px-2 py-1 font-medium">{{ $row->project_name }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->title }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->priority }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->target_closure_date ? \Carbon\Carbon::parse($row->target_closure_date)->format('d/m/Y') : '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->status }}</td>
                                <td class="border border-gray-300 px-2 py-1">
                                    <a href="{{ route('site-issues.show', $row->id) }}" class="font-medium text-blue-700 hover:underline">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if(($counts['tomorrow_plans'] ?? 0) > 0)
            <section>
                <div class="flex items-center justify-between bg-gray-100 px-2 py-1.5">
                    <div class="text-sm font-bold text-gray-900">Tomorrow Plans</div>
                    <div class="flex items-center gap-3 text-xs">
                        <span>{{ $counts['tomorrow_plans'] }} Pending</span>
                        <a href="{{ route('tomorrow-plans.index') }}" class="font-medium text-blue-700 hover:underline">View All</a>
                    </div>
                </div>

                <table class="w-full border-collapse text-xs">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="border border-slate-700 px-2 py-1 text-left">Sno</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Planned Date</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Project</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Activity</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Engineer</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Quantity</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Priority</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Status</th>
                            <th class="border border-slate-700 px-2 py-1 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tomorrowPlans as $row)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1">{{ $loop->iteration }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td class="border border-gray-300 px-2 py-1 font-medium">{{ $row->project_name }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->activity_name ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->engineer_name ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $fmtQty($row->planned_quantity) }} {{ $row->unit }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->priority ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-1">{{ $row->status }}</td>
                                <td class="border border-gray-300 px-2 py-1">
                                    <a href="{{ route('tomorrow-plans.show', $row->id) }}" class="font-medium text-blue-700 hover:underline">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if(($counts['total_actions'] ?? 0) === 0)
            <div class="border border-gray-300 bg-white px-4 py-10 text-center">
                <div class="text-lg font-semibold text-gray-900">No pending PMO actions</div>
                <div class="mt-1 text-sm text-gray-500">All current approval, verification, mapping and escalated action queues are clear.</div>
            </div>
        @endif
    </div>

    {{-- Mobile / PWA --}}
    <div class="space-y-2 md:hidden">
        @php
            $mobileQueues = [
                ['Labour Attendance', $counts['attendance_pending'] ?? 0, 'labour-attendances.index'],
                ['Attendance Corrections', $counts['attendance_corrections'] ?? 0, 'attendance-corrections.index'],
                ['Materials Received', $counts['material_received'] ?? 0, 'material-received.index'],
                ['Material Verification', $counts['material_verification'] ?? 0, 'material-verifications.index'],
                ['Materials Consumed', $counts['material_consumed'] ?? 0, 'material-consumed.index'],
                ['Materials Required', $counts['material_requirements'] ?? 0, 'material-requirements.index'],
                ['DPRs', $counts['dpr_pending'] ?? 0, 'pmo.dprs'],
                ['Mapping Pending', $counts['mapping_pending'] ?? 0, 'mapping-pending-queue.index'],
                ['Escalated Site Issues', $counts['open_issues'] ?? 0, 'site-issues.index'],
                ['Tomorrow Plans', $counts['tomorrow_plans'] ?? 0, 'tomorrow-plans.index'],
            ];
        @endphp

        @foreach($mobileQueues as [$label, $value, $routeName])
            @if($value > 0)
                <a href="{{ route($routeName) }}"
                   class="flex min-h-12 items-center justify-between border border-gray-300 bg-white px-3 py-2">
                    <span class="text-sm font-medium text-gray-900">{{ $label }}</span>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-gray-900">{{ $value }}</span>
                        <span class="text-xs text-blue-700">Open</span>
                    </div>
                </a>
            @endif
        @endforeach

        @if(($counts['total_actions'] ?? 0) === 0)
            <div class="border border-gray-300 bg-white px-4 py-8 text-center">
                <div class="font-semibold text-gray-900">No pending actions</div>
                <div class="mt-1 text-sm text-gray-500">PMO action queue is clear.</div>
            </div>
        @endif
    </div>

</div>
@endsection
