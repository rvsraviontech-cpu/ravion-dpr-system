@extends('layouts.app')

@section('content')

@php
    $statusVariants = [
        'draft' => 'secondary',
        'calculated' => 'info',
        'submitted' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'paid' => 'success',
    ];
@endphp

<x-rds.page-header
    title="Weekly Wage Sheets"
    subtitle="Generate, review, approve, and track weekly labour wage calculations."
>
    <x-slot:actions>
        @if(auth()->user()->hasPermission('weekly_wage_sheets.create'))
            <x-rds.button
                href="{{ route('weekly-wage-sheets.create') }}"
                variant="primary"
            >
                Create Weekly Wage Sheet
            </x-rds.button>
        @endif
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<div class="space-y-6">

    <x-rds.card>

        <form
            method="GET"
            action="{{ route('weekly-wage-sheets.index') }}"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                <x-rds.input
                    name="search"
                    label="Search"
                    value="{{ request('search') }}"
                    placeholder="Wage sheet no., project name, or code"
                />

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
                                (string) request('project_id')
                                === (string) $project->id
                            )
                        >
                            {{ $project->project_name }}
                        </option>
                    @endforeach
                </x-rds.select>

                <x-rds.input
                    name="week_start_date"
                    label="Week Starting Sunday"
                    type="date"
                    value="{{ request('week_start_date') }}"
                />

                <x-rds.select
                    name="status"
                    label="Status"
                >
                    <option value="">
                        All Statuses
                    </option>

                    @foreach($statuses as $statusValue => $statusLabel)
                        <option
                            value="{{ $statusValue }}"
                            @selected(
                                request('status')
                                === $statusValue
                            )
                        >
                            {{ $statusLabel }}
                        </option>
                    @endforeach
                </x-rds.select>

            </div>

            <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('weekly-wage-sheets.index') }}"
                    variant="secondary"
                >
                    Reset
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Apply Filters
                </x-rds.button>

            </div>
        </form>

    </x-rds.card>

    <x-rds.card :padding="false">

        <div class="w-full overflow-x-auto">

            <table class="w-full min-w-[850px] table-fixed border-collapse">

                <colgroup>
                    <col style="width: 5%;">
                    <col style="width: 28%;">
                    <col style="width: 20%;">
                    <col style="width: 18%;">
                    <col style="width: 13%;">
                    <col style="width: 16%;">
                </colgroup>

                <thead class="bg-gray-50">
                    <tr>
                        <th class="border-b border-gray-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            #
                        </th>

                        <th class="border-b border-gray-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Project
                        </th>

                        <th class="border-b border-gray-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Week
                        </th>

                        <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Total Payable
                        </th>

                        <th class="border-b border-gray-200 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Status
                        </th>

                        <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white">

                    @forelse($weeklyWageSheets as $weeklyWageSheet)

                        @php
                            $status = strtolower(
                                (string) $weeklyWageSheet->status
                            );

                            $statusLabel =
                                $statuses[$status]
                                ?? ucfirst($status ?: 'Unknown');

                            $statusVariant =
                                $statusVariants[$status]
                                ?? 'secondary';

                            $canCalculate =
                                auth()->user()->hasPermission(
                                    'weekly_wage_sheets.calculate'
                                )
                                && in_array(
                                    $status,
                                    [
                                        'draft',
                                        'calculated',
                                        'rejected',
                                    ],
                                    true
                                );

                            $canSubmit =
                                auth()->user()->hasPermission(
                                    'weekly_wage_sheets.submit'
                                )
                                && in_array(
                                    $status,
                                    [
                                        'calculated',
                                        'rejected',
                                    ],
                                    true
                                );
                        @endphp

                        <tr class="align-middle transition hover:bg-gray-50">

                            <td class="border-b border-gray-100 px-4 py-4 text-sm text-gray-500">
                                {{ $weeklyWageSheets->firstItem() + $loop->index }}
                            </td>

                            <td class="border-b border-gray-100 px-4 py-4">
                                <div class="truncate text-sm font-semibold text-gray-900">
                                    {{ $weeklyWageSheet->project?->project_name ?? '—' }}
                                </div>

                                @if($weeklyWageSheet->project?->project_code)
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $weeklyWageSheet->project->project_code }}
                                    </div>
                                @endif
                            </td>

                            <td class="border-b border-gray-100 px-4 py-4">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $weeklyWageSheet->week_start_date?->format('d M Y') ?? '—' }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    to
                                    {{ $weeklyWageSheet->week_end_date?->format('d M Y') ?? '—' }}
                                </div>
                            </td>

                            <td class="border-b border-gray-100 px-4 py-4 text-right">
                                <div class="whitespace-nowrap text-base font-bold text-blue-800">
                                    ₹{{ number_format(
                                        (float) $weeklyWageSheet->total_project_payable,
                                        2
                                    ) }}
                                </div>
                            </td>

                            <td class="border-b border-gray-100 px-4 py-4 text-center">
                                <x-rds.badge :variant="$statusVariant">
                                    {{ $statusLabel }}
                                </x-rds.badge>

                                @if(
                                    $status === 'paid'
                                    && $weeklyWageSheet->payment_date
                                )
                                    <div class="mt-1 text-[11px] text-gray-500">
                                        {{ $weeklyWageSheet->payment_date->format('d M Y') }}
                                    </div>
                                @endif
                            </td>

                            <td class="border-b border-gray-100 px-4 py-4">
                                <div class="flex flex-wrap items-center justify-end gap-2">

                                    @if(
                                        auth()->user()->hasPermission(
                                            'weekly_wage_sheets.view'
                                        )
                                    )
                                        <x-rds.button
                                            href="{{ route(
                                                'weekly-wage-sheets.show',
                                                $weeklyWageSheet
                                            ) }}"
                                            variant="secondary"
                                            size="sm"
                                            class="!px-3 !py-1.5 !text-xs"
                                        >
                                            View
                                        </x-rds.button>
                                    @endif

                                    @if($canCalculate)
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'weekly-wage-sheets.generate',
                                                $weeklyWageSheet
                                            ) }}"
                                            onsubmit="return confirm('{{ $status === 'draft'
                                                ? 'Calculate this wage sheet from approved attendance?'
                                                : 'Recalculate this wage sheet from approved attendance? Existing additions and deductions will be preserved.' }}');"
                                        >
                                            @csrf

                                            <x-rds.button
                                                type="submit"
                                                variant="primary"
                                                size="sm"
                                                class="!px-3 !py-1.5 !text-xs"
                                            >
                                                {{ $status === 'draft'
                                                    ? 'Calculate'
                                                    : 'Recalculate' }}
                                            </x-rds.button>
                                        </form>
                                    @endif

                                    @if($canSubmit)
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'weekly-wage-sheets.submit',
                                                $weeklyWageSheet
                                            ) }}"
                                            onsubmit="return confirm('Submit this weekly wage sheet for approval?');"
                                        >
                                            @csrf

                                            <x-rds.button
                                                type="submit"
                                                variant="success"
                                                size="sm"
                                                class="!px-3 !py-1.5 !text-xs"
                                            >
                                                Submit
                                            </x-rds.button>
                                        </form>
                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center">
                                <div class="text-sm font-semibold text-gray-700">
                                    No weekly wage sheets found.
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    Adjust the filters or create the first weekly wage sheet.
                                </div>

                                @if(
                                    auth()->user()->hasPermission(
                                        'weekly_wage_sheets.create'
                                    )
                                )
                                    <div class="mt-4">
                                        <x-rds.button
                                            href="{{ route(
                                                'weekly-wage-sheets.create'
                                            ) }}"
                                            variant="primary"
                                            size="sm"
                                        >
                                            Create Weekly Wage Sheet
                                        </x-rds.button>
                                    </div>
                                @endif
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        @if($weeklyWageSheets->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $weeklyWageSheets->withQueryString()->links() }}
            </div>
        @endif

    </x-rds.card>

</div>

@endsection
