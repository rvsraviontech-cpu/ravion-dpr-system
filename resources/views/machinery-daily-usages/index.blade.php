@extends('layouts.app')

@section('content')
@php
    $formatNumber = function ($value, $decimals = 2) {
        if ($value === null) {
            return '—';
        }

        return rtrim(
            rtrim(
                number_format((float) $value, $decimals, '.', ''),
                '0'
            ),
            '.'
        );
    };
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Daily Machinery Logs
            </h1>

            <p class="mt-1 text-sm text-gray-600">
                Daily equipment usage, operating hours, idle time,
                breakdowns and site activity.
            </p>
        </div>

        <a href="{{ route('machinery-daily-usages.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
            + New Daily Log
        </a>
    </div>


    {{-- ============================================================
         MESSAGES
    ============================================================ --}}
    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="font-semibold">
                Please correct the following:
            </div>

            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- ============================================================
         FILTERS
    ============================================================ --}}
    <section class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-900">
                Filters
            </h2>
        </div>

        <form method="GET"
              action="{{ route('machinery-daily-usages.index') }}"
              class="p-5">

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                {{-- Project --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Project
                    </label>

                    <select name="project_id"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                        <option value="">
                            All Projects
                        </option>

                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}"
                                @selected(
                                    (string) request('project_id')
                                    === (string) $project->id
                                )>
                                {{ $project->project_code }}
                                — {{ $project->project_name }}
                            </option>
                        @endforeach

                    </select>
                </div>


                {{-- Equipment --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Equipment
                    </label>

                    <select name="machinery_equipment_id"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                        <option value="">
                            All Equipment
                        </option>

                        @foreach ($equipment as $item)
                            @php
                                $equipmentName =
                                    $item->equipment_name
                                    ?: $item->machineryTool?->machine_name
                                    ?: 'Equipment';
                            @endphp

                            <option value="{{ $item->id }}"
                                @selected(
                                    (string) request('machinery_equipment_id')
                                    === (string) $item->id
                                )>
                                {{ $item->equipment_code }}
                                — {{ $equipmentName }}
                            </option>
                        @endforeach

                    </select>
                </div>


                {{-- From --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        From Date
                    </label>

                    <input type="date"
                           name="from_date"
                           value="{{ request('from_date') }}"
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>


                {{-- To --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        To Date
                    </label>

                    <input type="date"
                           name="to_date"
                           value="{{ request('to_date') }}"
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>


                {{-- Condition --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Working Condition
                    </label>

                    <select name="working_condition"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                        <option value="">
                            All Conditions
                        </option>

                        @foreach ($workingConditions as $value => $label)
                            <option value="{{ $value }}"
                                @selected(
                                    request('working_condition') === $value
                                )>
                                {{ $label }}
                            </option>
                        @endforeach

                    </select>
                </div>


                {{-- Status --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Status
                    </label>

                    <select name="status"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                        <option value="">
                            All Statuses
                        </option>

                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}"
                                @selected(request('status') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach

                    </select>
                </div>

            </div>


            <div class="mt-5 flex flex-wrap gap-2">

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                    Apply Filters
                </button>

                <a href="{{ route('machinery-daily-usages.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Clear
                </a>

            </div>

        </form>
    </section>


    {{-- ============================================================
         RESULT SUMMARY
    ============================================================ --}}
    <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

        <div class="text-sm text-gray-600">
            Showing
            <span class="font-semibold text-gray-900">
                {{ $usages->firstItem() ?? 0 }}
            </span>
            –
            <span class="font-semibold text-gray-900">
                {{ $usages->lastItem() ?? 0 }}
            </span>
            of
            <span class="font-semibold text-gray-900">
                {{ $usages->total() }}
            </span>
            logs
        </div>

    </div>


    {{-- ============================================================
         DESKTOP TABLE
    ============================================================ --}}
    <section class="hidden overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:block">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Date
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Project
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Equipment
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Qty
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Shift
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Site Hrs
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Operating
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Idle
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Breakdown
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Condition
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Status
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Action
                        </th>

                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">

                    @forelse ($usages as $usage)

                        @php
                            $equipmentName =
                                $usage->equipment?->equipment_name
                                ?: $usage->equipment?->machineryTool?->machine_name
                                ?: 'Equipment';

                            $conditionLabel =
                                $workingConditions[
                                    $usage->working_condition
                                ]
                                ?? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $usage->working_condition ?? ''
                                    )
                                );

                            $statusLabel =
                                $statuses[$usage->status]
                                ?? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $usage->status ?? ''
                                    )
                                );
                        @endphp

                        <tr class="hover:bg-gray-50">

                            {{-- Date --}}
                            <td class="whitespace-nowrap px-4 py-4 align-top">

                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $usage->usage_date?->format('d M Y') }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    #{{ $usage->id }}
                                </div>

                            </td>


                            {{-- Project --}}
                            <td class="px-4 py-4 align-top">

                                <div class="text-sm font-medium text-gray-900">
                                    {{ $usage->project?->project_name ?? '—' }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $usage->project?->project_code ?? '' }}
                                </div>

                            </td>


                            {{-- Equipment --}}
                            <td class="px-4 py-4 align-top">

                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $equipmentName }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $usage->equipment?->equipment_code ?? '—' }}
                                </div>

                            </td>


                            {{-- Quantity --}}
                            <td class="whitespace-nowrap px-4 py-4 text-right align-top text-sm text-gray-900">

                                {{ $formatNumber($usage->quantity_used, 3) }}

                                <span class="text-xs text-gray-500">
                                    {{ $usage->unit }}
                                </span>

                            </td>


                            {{-- Shift --}}
                            <td class="whitespace-nowrap px-4 py-4 align-top text-sm text-gray-700">
                                {{ ucfirst(str_replace('_', ' ', $usage->shift)) }}
                            </td>


                            {{-- Site Hours --}}
                            <td class="whitespace-nowrap px-4 py-4 text-right align-top text-sm text-gray-700">
                                {{ $formatNumber($usage->site_hours) }}
                            </td>


                            {{-- Operating --}}
                            <td class="whitespace-nowrap px-4 py-4 text-right align-top">

                                <span class="text-sm font-semibold text-gray-900">
                                    {{ $formatNumber($usage->final_operating_hours) }}
                                </span>

                            </td>


                            {{-- Idle --}}
                            <td class="whitespace-nowrap px-4 py-4 text-right align-top text-sm text-gray-700">
                                {{ $formatNumber($usage->idle_hours) }}
                            </td>


                            {{-- Breakdown --}}
                            <td class="whitespace-nowrap px-4 py-4 text-right align-top">

                                @if ((float) $usage->breakdown_hours > 0)
                                    <span class="font-semibold text-red-700">
                                        {{ $formatNumber($usage->breakdown_hours) }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-700">
                                        0
                                    </span>
                                @endif

                            </td>


                            {{-- Condition --}}
                            <td class="whitespace-nowrap px-4 py-4 align-top">

                                @if ($usage->working_condition === 'working')

                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                        {{ $conditionLabel }}
                                    </span>

                                @elseif ($usage->working_condition === 'breakdown')

                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                        {{ $conditionLabel }}
                                    </span>

                                @elseif ($usage->working_condition === 'maintenance')

                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        {{ $conditionLabel }}
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                        {{ $conditionLabel }}
                                    </span>

                                @endif

                            </td>


                            {{-- Status --}}
                            <td class="whitespace-nowrap px-4 py-4 align-top">

                                @if ($usage->status === 'submitted')

                                    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                        {{ $statusLabel }}
                                    </span>

                                @elseif ($usage->status === 'verified')

                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                        {{ $statusLabel }}
                                    </span>

                                @elseif ($usage->status === 'cancelled')

                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                        {{ $statusLabel }}
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        {{ $statusLabel }}
                                    </span>

                                @endif

                            </td>


                            {{-- Action --}}
                            <td class="whitespace-nowrap px-4 py-4 text-right align-top">

                                <a href="{{ route('machinery-daily-usages.show', $usage) }}"
                                   class="text-sm font-semibold text-blue-700 hover:text-blue-900">
                                    View
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="12"
                                class="px-6 py-12 text-center">

                                <div class="text-base font-semibold text-gray-900">
                                    No machinery logs found
                                </div>

                                <div class="mt-1 text-sm text-gray-500">
                                    Change the filters or create the first daily machinery log.
                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

        </div>
    </section>


    {{-- ============================================================
         MOBILE / TABLET CARDS
    ============================================================ --}}
    <div class="space-y-4 lg:hidden">

        @forelse ($usages as $usage)

            @php
                $equipmentName =
                    $usage->equipment?->equipment_name
                    ?: $usage->equipment?->machineryTool?->machine_name
                    ?: 'Equipment';

                $conditionLabel =
                    $workingConditions[$usage->working_condition]
                    ?? ucfirst(
                        str_replace(
                            '_',
                            ' ',
                            $usage->working_condition ?? ''
                        )
                    );

                $statusLabel =
                    $statuses[$usage->status]
                    ?? ucfirst(
                        str_replace(
                            '_',
                            ' ',
                            $usage->status ?? ''
                        )
                    );
            @endphp

            <a href="{{ route('machinery-daily-usages.show', $usage) }}"
               class="block rounded-xl border border-gray-200 bg-white p-4 shadow-sm">

                <div class="flex items-start justify-between gap-3">

                    <div>

                        <div class="text-xs text-gray-500">
                            {{ $usage->usage_date?->format('d M Y') }}
                            ·
                            {{ ucfirst(str_replace('_', ' ', $usage->shift)) }}
                        </div>

                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $equipmentName }}
                        </div>

                        <div class="mt-1 text-xs text-gray-500">
                            {{ $usage->equipment?->equipment_code }}
                        </div>

                    </div>

                    @if ($usage->status === 'submitted')

                        <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">
                            {{ $statusLabel }}
                        </span>

                    @elseif ($usage->status === 'verified')

                        <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">
                            {{ $statusLabel }}
                        </span>

                    @elseif ($usage->status === 'cancelled')

                        <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">
                            {{ $statusLabel }}
                        </span>

                    @else

                        <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">
                            {{ $statusLabel }}
                        </span>

                    @endif

                </div>


                <div class="mt-4 text-sm text-gray-700">
                    {{ $usage->project?->project_code }}
                    — {{ $usage->project?->project_name }}
                </div>


                <div class="mt-4 grid grid-cols-4 gap-3 border-t border-gray-100 pt-4">

                    <div>
                        <div class="text-xs text-gray-500">
                            Qty
                        </div>

                        <div class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $formatNumber($usage->quantity_used, 3) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">
                            Site Hrs
                        </div>

                        <div class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $formatNumber($usage->site_hours) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">
                            Operating
                        </div>

                        <div class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $formatNumber($usage->final_operating_hours) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">
                            Breakdown
                        </div>

                        <div class="mt-1 text-sm font-semibold
                            {{ (float) $usage->breakdown_hours > 0
                                ? 'text-red-700'
                                : 'text-gray-900' }}">
                            {{ $formatNumber($usage->breakdown_hours) }}
                        </div>
                    </div>

                </div>


                <div class="mt-4 flex items-center justify-between">

                    <span class="text-xs font-medium text-gray-600">
                        {{ $conditionLabel }}
                    </span>

                    <span class="text-sm font-semibold text-blue-700">
                        View →
                    </span>

                </div>

            </a>

        @empty

            <div class="rounded-xl border border-gray-200 bg-white px-6 py-12 text-center shadow-sm">

                <div class="font-semibold text-gray-900">
                    No machinery logs found
                </div>

                <div class="mt-1 text-sm text-gray-500">
                    Change the filters or create a new daily log.
                </div>

            </div>

        @endforelse

    </div>


    {{-- ============================================================
         PAGINATION
    ============================================================ --}}
    @if ($usages->hasPages())
        <div class="mt-6">
            {{ $usages->links() }}
        </div>
    @endif

</div>
@endsection