@extends('layouts.app')

@section('content')

@php
    $scopeLabels = [
        'all_projects' => 'All Projects',
        'specific_project' => 'Specific Project',
        'all_engineers' => 'All Engineers',
        'specific_engineer' => 'Specific Engineer',
    ];

    $periodLabels = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'half_yearly' => 'Half Yearly',
        'yearly' => 'Yearly',
        'custom' => 'Custom Range',
    ];

    $statusLabels = [
        'all_eligible' => 'All Eligible',
        'calculated' => 'Calculated',
        'submitted' => 'Submitted',
        'approved' => 'Approved',
        'paid' => 'Paid',
    ];
@endphp

<x-rds.page-header
    title="Labour Wage Reports"
    subtitle="Project-wise and engineer-wise wage reporting across flexible periods."
>
    <x-slot:actions>
        @if($wageSheets->isNotEmpty())
            <a
                href="{{ route('reports.labour-wages.pdf', request()->query()) }}"
                class="inline-flex items-center justify-center rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-900"
            >
                Download PDF
            </a>
        @endif
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<div
    class="space-y-5"
    x-data="{
        scope: @js($filters['scope']),
        period: @js($filters['period'])
    }"
>
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-bold text-gray-900">Report Filters</h2>
            <p class="mt-1 text-xs text-gray-500">
                Reports read existing Weekly Wage Sheets only; payroll is not recalculated here.
            </p>
        </div>

        <form method="GET" action="{{ route('reports.labour-wages.index') }}" class="p-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Report Scope
                    </label>
                    <select
                        name="scope"
                        x-model="scope"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm"
                    >
                        @foreach($scopeLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Period
                    </label>
                    <select
                        name="period"
                        x-model="period"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm"
                    >
                        @foreach($periodLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Status
                    </label>
                    <select
                        name="status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm"
                    >
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-show="scope === 'specific_project'" x-cloak>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Project
                    </label>
                    <select
                        name="project_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm"
                    >
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option
                                value="{{ $project->id }}"
                                @selected((string) $filters['project_id'] === (string) $project->id)
                            >
                                {{ $project->project_name }}
                                @if($project->project_code)
                                    — {{ $project->project_code }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-show="scope === 'specific_engineer'" x-cloak>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Engineer
                    </label>
                    <select
                        name="engineer_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm"
                    >
                        <option value="">Select Engineer</option>
                        @foreach($engineers as $engineer)
                            <option
                                value="{{ $engineer['id'] }}"
                                @selected((string) $filters['engineer_id'] === (string) $engineer['id'])
                            >
                                {{ $engineer['name'] }}
                                @if($engineer['employee_code'])
                                    — {{ $engineer['employee_code'] }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-show="period === 'daily'" x-cloak>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Date
                    </label>
                    <input
                        type="date"
                        name="date"
                        value="{{ $filters['date'] }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                    >
                </div>

                <div x-show="period === 'weekly'" x-cloak>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Week
                    </label>
                    <input
                        type="date"
                        name="week_start"
                        value="{{ $filters['week_start'] }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                    >
                    <p class="mt-1 text-[11px] text-gray-500">
                        Any date in the week is accepted; it resolves to Sunday–Saturday.
                    </p>
                </div>

                <div x-show="period === 'monthly'" x-cloak>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Month
                    </label>
                    <input
                        type="month"
                        name="month"
                        value="{{ $filters['month'] }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                    >
                </div>

                <div
                    x-show="['quarterly','half_yearly','yearly'].includes(period)"
                    x-cloak
                >
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Year
                    </label>
                    <input
                        type="number"
                        name="year"
                        value="{{ $filters['year'] }}"
                        min="2020"
                        max="2100"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                    >
                </div>

                <div x-show="period === 'quarterly'" x-cloak>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Quarter
                    </label>
                    <select
                        name="quarter"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm"
                    >
                        @foreach([1, 2, 3, 4] as $quarter)
                            <option value="{{ $quarter }}" @selected((string) $filters['quarter'] === (string) $quarter)>
                                Q{{ $quarter }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-show="period === 'half_yearly'" x-cloak>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Half Year
                    </label>
                    <select
                        name="half_year"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm"
                    >
                        <option value="1" @selected((string) $filters['half_year'] === '1')>
                            H1 — Jan to Jun
                        </option>
                        <option value="2" @selected((string) $filters['half_year'] === '2')>
                            H2 — Jul to Dec
                        </option>
                    </select>
                </div>

                <div x-show="period === 'custom'" x-cloak>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        From Date
                    </label>
                    <input
                        type="date"
                        name="from_date"
                        value="{{ $filters['from_date'] }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                    >
                </div>

                <div x-show="period === 'custom'" x-cloak>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        To Date
                    </label>
                    <input
                        type="date"
                        name="to_date"
                        value="{{ $filters['to_date'] }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                    >
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
                <div class="text-xs text-gray-500">
                    Monthly, quarterly, half-yearly and yearly reports classify a Weekly Wage Sheet by its Sunday week-start date.
                </div>

                <div class="flex gap-2">
                    <a
                        href="{{ route('reports.labour-wages.index') }}"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Reset
                    </a>

                    <button
                        type="submit"
                        class="rounded-lg bg-[#0F2A52] px-5 py-2 text-sm font-semibold text-white hover:bg-[#173b70]"
                    >
                        Generate Report
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-8">
        @foreach([
            ['Wage Sheets', $totals['wage_sheet_count']],
            ['Projects', $totals['project_count']],
            ['Unique Labour', $totals['labour_count']],
            ['Payable Days', number_format($totals['payable_days'], 2)],
            ['Normal Wages', '₹' . number_format($totals['normal_wages'], 2)],
            ['OT Amount', '₹' . number_format($totals['ot_amount'], 2)],
            ['Deductions', '₹' . number_format($totals['deductions'], 2)],
            ['Net Payable', '₹' . number_format($totals['net_payable'], 2)],
        ] as [$label, $value])
            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
                <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">
                    {{ $label }}
                </div>
                <div class="mt-1 text-base font-bold text-gray-900">
                    {{ $value }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-3">
            <div>
                <h2 class="text-sm font-bold text-gray-900">
                    {{ $scopeLabels[$filters['scope']] }} — {{ $periodLabel }}
                </h2>
                <p class="mt-1 text-xs text-gray-500">
                    {{ $fromDate->format('d M Y') }} to {{ $toDate->format('d M Y') }}
                </p>
            </div>

            @if($wageSheets->isNotEmpty())
                <a
                    href="{{ route('reports.labour-wages.pdf', request()->query()) }}"
                    class="rounded-lg bg-slate-800 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-900"
                >
                    Download PDF
                </a>
            @endif
        </div>

        @if($wageSheets->isEmpty())
            <div class="px-5 py-14 text-center">
                <div class="text-sm font-semibold text-gray-700">
                    No wage sheets found.
                </div>
                <div class="mt-1 text-xs text-gray-500">
                    Adjust the report scope, period, or status and generate again.
                </div>
            </div>

        @elseif(in_array($filters['scope'], ['all_engineers', 'specific_engineer'], true))
            <div class="space-y-4 p-4">
                @foreach($engineerGroups as $group)
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <div class="bg-blue-50 px-4 py-3">
                            <div class="font-bold text-[#0F2A52]">
                                {{ $group['label'] }}
                            </div>
                            <div class="mt-0.5 text-xs text-blue-700">
                                {{ $group['project_count'] }} project(s) ·
                                {{ $group['labour_count'] }} unique labour ·
                                ₹{{ number_format($group['net_payable'], 2) }} net payable
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[1100px] text-xs">
                                <thead class="bg-gray-50 text-gray-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Project</th>
                                        <th class="px-3 py-2 text-right">Weeks</th>
                                        <th class="px-3 py-2 text-right">Labour</th>
                                        <th class="px-3 py-2 text-right">Payable Days</th>
                                        <th class="px-3 py-2 text-right">Normal Wages</th>
                                        <th class="px-3 py-2 text-right">OT Hrs</th>
                                        <th class="px-3 py-2 text-right">OT Amount</th>
                                        <th class="px-3 py-2 text-right">Additions</th>
                                        <th class="px-3 py-2 text-right">Deductions</th>
                                        <th class="px-3 py-2 text-right">Net Payable</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($group['projects'] as $row)
                                        <tr>
                                            <td class="px-3 py-2.5">
                                                <div class="font-semibold text-gray-900">{{ $row['project_name'] }}</div>
                                                <div class="text-[10px] text-gray-500">{{ $row['project_code'] ?: '—' }}</div>
                                            </td>
                                            <td class="px-3 py-2.5 text-right">{{ $row['wage_sheet_count'] }}</td>
                                            <td class="px-3 py-2.5 text-right">{{ $row['labour_count'] }}</td>
                                            <td class="px-3 py-2.5 text-right">{{ number_format($row['payable_days'], 2) }}</td>
                                            <td class="px-3 py-2.5 text-right">₹{{ number_format($row['normal_wages'], 2) }}</td>
                                            <td class="px-3 py-2.5 text-right">{{ number_format($row['ot_hours'], 2) }}</td>
                                            <td class="px-3 py-2.5 text-right">₹{{ number_format($row['ot_amount'], 2) }}</td>
                                            <td class="px-3 py-2.5 text-right">₹{{ number_format($row['additions'], 2) }}</td>
                                            <td class="px-3 py-2.5 text-right">₹{{ number_format($row['deductions'], 2) }}</td>
                                            <td class="px-3 py-2.5 text-right font-bold text-[#0F2A52]">₹{{ number_format($row['net_payable'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                @if($filters['scope'] === 'all_engineers')
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                        Shared projects are shown once under a combined Engineer Assignment group so the same wages are not counted twice.
                    </div>
                @endif
            </div>

        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1250px] text-xs">
                    <thead class="bg-[#0F2A52] text-white">
                        <tr>
                            <th class="px-3 py-3 text-left">Project</th>
                            <th class="px-3 py-3 text-left">Engineer(s)</th>
                            <th class="px-3 py-3 text-right">Weeks</th>
                            <th class="px-3 py-3 text-right">Labour</th>
                            <th class="px-3 py-3 text-right">Payable Days</th>
                            <th class="px-3 py-3 text-right">Normal Wages</th>
                            <th class="px-3 py-3 text-right">OT Hrs</th>
                            <th class="px-3 py-3 text-right">OT Amount</th>
                            <th class="px-3 py-3 text-right">Additions</th>
                            <th class="px-3 py-3 text-right">Deductions</th>
                            <th class="px-3 py-3 text-right">Net Payable</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($projectRows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3">
                                    <div class="font-semibold text-gray-900">{{ $row['project_name'] }}</div>
                                    <div class="mt-0.5 text-[10px] text-gray-500">{{ $row['project_code'] ?: '—' }}</div>
                                </td>
                                <td class="px-3 py-3 text-gray-700">{{ $row['engineer_names'] ?: 'Unassigned' }}</td>
                                <td class="px-3 py-3 text-right">{{ $row['wage_sheet_count'] }}</td>
                                <td class="px-3 py-3 text-right">{{ $row['labour_count'] }}</td>
                                <td class="px-3 py-3 text-right">{{ number_format($row['payable_days'], 2) }}</td>
                                <td class="px-3 py-3 text-right">₹{{ number_format($row['normal_wages'], 2) }}</td>
                                <td class="px-3 py-3 text-right">{{ number_format($row['ot_hours'], 2) }}</td>
                                <td class="px-3 py-3 text-right">₹{{ number_format($row['ot_amount'], 2) }}</td>
                                <td class="px-3 py-3 text-right">₹{{ number_format($row['additions'], 2) }}</td>
                                <td class="px-3 py-3 text-right">₹{{ number_format($row['deductions'], 2) }}</td>
                                <td class="px-3 py-3 text-right font-bold text-[#0F2A52]">₹{{ number_format($row['net_payable'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
