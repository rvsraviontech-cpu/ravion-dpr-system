@extends('layouts.app')

@section('content')

@php
    $statusClass = match($dpr->status) {
        'Approved' => 'bg-green-100 text-green-800',
        'Rejected' => 'bg-red-100 text-red-800',
        default => 'bg-yellow-100 text-yellow-800',
    };

    $attendanceDetails = $dpr->labourAttendances
        ->flatMap(fn ($attendance) => $attendance->details ?? collect());

    $presentCount = $attendanceDetails
        ->filter(fn ($detail) => strtolower((string) ($detail->attendanceStatus?->code ?? $detail->attendanceStatus?->name ?? '')) === 'present')
        ->count();

    $absentCount = $attendanceDetails
        ->filter(fn ($detail) => strtolower((string) ($detail->attendanceStatus?->code ?? $detail->attendanceStatus?->name ?? '')) === 'absent')
        ->count();

    $halfDayCount = $attendanceDetails
        ->filter(fn ($detail) => str_contains(
            strtolower((string) ($detail->attendanceStatus?->code ?? $detail->attendanceStatus?->name ?? '')),
            'half'
        ))
        ->count();

    $uniqueLabourCount = $attendanceDetails
        ->pluck('labour_id')
        ->filter()
        ->unique()
        ->count();

    $totalNormalHours = round((float) $attendanceDetails->sum('normal_hours'), 2);
    $totalOtHours = round((float) $attendanceDetails->sum('ot_hours'), 2);

    $formatQty = function ($value) {
        if ($value === null || $value === '') {
            return '0';
        }

        return rtrim(
            rtrim(
                number_format((float) $value, 3, '.', ''),
                '0'
            ),
            '.'
        );
    };
@endphp

<div class="mx-auto max-w-full">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                    Daily Progress Report
                </h1>

                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                    {{ $dpr->status }}
                </span>
            </div>

            <p class="mt-1 text-gray-500">
                DPR #{{ $dpr->id }} · {{ $dpr->dpr_date?->format('d M Y') }}
            </p>
        </div>

        <div class="grid grid-cols-3 gap-2 sm:flex sm:flex-wrap">

            <a href="{{ route('dprs.index') }}"
               class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-3 py-3 text-sm font-semibold text-white hover:bg-gray-700 sm:px-5 sm:py-2.5">
                Back
            </a>

            <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-700 px-3 py-3 text-sm font-semibold text-white hover:bg-slate-800 sm:px-5 sm:py-2.5">
                Print
            </button>

            <a href="{{ route('dprs.pdf', $dpr->id) }}"
               class="inline-flex items-center justify-center rounded-lg bg-green-600 px-3 py-3 text-center text-sm font-semibold text-white hover:bg-green-700 sm:px-5 sm:py-2.5">
                Download PDF
            </a>

        </div>

    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- DPR Header --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="DPR Overview"
            subtitle="Project, engineer and submission details."
            icon="📋"
        />

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Project
                </div>

                <div class="mt-1 font-semibold text-gray-800">
                    {{ $dpr->project?->project_name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Engineer
                </div>

                <div class="mt-1 font-semibold text-gray-800">
                    {{ $dpr->user?->name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    DPR Date
                </div>

                <div class="mt-1 font-semibold text-gray-800">
                    {{ $dpr->dpr_date?->format('d/m/Y') ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Weather
                </div>

                <div class="mt-1 font-semibold text-gray-800">
                    {{ $dpr->weather ?: '-' }}
                </div>
            </div>

        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-8">

        @php
            $summaryCards = [
                ['label' => 'Labour', 'value' => $uniqueLabourCount],
                ['label' => 'Work Done', 'value' => $dpr->workDoneItems->count()],
                ['label' => 'Received', 'value' => $dpr->materialReceipts->count()],
                ['label' => 'Consumed', 'value' => $dpr->materialConsumptions->count()],
                ['label' => 'Required', 'value' => $dpr->materialRequirements->count()],
                ['label' => 'Issues', 'value' => $dpr->siteIssues->count()],
                ['label' => 'Machinery', 'value' => $dpr->machineryTools->count()],
                ['label' => 'Photos', 'value' => $dpr->photos->count()],
            ];
        @endphp

        @foreach($summaryCards as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    {{ $card['label'] }}
                </div>

                <div class="mt-1 text-2xl font-bold text-gray-800">
                    {{ $card['value'] }}
                </div>
            </div>
        @endforeach

    </div>

    {{-- Mobile Section Navigation --}}
    <div class="sticky top-0 z-20 -mx-1 mt-5 overflow-x-auto bg-gray-50/95 px-1 py-2 backdrop-blur lg:static lg:bg-transparent lg:py-0">
        <div class="flex min-w-max gap-2">
            <a href="#dpr-labour" class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">Labour</a>
            <a href="#dpr-work" class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">Work Done</a>
            <a href="#dpr-received" class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">Received</a>
            <a href="#dpr-consumed" class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">Consumed</a>
            <a href="#dpr-required" class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">Required</a>
            <a href="#dpr-issues" class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">Issues</a>
            <a href="#dpr-machinery" class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">Machinery</a>
            <a href="#dpr-photos" class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">Photos</a>
            <a href="#dpr-tomorrow" class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">Tomorrow</a>
        </div>
    </div>

    {{-- Labour Attendance --}}
    <div id="dpr-labour" class="mt-6 scroll-mt-20 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="Labour Attendance"
            subtitle="Attendance-linked labour details for this DPR."
            icon="👷"
        />

        @if($dpr->labourAttendances->isNotEmpty())

            <div class="mb-5 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase text-gray-500">Total Labour</div>
                    <div class="mt-1 text-2xl font-bold text-gray-800">{{ $uniqueLabourCount }}</div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase text-gray-500">Present</div>
                    <div class="mt-1 text-2xl font-bold text-gray-800">{{ $presentCount }}</div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase text-gray-500">Absent</div>
                    <div class="mt-1 text-2xl font-bold text-gray-800">{{ $absentCount }}</div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase text-gray-500">Half Day</div>
                    <div class="mt-1 text-2xl font-bold text-gray-800">{{ $halfDayCount }}</div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase text-gray-500">Normal Hrs</div>
                    <div class="mt-1 text-2xl font-bold text-gray-800">{{ $totalNormalHours }}</div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase text-gray-500">OT Hrs</div>
                    <div class="mt-1 text-2xl font-bold text-gray-800">{{ $totalOtHours }}</div>
                </div>

            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[900px] w-full text-sm">

                    <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Labour</th>
                            <th class="px-4 py-3 text-left">Designation</th>
                            <th class="px-4 py-3 text-left">Shift</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Normal Hrs</th>
                            <th class="px-4 py-3 text-right">OT Hrs</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">

                        @foreach($dpr->labourAttendances as $attendance)
                            @foreach($attendance->details as $detail)
                                <tr>
                                    <td class="px-4 py-3">
                                        {{ $detail->labour?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $detail->designationRole?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $attendance->shift?->shift_name ?? $attendance->shift?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $detail->attendanceStatus?->name ?? $detail->attendanceStatus?->code ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ $detail->normal_hours ?? 0 }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ $detail->ot_hours ?? 0 }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach

                    </tbody>
                </table>
            </div>

        @else

            <div class="rounded-lg border border-dashed border-gray-300 px-5 py-8 text-center text-sm text-gray-500">
                No linked Labour Attendance.
            </div>

        @endif
    </div>

    {{-- Work Done --}}
    <div id="dpr-work" class="mt-6 scroll-mt-20 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="Work Done"
            subtitle="Physical work activities completed during the day."
            icon="🏗️"
        />

        @forelse($dpr->workDoneItems as $index => $item)

            <div class="{{ $index > 0 ? 'mt-5' : '' }} rounded-xl border border-gray-200 bg-gray-50 p-5">

                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Work {{ $index + 1 }}
                        </div>

                        <h3 class="mt-1 text-lg font-bold text-gray-800">
                            {{ $item->activity_name ?? $item->activity?->activity_name ?? '-' }}
                        </h3>

                        @if($item->activityMapping?->division?->name)
                            <div class="mt-1 text-sm text-gray-500">
                                {{ $item->activityMapping->division->name }}
                            </div>
                        @endif
                    </div>

                    <div class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-800">
                        {{ $formatQty($item->quantity_completed) }} {{ $item->unit ?? $item->activityMapping?->unit ?? $item->activity?->unit }}
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                    <div>
                        <div class="text-xs font-semibold uppercase text-gray-500">Location</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $item->location_path ?: '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase text-gray-500">Contractor</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $item->contractor?->contractor_name ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase text-gray-500">Status</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $item->execution_status ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase text-gray-500">Progress</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $item->progress_percentage ?? 0 }}%</div>
                    </div>

                </div>

                @if($item->remarks)
                    <div class="mt-4 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700">
                        {{ $item->remarks }}
                    </div>
                @endif

                @if($item->photos->isNotEmpty())
                    <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-6">
                        @foreach($item->photos as $photo)
                            <a href="{{ $photo->file_url }}"
                               target="_blank"
                               rel="noopener">
                                <img src="{{ $photo->file_url }}"
                                     alt="{{ $photo->display_caption }}"
                                     class="h-28 w-full rounded-lg border border-gray-200 bg-white object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>

        @empty

            <div class="rounded-lg border border-dashed border-gray-300 px-5 py-8 text-center text-sm text-gray-500">
                No linked Work Done records.
            </div>

        @endforelse
    </div>

    {{-- Material Received --}}
    <div id="dpr-received" class="mt-6 scroll-mt-20 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="Material Received"
            subtitle="Materials received on site during the DPR day."
            icon="📦"
        />

        @forelse($dpr->materialReceipts as $receipt)

            <div class="{{ !$loop->first ? 'mt-5' : '' }} rounded-xl border border-gray-200 bg-gray-50 p-5">

                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="font-semibold text-gray-800">
                            Receipt #{{ $receipt->id }}
                        </div>

                        <div class="mt-1 text-sm text-gray-500">
                            Vendor: {{ $receipt->vendor?->vendor_name ?? $receipt->vendor_name ?? '-' }}
                        </div>
                    </div>

                    <div class="text-sm text-gray-600">
                        {{ $receipt->status ?? 'Recorded' }}
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-[850px] w-full text-sm">
                        <thead class="bg-white text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left">Material</th>
                                <th class="px-3 py-2 text-left">Brand</th>
                                <th class="px-3 py-2 text-left">Specification</th>
                                <th class="px-3 py-2 text-left">Grade</th>
                                <th class="px-3 py-2 text-right">Quantity</th>
                                <th class="px-3 py-2 text-left">Unit</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($receipt->items as $item)
                                <tr>
                                    <td class="px-3 py-2">
                                        {{ $item->materialType?->material_type_name ?? $item->display_name ?? 'Material' }}
                                    </td>

                                    <td class="px-3 py-2">
                                        {{ $item->brand?->brand_name ?? '-' }}
                                    </td>

                                    <td class="px-3 py-2">
                                        {{ $item->specification?->specification_name ?? '-' }}
                                    </td>

                                    <td class="px-3 py-2">
                                        {{ $item->grade?->grade_name ?? '-' }}
                                    </td>

                                    <td class="px-3 py-2 text-right">
                                        {{ $formatQty($item->quantity_received) }}
                                    </td>

                                    <td class="px-3 py-2">
                                        {{ $item->unit?->unit_name ?? $item->unit?->name ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-3 text-center text-gray-500">
                                        No item rows available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($receipt->photos->isNotEmpty())
                    <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-6">
                        @foreach($receipt->photos as $photo)
                            <a href="{{ $photo->file_url }}"
                               target="_blank"
                               rel="noopener">
                                <img src="{{ $photo->file_url }}"
                                     alt="Material Received Photo"
                                     class="h-28 w-full rounded-lg border border-gray-200 bg-white object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>

        @empty

            <div class="rounded-lg border border-dashed border-gray-300 px-5 py-8 text-center text-sm text-gray-500">
                No linked Material Received records.
            </div>

        @endforelse
    </div>

    {{-- Material Consumed --}}
    <div id="dpr-consumed" class="mt-6 scroll-mt-20 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="Material Consumed"
            subtitle="Materials consumed and wastage recorded during execution."
            icon="🧱"
        />

        @if($dpr->materialConsumptions->isNotEmpty())

            <div class="overflow-x-auto">
                <table class="min-w-[900px] w-full text-sm">

                    <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Record</th>
                            <th class="px-4 py-3 text-left">Material</th>
                            <th class="px-4 py-3 text-right">Consumed</th>
                            <th class="px-4 py-3 text-right">Wastage</th>
                            <th class="px-4 py-3 text-left">Unit</th>
                            <th class="px-4 py-3 text-left">Remarks</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">

                        @foreach($dpr->materialConsumptions as $consumption)
                            @forelse($consumption->items as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        #{{ $consumption->id }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $item->display_name ?: ($item->materialType?->material_type_name ?? 'Material') }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ $formatQty($item->quantity_consumed) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ $formatQty($item->wastage_quantity) }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $item->unit?->unit_name ?? $item->unit?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $item->remarks ?: ($consumption->remarks ?: '-') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-3">#{{ $consumption->id }}</td>
                                    <td class="px-4 py-3">Material</td>
                                    <td class="px-4 py-3 text-right">{{ $formatQty($consumption->quantity_consumed) }}</td>
                                    <td class="px-4 py-3 text-right">{{ $formatQty($consumption->wastage_quantity) }}</td>
                                    <td class="px-4 py-3">{{ $consumption->unit ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $consumption->remarks ?? '-' }}</td>
                                </tr>
                            @endforelse
                        @endforeach

                    </tbody>
                </table>
            </div>

        @else

            <div class="rounded-lg border border-dashed border-gray-300 px-5 py-8 text-center text-sm text-gray-500">
                No linked Material Consumed records.
            </div>

        @endif
    </div>

    {{-- Material Required --}}
    <div id="dpr-required" class="mt-6 scroll-mt-20 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="Material Required"
            subtitle="Material requirements raised during the DPR day."
            icon="📌"
        />

        @if($dpr->materialRequirements->isNotEmpty())

            <div class="overflow-x-auto">
                <table class="min-w-[900px] w-full text-sm">

                    <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Requirement</th>
                            <th class="px-4 py-3 text-left">Material</th>
                            <th class="px-4 py-3 text-right">Required Qty</th>
                            <th class="px-4 py-3 text-left">Unit</th>
                            <th class="px-4 py-3 text-left">Required Date</th>
                            <th class="px-4 py-3 text-left">Priority</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">

                        @foreach($dpr->materialRequirements as $requirement)
                            @forelse($requirement->items as $item)
                                <tr>
                                    <td class="px-4 py-3">#{{ $requirement->id }}</td>

                                    <td class="px-4 py-3">
                                        {{ $item->display_name ?: ($item->materialType?->material_type_name ?? 'Material') }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ $formatQty($item->required_quantity) }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $item->unit?->unit_name ?? $item->unit?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $requirement->required_date?->format('d/m/Y') ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $requirement->priority ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $requirement->status ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-3">#{{ $requirement->id }}</td>
                                    <td class="px-4 py-3">{{ $requirement->material?->material_name ?? 'Material' }}</td>
                                    <td class="px-4 py-3 text-right">{{ $formatQty($requirement->required_quantity) }}</td>
                                    <td class="px-4 py-3">{{ $requirement->unit ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $requirement->required_date?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $requirement->priority ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $requirement->status ?? '-' }}</td>
                                </tr>
                            @endforelse
                        @endforeach

                    </tbody>
                </table>
            </div>

        @else

            <div class="rounded-lg border border-dashed border-gray-300 px-5 py-8 text-center text-sm text-gray-500">
                No linked Material Required records.
            </div>

        @endif
    </div>

    {{-- Site Issues --}}
    <div id="dpr-issues" class="mt-6 scroll-mt-20 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="Site Issues"
            subtitle="Issues, risks and delays reported during the day."
            icon="⚠️"
        />

        @forelse($dpr->siteIssues as $issue)

            @php
                $priorityClass = match($issue->priority) {
                    'Low' => 'bg-gray-100 text-gray-700',
                    'Medium' => 'bg-blue-100 text-blue-800',
                    'High' => 'bg-orange-100 text-orange-800',
                    'Critical' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-700',
                };
            @endphp

            <div class="{{ !$loop->first ? 'mt-5' : '' }} rounded-xl border border-gray-200 bg-gray-50 p-5">

                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">
                            {{ $issue->title }}
                        </h3>

                        <div class="mt-1 text-sm text-gray-500">
                            {{ $issue->issue_type }}
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClass }}">
                            {{ $issue->priority }}
                        </span>

                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700">
                            {{ $issue->status }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                    <div>
                        <div class="text-xs font-semibold uppercase text-gray-500">Location</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $issue->location_path ?: '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase text-gray-500">Activity</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $issue->activity?->activity_name ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase text-gray-500">Responsible</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $issue->responsible_person ?: '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase text-gray-500">Target Closure</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $issue->target_closure_date?->format('d/m/Y') ?? '-' }}</div>
                    </div>

                </div>

                @if($issue->description)
                    <div class="mt-4 rounded-lg border border-gray-200 bg-white px-4 py-3">
                        <div class="text-xs font-semibold uppercase text-gray-500">Description</div>
                        <div class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $issue->description }}</div>
                    </div>
                @endif

                @if($issue->resolution)
                    <div class="mt-4 rounded-lg border border-gray-200 bg-white px-4 py-3">
                        <div class="text-xs font-semibold uppercase text-gray-500">Resolution</div>
                        <div class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $issue->resolution }}</div>
                    </div>
                @endif

                @if($issue->photos->isNotEmpty())
                    <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-6">
                        @foreach($issue->photos as $photo)
                            <a href="{{ $photo->file_url }}"
                               target="_blank"
                               rel="noopener">
                                <img src="{{ $photo->file_url }}"
                                     alt="{{ $photo->display_caption }}"
                                     class="h-28 w-full rounded-lg border border-gray-200 bg-white object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>

        @empty

            <div class="rounded-lg border border-dashed border-gray-300 px-5 py-8 text-center text-sm text-gray-500">
                No linked Site Issues.
            </div>

        @endforelse
    </div>

    {{-- Machinery --}}
    <div id="dpr-machinery" class="mt-6 scroll-mt-20 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="Machinery / Equipment Used"
            subtitle="Machinery and equipment recorded directly in the DPR."
            icon="🚜"
        />

        @if($dpr->machineryTools->isNotEmpty())

            <div class="overflow-x-auto">
                <table class="min-w-[800px] w-full text-sm">

                    <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Machine</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                            <th class="px-4 py-3 text-right">Usage Hrs</th>
                            <th class="px-4 py-3 text-left">Condition</th>
                            <th class="px-4 py-3 text-left">Remarks</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @foreach($dpr->machineryTools as $machine)
                            <tr>
                                <td class="px-4 py-3">
                                    {{ $machine->machineryTool?->machine_name ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ $machine->quantity }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ $machine->usage_hours }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $machine->working_condition }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $machine->remarks ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        @else

            <div class="rounded-lg border border-dashed border-gray-300 px-5 py-8 text-center text-sm text-gray-500">
                No Machinery / Equipment recorded.
            </div>

        @endif
    </div>

    {{-- DPR Photos --}}
    <div id="dpr-photos" class="mt-6 scroll-mt-20 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="DPR Photos"
            subtitle="General site progress photos uploaded with the DPR."
            icon="📷"
        />

        @if($dpr->photos->isNotEmpty())

            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">

                @foreach($dpr->photos as $photo)
                    <a href="{{ asset('storage/' . ltrim($photo->photo_path, '/')) }}"
                       target="_blank"
                       rel="noopener">
                        <img src="{{ asset('storage/' . ltrim($photo->photo_path, '/')) }}"
                             alt="DPR Photo"
                             class="h-40 w-full rounded-xl border border-gray-200 bg-gray-100 object-cover shadow-sm sm:h-56">
                    </a>
                @endforeach

            </div>

        @else

            <div class="rounded-lg border border-dashed border-gray-300 px-5 py-8 text-center text-sm text-gray-500">
                No DPR-level photos uploaded.
            </div>

        @endif
    </div>

    {{-- Tomorrow Plan --}}
    <div id="dpr-tomorrow" class="mt-6 scroll-mt-20 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="Tomorrow Plan"
            subtitle="Planned execution for the upcoming work day."
            icon="📅"
        />

        @if($dpr->tomorrowPlans->isNotEmpty())

            <div class="overflow-x-auto">
                <table class="min-w-[950px] w-full text-sm">

                    <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Activity</th>
                            <th class="px-4 py-3 text-left">Location</th>
                            <th class="px-4 py-3 text-right">Planned Qty</th>
                            <th class="px-4 py-3 text-left">Unit</th>
                            <th class="px-4 py-3 text-right">Labour</th>
                            <th class="px-4 py-3 text-left">Priority</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @foreach($dpr->tomorrowPlans as $plan)

                            @php
                                $planLocation = collect([
                                    $plan->block?->name,
                                    $plan->floor?->name,
                                    $plan->projectUnit?->name,
                                    $plan->room?->name,
                                    $plan->subspace?->name,
                                ])->filter()->implode(' → ');
                            @endphp

                            <tr>
                                <td class="px-4 py-3">
                                    {{ $plan->activity?->activity_name ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $planLocation ?: '-' }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ $formatQty($plan->planned_quantity) }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $plan->unit ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ $plan->planned_labour ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $plan->priority ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $plan->status ?? '-' }}
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>

        @else

            <div class="rounded-lg border border-dashed border-gray-300 px-5 py-8 text-center text-sm text-gray-500">
                No Tomorrow Plan linked.
            </div>

        @endif
    </div>

    {{-- Remarks --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="General Remarks"
                subtitle="Engineer remarks for this DPR."
                icon="📝"
            />

            <div class="whitespace-pre-line text-sm leading-6 text-gray-700">
                {{ $dpr->remarks ?: '-' }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="PMO Remarks"
                subtitle="Review remarks from PMO / Management."
                icon="💬"
            />

            <div class="whitespace-pre-line text-sm leading-6 text-gray-700">
                {{ $dpr->pmo_remarks ?: 'No PMO remarks available.' }}
            </div>
        </div>

    </div>

    {{-- PMO Review --}}
    @if(
        in_array(
            auth()->user()->role->name,
            ['PMO', 'Admin', 'DGM'],
            true
        )
        && $dpr->status === 'Pending'
    )

        <div class="mt-6 rounded-xl border border-blue-200 bg-blue-50 p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="PMO Review"
                subtitle="Approve or return this DPR for correction."
                icon="✅"
            />

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                <form method="POST"
                      action="{{ route('dprs.approve', $dpr->id) }}"
                      class="rounded-xl border border-green-200 bg-white p-5">

                    @csrf

                    <label class="mb-2 block text-sm font-semibold text-gray-800">
                        Approval Remarks
                    </label>

                    <textarea name="pmo_remarks"
                              rows="4"
                              class="w-full rounded-lg border border-gray-300 p-3 text-sm"
                              placeholder="Optional approval remarks"></textarea>

                    <button type="submit"
                            onclick="return confirm('Approve this DPR?')"
                            class="mt-4 w-full rounded-lg bg-green-600 px-6 py-3 font-semibold text-white hover:bg-green-700 sm:w-auto">
                        Approve DPR
                    </button>

                </form>

                <form method="POST"
                      action="{{ route('dprs.reject', $dpr->id) }}"
                      class="rounded-xl border border-red-200 bg-white p-5">

                    @csrf

                    <label class="mb-2 block text-sm font-semibold text-gray-800">
                        Return for Correction <span class="text-red-600">*</span>
                    </label>

                    <textarea name="pmo_remarks"
                              rows="4"
                              class="w-full rounded-lg border border-gray-300 p-3 text-sm"
                              placeholder="Reason for correction"
                              required></textarea>

                    <button type="submit"
                            onclick="return confirm('Return this DPR for correction?')"
                            class="mt-4 w-full rounded-lg bg-red-600 px-6 py-3 font-semibold text-white hover:bg-red-700 sm:w-auto">
                        Return for Correction
                    </button>

                </form>

            </div>
        </div>

    @endif

</div>

<style>
@media print {
    aside,
    header,
    nav,
    button,
    a[href*="/pdf"] {
        display: none !important;
    }

    body {
        background: #ffffff !important;
    }

    .shadow-sm,
    .shadow-lg {
        box-shadow: none !important;
    }
}
</style>

@endsection
