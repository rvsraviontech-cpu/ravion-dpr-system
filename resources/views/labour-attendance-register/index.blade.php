@extends('layouts.app')

@section('content')

@php
    $periodType = $filters['period_type'] ?? 'monthly';

    $selectedMonth = (int) (
        $filters['month']
        ?? now()->month
    );

    $selectedYear = (int) (
        $filters['year']
        ?? now()->year
    );

    $selectedWeekStart = $filters['week_start']
        ?? now()
            ->startOfWeek(\Carbon\Carbon::SUNDAY)
            ->toDateString();

    $statusClasses = [
        'P' => 'border-green-300 bg-green-100 text-green-800',
        'A' => 'border-red-300 bg-red-100 text-red-800',
        'HD' => 'border-amber-300 bg-amber-100 text-amber-800',
        'L' => 'border-blue-300 bg-blue-100 text-blue-800',
        'WO' => 'border-gray-300 bg-gray-100 text-gray-700',
        'H' => 'border-violet-300 bg-violet-100 text-violet-800',
    ];

    $fixedColumnWidth = 250;
@endphp

<x-rds.page-header
    title="Labour Attendance Register"
    subtitle="Monthly and weekly project-wise labour attendance, normal hours, and overtime."
>
    <x-slot:actions>
        <div class="flex flex-wrap items-center gap-2">
           @if(auth()->user()->hasPermission('attendance_register.export'))

    @php
        $exportQuery = http_build_query(
            request()->query(),
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        $excelExportUrl = route(
            'labour-attendance-register.export-excel'
        ) . ($exportQuery !== '' ? '?' . $exportQuery : '');

        $pdfExportUrl = route(
            'labour-attendance-register.export-pdf'
        ) . ($exportQuery !== '' ? '?' . $exportQuery : '');
    @endphp

    <a
        href="{!! $excelExportUrl !!}"
        class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
    >
        Export Excel
    </a>

    <a
        href="{!! $pdfExportUrl !!}"
        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
    >
        Export PDF
    </a>

@endif

            <x-rds.button
                href="{{ route('labour-attendances.index') }}"
                variant="secondary"
            >
                Back to Attendance
            </x-rds.button>
        </div>
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<div class="space-y-6">

    <x-rds.card>
        <form
            method="GET"
            action="{{ route('labour-attendance-register.index') }}"
            x-data="{
                periodType: @js($periodType)
            }"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                <x-rds.select
                    name="period_type"
                    label="Register Type"
                    x-model="periodType"
                >
                    <option value="monthly">
                        Monthly Register
                    </option>

                    <option value="weekly">
                        Weekly Register
                    </option>
                </x-rds.select>

                <div x-show="periodType === 'monthly'">
                    <x-rds.select
                        name="month"
                        label="Month"
                    >
                        @foreach(range(1, 12) as $monthNumber)
                            <option
                                value="{{ $monthNumber }}"
                                @selected(
                                    $selectedMonth === $monthNumber
                                )
                            >
                                {{ \Carbon\Carbon::create(null, $monthNumber, 1)->format('F') }}
                            </option>
                        @endforeach
                    </x-rds.select>
                </div>

                <div x-show="periodType === 'monthly'">
                    <x-rds.select
                        name="year"
                        label="Year"
                    >
                        @foreach($availableYears as $year)
                            <option
                                value="{{ $year }}"
                                @selected(
                                    $selectedYear === (int) $year
                                )
                            >
                                {{ $year }}
                            </option>
                        @endforeach
                    </x-rds.select>
                </div>

                <div x-show="periodType === 'weekly'">
                    <x-rds.input
                        name="week_start"
                        label="Week Starting Sunday"
                        type="date"
                        value="{{ $selectedWeekStart }}"
                    />
                </div>

                <x-rds.select
                    name="project_id"
                    label="Project"
                >
                    <option value="">
                        All Projects
                    </option>

                    @foreach($projects as $project)
                        <option
                            value="{{ $project->id }}"
                            @selected(
                                (string) (
                                    $filters['project_id']
                                    ?? ''
                                )
                                === (string) $project->id
                            )
                        >
                            {{ $project->project_name }}
                        </option>
                    @endforeach
                </x-rds.select>

                <x-rds.select
                    name="shift_id"
                    label="Shift"
                >
                    <option value="">
                        All Shifts
                    </option>

                    @foreach($shifts as $shift)
                        <option
                            value="{{ $shift->id }}"
                            @selected(
                                (string) (
                                    $filters['shift_id']
                                    ?? ''
                                )
                                === (string) $shift->id
                            )
                        >
                            {{ $shift->name }}
                        </option>
                    @endforeach
                </x-rds.select>

                <x-rds.select
                    name="contractor_id"
                    label="Contractor"
                >
                    <option value="">
                        All Contractors
                    </option>

                    @foreach($contractors as $contractor)
                        <option
                            value="{{ $contractor->id }}"
                            @selected(
                                (string) (
                                    $filters['contractor_id']
                                    ?? ''
                                )
                                === (string) $contractor->id
                            )
                        >
                            {{ $contractor->contractor_name }}
                        </option>
                    @endforeach
                </x-rds.select>

                <x-rds.select
                    name="labour_category_id"
                    label="Labour Category"
                >
                    <option value="">
                        All Categories
                    </option>

                    @foreach($labourCategories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(
                                (string) (
                                    $filters['labour_category_id']
                                    ?? ''
                                )
                                === (string) $category->id
                            )
                        >
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </x-rds.select>

                <x-rds.select
                    name="designation_role_id"
                    label="Designation"
                >
                    <option value="">
                        All Designations
                    </option>

                    @foreach($designationRoles as $designation)
                        <option
                            value="{{ $designation->id }}"
                            @selected(
                                (string) (
                                    $filters['designation_role_id']
                                    ?? ''
                                )
                                === (string) $designation->id
                            )
                        >
                            {{ $designation->name }}
                        </option>
                    @endforeach
                </x-rds.select>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Workflow
                    </label>

                    <label class="flex min-h-[42px] items-center gap-3 rounded-lg border border-gray-200 px-3 py-2">
                        <input
                            type="checkbox"
                            name="approved_only"
                            value="1"
                            class="rounded border-gray-300"
                            @checked(
                                $filters['approved_only']
                                ?? true
                            )
                        >

                        <span>
                            <span class="block text-sm font-semibold text-gray-800">
                                Approved Attendance Only
                            </span>

                            <span class="block text-xs text-gray-500">
                                Recommended for official reporting.
                            </span>
                        </span>
                    </label>
                </div>

            </div>

            <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-rds.button
                    href="{{ route('labour-attendance-register.index') }}"
                    variant="secondary"
                >
                    Reset
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Generate Register
                </x-rds.button>
            </div>
        </form>
    </x-rds.card>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">

        @foreach([
            [
                'label' => 'Total Labour',
                'value' => $summary['total_labour'],
                'class' => 'text-gray-900',
            ],
            [
                'label' => 'Present',
                'value' => $summary['present'],
                'class' => 'text-green-700',
            ],
            [
                'label' => 'Absent',
                'value' => $summary['absent'],
                'class' => 'text-red-700',
            ],
            [
                'label' => 'Half Day',
                'value' => $summary['half_day'],
                'class' => 'text-amber-700',
            ],
            [
                'label' => 'Leave',
                'value' => $summary['leave'],
                'class' => 'text-blue-700',
            ],
            [
                'label' => 'Normal Hours',
                'value' => number_format(
                    (float) $summary['normal_hours'],
                    2
                ),
                'class' => 'text-gray-900',
            ],
            [
                'label' => 'OT Hours',
                'value' => number_format(
                    (float) $summary['ot_hours'],
                    2
                ),
                'class' => 'text-violet-700',
            ],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    {{ $card['label'] }}
                </div>

                <div class="mt-2 text-2xl font-bold {{ $card['class'] }}">
                    {{ $card['value'] }}
                </div>
            </div>
        @endforeach

    </div>

    <x-rds.card :padding="false">

        <div class="border border-gray-200 px-4 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">
                        {{ $periodLabel }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Labour and Project columns remain frozen while scrolling through attendance dates.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 text-xs">
                    @foreach([
                        ['code' => 'P', 'label' => 'Present'],
                        ['code' => 'A', 'label' => 'Absent'],
                        ['code' => 'HD', 'label' => 'Half Day'],
                        ['code' => 'L', 'label' => 'Leave'],
                        ['code' => 'WO', 'label' => 'Weekly Off'],
                        ['code' => 'H', 'label' => 'Holiday'],
                    ] as $legend)
                        <span class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-2 py-1 text-gray-600">
                            <span class="font-bold">
                                {{ $legend['code'] }}
                            </span>

                            {{ $legend['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        @forelse($projectGroups as $projectGroup)

            <div class="border-b border-gray-300 last:border-b-0">

                <div class="flex flex-col gap-3 border-b border-blue-200 bg-blue-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-sm font-bold text-blue-900">
                            {{ $projectGroup['project_name'] }}
                        </div>

                        @if($projectGroup['project_code'])
                            <div class="mt-0.5 text-xs text-blue-700">
                                {{ $projectGroup['project_code'] }}
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="rounded-md border border-blue-200 bg-white px-2 py-1 text-blue-800">
                            Labour:
                            <strong>
                                {{ $projectGroup['summary']['total_labour'] }}
                            </strong>
                        </span>

                        <span class="rounded-md border border-green-200 bg-white px-2 py-1 text-green-700">
                            P:
                            <strong>
                                {{ $projectGroup['summary']['present'] }}
                            </strong>
                        </span>

                        <span class="rounded-md border border-red-200 bg-white px-2 py-1 text-red-700">
                            A:
                            <strong>
                                {{ $projectGroup['summary']['absent'] }}
                            </strong>
                        </span>

                        <span class="rounded-md border border-gray-200 bg-white px-2 py-1 text-gray-700">
                            Normal:
                            <strong>
                                {{ number_format(
                                    (float) $projectGroup['summary']['normal_hours'],
                                    2
                                ) }}
                            </strong>
                        </span>

                        <span class="rounded-md border border-violet-200 bg-white px-2 py-1 text-violet-700">
                            OT:
                            <strong>
                                {{ number_format(
                                    (float) $projectGroup['summary']['ot_hours'],
                                    2
                                ) }}
                            </strong>
                        </span>
                    </div>
                </div>

                <div class="w-full overflow-x-auto border-x border border-gray-200">
                    <table
                        class="{{ $periodType === 'weekly'
                            ? 'w-full table-fixed'
                            : 'min-w-max table-fixed' }} border-collapse"
                    >

                        <colgroup>
                            <col class="{{ $periodType === 'weekly' ? 'w-[240px]' : 'w-[280px]' }}">

                            @foreach($dateColumns as $dateColumn)
                                <col class="w-[44px]">
                            @endforeach

                            <col class="w-[58px]">
                            <col class="w-[58px]">
                            <col class="w-[62px]">
                            <col class="w-[58px]">
                            <col class="w-[92px]">
                            <col class="w-[82px]">
                        </colgroup>

                        <thead>
                            <tr>
                                <th
    class="sticky left-0 top-0 z-40 w-[240px] min-w-[240px] border border-gray-300 bg-gray-100 px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-700"
>
    Labour
</th>

                                @foreach($dateColumns as $dateColumn)
                                    <th
                                        class="sticky top-0 z-30 h-[50px] border border-gray-200 px-1 py-1.5 text-center {{ $dateColumn['is_sunday'] ? 'bg-red-50 text-red-600' : 'bg-gray-50 text-gray-700' }}"
                                        title="{{ $dateColumn['date']->format('l, d F Y') }}"
                                    >
                                        <div class="text-xs font-bold">
                                            {{ $dateColumn['day_number'] }}
                                        </div>

                                        <div class="mt-0.5 text-[6px] font-normal uppercase leading-none tracking-tighter">
                                            {{ $dateColumn['weekday_short'] }}
                                        </div>
                                    </th>
                                @endforeach

                                <th class="sticky top-0 z-30 border border-gray-200 bg-green-50 px-2 py-3 text-center text-xs font-bold text-green-700">
                                    P
                                </th>

                                <th class="sticky top-0 z-30 border border-gray-200 bg-red-50 px-2 py-3 text-center text-xs font-bold text-red-700">
                                    A
                                </th>

                                <th class="sticky top-0 z-30 border border-gray-200 bg-amber-50 px-2 py-3 text-center text-xs font-bold text-amber-700">
                                    HD
                                </th>

                                <th class="sticky top-0 z-30 border border-gray-200 bg-blue-50 px-2 py-3 text-center text-xs font-bold text-blue-700">
                                    L
                                </th>

                                <th class="sticky top-0 z-30 border border-gray-200 bg-gray-100 px-2 py-3 text-right text-xs font-bold text-gray-700">
                                    Normal
                                </th>

                                <th class="sticky top-0 z-30 border border-gray-200 bg-violet-50 px-2 py-3 text-right text-xs font-bold text-violet-700">
                                    OT
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($projectGroup['rows'] as $row)

                                <tr class="group">

                                    <td
    class="sticky left-0 z-20 w-[240px] min-w-[240px] border border-gray-200 bg-white px-3 py-2 group-hover:bg-blue-50"
>
                                        <div class="whitespace-normal break-words text-sm font-semibold leading-5 text-gray-900">
                                            {{ $row['labour_name'] }}
                                        </div>

                                        <div class="mt-0.5 whitespace-normal break-words text-[11px] leading-4 text-gray-500">
                                            {{ $row['designation'] }}
                                        </div>
                                    </td>

                                    @foreach($dateColumns as $dateColumn)
                                        @php
                                            $dateKey = $dateColumn['key'];

                                            $dayEntry =
                                                $row['days'][$dateKey]
                                                ?? null;

                                            $dayCode =
                                                $dayEntry['code']
                                                ?? '—';

                                            $cellClass =
                                                $statusClasses[$dayCode]
                                                ?? 'border-gray-200 bg-white text-gray-400';

                                            $tooltipParts = [];

                                            if($dayEntry) {
                                                $tooltipParts[] =
                                                    $dayEntry['label']
                                                    ?? $dayCode;

                                                if($dayEntry['working_status']) {
                                                    $tooltipParts[] =
                                                        'Working: '
                                                        . $dayEntry['working_status'];
                                                }

                                                if($dayEntry['check_in']) {
                                                    $tooltipParts[] =
                                                        'In: '
                                                        . $dayEntry['check_in'];
                                                }

                                                if($dayEntry['check_out']) {
                                                    $tooltipParts[] =
                                                        'Out: '
                                                        . $dayEntry['check_out'];
                                                }

                                                $tooltipParts[] =
                                                    'Normal: '
                                                    . number_format(
                                                        (float) $dayEntry['normal_hours'],
                                                        2
                                                    );

                                                $tooltipParts[] =
                                                    'OT: '
                                                    . number_format(
                                                        (float) $dayEntry['ot_hours'],
                                                        2
                                                    );

                                                if($dayEntry['attendance_number']) {
                                                    $tooltipParts[] =
                                                        $dayEntry['attendance_number'];
                                                }
                                            }

                                            $tooltip = implode(
                                                ' | ',
                                                $tooltipParts
                                            );
                                        @endphp

                                        <td class="border border-gray-200 bg-white px-1 py-1.5 text-center group-hover:bg-blue-50">
                                            @if($dayEntry)
                                                <span
                                                    class="inline-flex h-6 min-w-6 items-center justify-center rounded border px-1 text-[10px] font-bold {{ $cellClass }}"
                                                    title="{{ $tooltip }}"
                                                >
                                                    {{ $dayCode }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-300">
                                                    —
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="border border-gray-200 bg-green-50/50 px-2 py-2 text-center text-sm font-semibold text-green-700">
                                        {{ $row['totals']['present'] }}
                                    </td>

                                    <td class="border border-gray-200 bg-red-50/50 px-2 py-2 text-center text-sm font-semibold text-red-700">
                                        {{ $row['totals']['absent'] }}
                                    </td>

                                    <td class="border border-gray-200 bg-amber-50/50 px-2 py-2 text-center text-sm font-semibold text-amber-700">
                                        {{ $row['totals']['half_day'] }}
                                    </td>

                                    <td class="border border-gray-200 bg-blue-50/50 px-2 py-2 text-center text-sm font-semibold text-blue-700">
                                        {{ $row['totals']['leave'] }}
                                    </td>

                                    <td class="border border-gray-200 bg-gray-50 px-2 py-2 text-right text-sm font-semibold text-gray-800">
                                        {{ number_format(
                                            (float) $row['totals']['normal_hours'],
                                            2
                                        ) }}
                                    </td>

                                    <td class="border border-gray-200 bg-violet-50/50 px-2 py-2 text-right text-sm font-semibold text-violet-700">
                                        {{ number_format(
                                            (float) $row['totals']['ot_hours'],
                                            2
                                        ) }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                        <tfoot>
                            <tr>
                                <td
                                    class="sticky left-0 z-20 border-2 border-gray-300 bg-gray-100 px-3 py-2.5 text-sm font-bold text-gray-800"
                                >
                                    Project Totals
                                </td>

                                <td
                                    colspan="{{ $dateColumns->count() }}"
                                    class="border-2 border-gray-300 bg-gray-100 px-3 py-2.5 text-xs text-gray-500"
                                >
                                    {{ $projectGroup['summary']['total_labour'] }} Labour
                                </td>

                                <td class="border-2 border-gray-300 bg-green-50 px-2 py-2.5 text-center text-sm font-bold text-green-700">
                                    {{ $projectGroup['summary']['present'] }}
                                </td>

                                <td class="border-2 border-gray-300 bg-red-50 px-2 py-2.5 text-center text-sm font-bold text-red-700">
                                    {{ $projectGroup['summary']['absent'] }}
                                </td>

                                <td class="border-2 border-gray-300 bg-amber-50 px-2 py-2.5 text-center text-sm font-bold text-amber-700">
                                    {{ $projectGroup['summary']['half_day'] }}
                                </td>

                                <td class="border-2 border-gray-300 bg-blue-50 px-2 py-2.5 text-center text-sm font-bold text-blue-700">
                                    {{ $projectGroup['summary']['leave'] }}
                                </td>

                                <td class="border-2 border-gray-300 bg-gray-100 px-2 py-2.5 text-right text-sm font-bold text-gray-900">
                                    {{ number_format(
                                        (float) $projectGroup['summary']['normal_hours'],
                                        2
                                    ) }}
                                </td>

                                <td class="border-2 border-gray-300 bg-violet-50 px-2 py-2.5 text-right text-sm font-bold text-violet-700">
                                    {{ number_format(
                                        (float) $projectGroup['summary']['ot_hours'],
                                        2
                                    ) }}
                                </td>
                            </tr>
                        </tfoot>

                    </table>
                </div>

            </div>

        @empty

            <div class="px-4 py-14 text-center">
                <div class="text-sm font-semibold text-gray-700">
                    No attendance records were found for {{ $periodLabel }}.
                </div>

                <div class="mt-1 text-xs text-gray-500">
                    Change the filters or confirm that attendance has been approved for this period.
                </div>
            </div>

        @endforelse

    </x-rds.card>

</div>

@endsection
