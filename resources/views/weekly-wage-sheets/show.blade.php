@extends('layouts.app')

@section('content')

@php
    $status = strtolower(
        (string) $weeklyWageSheet->status
    );

    $statusLabel = match ($status) {
        'draft' => 'Draft',
        'calculated' => 'Calculated',
        'submitted' => 'Submitted',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'paid' => 'Paid',
        default => ucfirst($status ?: 'Unknown'),
    };

    $statusVariant = match ($status) {
        'draft' => 'secondary',
        'calculated' => 'info',
        'submitted' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'paid' => 'success',
        default => 'secondary',
    };

    $canEditFinancials = in_array(
        $status,
        ['draft', 'calculated', 'rejected'],
        true
    );

    $canCalculate =
        auth()->user()->hasPermission(
            'weekly_wage_sheets.calculate'
        )
        && $canEditFinancials;

    $canManageAdjustments =
        auth()->user()->hasPermission(
            'weekly_wage_sheets.manage_adjustments'
        )
        && $canEditFinancials
        && $weeklyWageSheet->details->isNotEmpty();

    $canManageCharges =
        auth()->user()->hasPermission(
            'weekly_wage_sheets.manage_charges'
        )
        && $canEditFinancials;

    $canSubmit =
        auth()->user()->hasPermission(
            'weekly_wage_sheets.submit'
        )
        && in_array(
            $status,
            ['calculated', 'rejected'],
            true
        );

    $canApprove =
        auth()->user()->hasPermission(
            'weekly_wage_sheets.approve'
        )
        && $status === 'submitted';

    $canReject =
        auth()->user()->hasPermission(
            'weekly_wage_sheets.reject'
        )
        && $status === 'submitted';

    $canMarkPaid =
        auth()->user()->hasPermission(
            'weekly_wage_sheets.mark_paid'
        )
        && $status === 'approved';

    $wageDetailsByGroup = $weeklyWageSheet->details
        ->sortBy(fn ($detail) => sprintf(
            '%08d|%s|%s',
            (int) ($detail->labour?->labourGroup?->sort_order ?? 999999),
            strtolower((string) ($detail->labour?->labourGroup?->name ?? 'Un-grouped Labour')),
            strtolower((string) ($detail->labour?->full_name ?? ''))
        ))
        ->groupBy(fn ($detail) => $detail->labour?->labourGroup?->name ?? 'Un-grouped Labour');

    $detailInputIndexes = $weeklyWageSheet->details
        ->values()
        ->mapWithKeys(fn ($detail, $index) => [$detail->id => $index]);
@endphp

<x-rds.page-header
    title="Weekly Wage Sheet"
    subtitle="Review labour attendance-based wage calculations, adjustments, site charges, approval, and payment."
>
    <x-slot:actions>

    <div class="flex flex-wrap gap-2">

        @if(auth()->user()->hasPermission('weekly_wage_sheets.export'))

    <x-rds.button
        href="{{ route(
            'weekly-wage-sheets.export-excel',
            $weeklyWageSheet
        ) }}"
        variant="success"
    >
        Export Excel
    </x-rds.button>

    <x-rds.button
        href="{{ route(
            'weekly-wage-sheets.export-pdf',
            $weeklyWageSheet
        ) }}"
        variant="danger"
    >
        Export PDF
    </x-rds.button>

@endif


        @if($canCalculate)

            <form
                method="POST"
                action="{{ route(
                    'weekly-wage-sheets.generate',
                    $weeklyWageSheet
                ) }}"
                onsubmit="return confirm('Generate or recalculate this wage sheet from approved attendance?');"
            >

                @csrf

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    {{ $status === 'draft'
                        ? 'Calculate Wages'
                        : 'Recalculate Wages' }}
                </x-rds.button>

            </form>

        @endif


        <x-rds.button
            href="{{ route('weekly-wage-sheets.index') }}"
            variant="secondary"
        >
            Back
        </x-rds.button>

    </div>

</x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<div class="space-y-6">

    @if($status === 'rejected' && $weeklyWageSheet->rejection_reason)
        <x-rds.alert type="danger">
            <strong>Rejection Reason:</strong>
            {{ $weeklyWageSheet->rejection_reason }}
        </x-rds.alert>
    @endif

    <x-rds.card>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Wage Sheet Number
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $weeklyWageSheet->wage_sheet_number }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Project
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $weeklyWageSheet->project?->project_name ?? '—' }}
                </p>

                @if($weeklyWageSheet->project?->project_code)
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $weeklyWageSheet->project->project_code }}
                    </p>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Wage Period
                </p>

                <p class="mt-1 text-sm font-medium text-gray-900">
                    {{ $weeklyWageSheet->week_start_date?->format('d M Y') ?? '—' }}
                    -
                    {{ $weeklyWageSheet->week_end_date?->format('d M Y') ?? '—' }}
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    Sunday to Saturday
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Status
                </p>

                <div class="mt-2">
                    <x-rds.badge :variant="$statusVariant">
                        {{ $statusLabel }}
                    </x-rds.badge>
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Generated By
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $weeklyWageSheet->generatedBy?->name ?? 'Not generated yet' }}
                </p>

                @if($weeklyWageSheet->generated_at)
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $weeklyWageSheet->generated_at->format('d M Y h:i A') }}
                    </p>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Submitted By
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $weeklyWageSheet->submittedBy?->name ?? '—' }}
                </p>

                @if($weeklyWageSheet->submitted_at)
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $weeklyWageSheet->submitted_at->format('d M Y h:i A') }}
                    </p>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Approved By
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $weeklyWageSheet->approvedBy?->name ?? '—' }}
                </p>

                @if($weeklyWageSheet->approved_at)
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $weeklyWageSheet->approved_at->format('d M Y h:i A') }}
                    </p>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Remarks
                </p>

                <p class="mt-1 whitespace-pre-line text-sm text-gray-700">
                    {{ $weeklyWageSheet->remarks ?: 'No remarks.' }}
                </p>
            </div>

        </div>

    </x-rds.card>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-6">

        @foreach([
            [
                'label' => 'Total Labour',
                'value' => $weeklyWageSheet->total_labours,
                'class' => 'text-gray-900',
            ],
            [
                'label' => 'Payable Days',
                'value' => number_format(
                    (float) $weeklyWageSheet->total_payable_days,
                    2
                ),
                'class' => 'text-blue-700',
            ],
            [
                'label' => 'Normal Wages',
                'value' => '₹' . number_format(
                    (float) $weeklyWageSheet->total_normal_wages,
                    2
                ),
                'class' => 'text-gray-900',
            ],
            [
                'label' => 'OT Amount',
                'value' => '₹' . number_format(
                    (float) $weeklyWageSheet->total_ot_wages,
                    2
                ),
                'class' => 'text-violet-700',
            ],
            [
                'label' => 'Net Labour Wages',
                'value' => '₹' . number_format(
                    (float) $weeklyWageSheet->net_labour_wages,
                    2
                ),
                'class' => 'text-green-700',
            ],
            [
                'label' => 'Project Payable',
                'value' => '₹' . number_format(
                    (float) $weeklyWageSheet->total_project_payable,
                    2
                ),
                'class' => 'text-blue-800',
            ],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    {{ $card['label'] }}
                </div>

                <div class="mt-2 text-xl font-bold {{ $card['class'] }}">
                    {{ $card['value'] }}
                </div>
            </div>
        @endforeach

    </div>

    <x-rds.card :padding="false">

        <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-900">
                    Labour Wage Calculations
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Payable days are calculated from approved attendance. OT Hours and OT Amount are taken from approved attendance; OT Rate is shown as the reference hourly rate.
                </p>
            </div>

            @if($canCalculate)
                <form
                    method="POST"
                    action="{{ route(
                        'weekly-wage-sheets.generate',
                        $weeklyWageSheet
                    ) }}"
                    onsubmit="return confirm('Recalculate all labour wages from approved attendance? Existing additions and deductions will be preserved.');"
                >
                    @csrf

                    <x-rds.button
                        type="submit"
                        variant="secondary"
                        size="sm"
                    >
                        Recalculate
                    </x-rds.button>
                </form>
            @endif
        </div>

        @if($weeklyWageSheet->details->isEmpty())

            <div class="px-4 py-14 text-center">
                <div class="text-sm font-semibold text-gray-700">
                    Wages have not been calculated yet.
                </div>

                <div class="mt-1 text-xs text-gray-500">
                    Use Calculate Wages to generate labour wage rows from approved attendance.
                </div>
            </div>

        @else

            <form
                method="POST"
                action="{{ route(
                    'weekly-wage-sheets.adjustments.update',
                    $weeklyWageSheet
                ) }}"
            >
                @csrf
                @method('PUT')

                <div class="w-full overflow-x-auto">

                    <table class="w-full min-w-[1850px] table-fixed divide-y divide-gray-200">

                        <colgroup>
                            <col class="w-[55px]">
                            <col class="w-[230px]">
                            <col class="w-[180px]">
                            <col class="w-[115px]">
                            <col class="w-[90px]">
                            <col class="w-[90px]">
                            <col class="w-[105px]">
                            <col class="w-[115px]">
                            <col class="w-[120px]">
                            <col class="w-[120px]">
                            <col class="w-[120px]">
                            <col class="w-[125px]">
                            <col class="w-[125px]">
                            <col class="w-[125px]">
                            <col class="w-[220px]">
                            <col class="w-[200px]">
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
                                    Designation
                                </th>

                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Daily Rate
                                </th>

                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Full Days
                                </th>

                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Half Days
                                </th>

                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Payable Days
                                </th>

                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Normal Wage
                                </th>

                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    OT Hours
                                </th>

                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    OT Rate
                                </th>

                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    OT Wage
                                </th>

                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Additions
                                </th>

                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Deductions
                                </th>

                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Net Payable
                                </th>

                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Adjustment Reason
                                </th>

                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Remarks
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">

                            @php $serial = 1; @endphp

                            @foreach($wageDetailsByGroup as $groupName => $groupDetails)
                                <tr class="bg-blue-50">
                                    <td colspan="16" class="px-3 py-2 text-sm font-bold text-blue-900">
                                        Labour Group: {{ $groupName }}
                                        <span class="ml-2 text-xs font-medium text-blue-700">({{ $groupDetails->count() }} Labour)</span>
                                    </td>
                                </tr>

                                @foreach($groupDetails as $detail)
                                    @php $detailIndex = $detailInputIndexes[$detail->id]; @endphp

                                <tr class="align-middle hover:bg-gray-50">

                                    <td class="px-3 py-3 text-sm text-gray-500">
                                        {{ $serial++ }}

                                        <input
                                            type="hidden"
                                            name="details[{{ $detailIndex }}][id]"
                                            value="{{ $detail->id }}"
                                        >
                                    </td>

                                    <td class="px-3 py-3">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $detail->labour?->full_name ?? '—' }}
                                        </div>

                                        @if($detail->labour?->labour_code)
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $detail->labour->labour_code }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-3 py-3 text-sm text-gray-700">
                                        {{ $detail->designationRole?->name ?? '—' }}
                                    </td>

                                    <td class="px-3 py-3 text-right text-sm font-semibold text-gray-800">
                                        ₹{{ number_format(
                                            (float) $detail->daily_wage_rate,
                                            2
                                        ) }}
                                    </td>

                                    <td class="px-3 py-3 text-right text-sm text-gray-700">
                                        {{ number_format(
                                            (float) $detail->full_days,
                                            2
                                        ) }}
                                    </td>

                                    <td class="px-3 py-3 text-right text-sm text-gray-700">
                                        {{ number_format(
                                            (float) $detail->half_days,
                                            2
                                        ) }}
                                    </td>

                                    <td class="px-3 py-3 text-right text-sm font-semibold text-blue-700">
                                        {{ number_format(
                                            (float) $detail->payable_days,
                                            2
                                        ) }}
                                    </td>

                                    <td class="px-3 py-3 text-right text-sm font-semibold text-gray-800">
                                        ₹{{ number_format(
                                            (float) $detail->normal_wage,
                                            2
                                        ) }}
                                    </td>

                                    <td class="px-3 py-3 text-right text-sm font-semibold text-violet-700">
                                        {{ number_format(
                                            (float) $detail->ot_hours,
                                            2
                                        ) }}
                                    </td>

                                    <td class="px-3 py-3 text-right text-sm text-gray-700">
                                        ₹{{ number_format(
                                            (float) $detail->ot_hourly_rate,
                                            2
                                        ) }}
                                    </td>

                                    <td class="px-3 py-3 text-right text-sm font-semibold text-violet-700">
                                        ₹{{ number_format(
                                            (float) $detail->ot_wage,
                                            2
                                        ) }}
                                    </td>

                                    <td class="px-3 py-3">
                                        <input
                                            type="number"
                                            name="details[{{ $detailIndex }}][additions]"
                                            value="{{ old(
                                                "details.{$detailIndex}.additions",
                                                number_format(
                                                    (float) $detail->additions,
                                                    2,
                                                    '.',
                                                    ''
                                                )
                                            ) }}"
                                            min="0"
                                            step="0.01"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-right text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            @disabled(! $canManageAdjustments)
                                        >
                                    </td>

                                    <td class="px-3 py-3">
                                        <input
                                            type="number"
                                            name="details[{{ $detailIndex }}][deductions]"
                                            value="{{ old(
                                                "details.{$detailIndex}.deductions",
                                                number_format(
                                                    (float) $detail->deductions,
                                                    2,
                                                    '.',
                                                    ''
                                                )
                                            ) }}"
                                            min="0"
                                            step="0.01"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-right text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            @disabled(! $canManageAdjustments)
                                        >
                                    </td>

                                    <td class="px-3 py-3 text-right text-sm font-bold text-green-700">
                                        ₹{{ number_format(
                                            (float) $detail->net_payable,
                                            2
                                        ) }}
                                    </td>

                                    <td class="px-3 py-3">
                                        <textarea
                                            name="details[{{ $detailIndex }}][adjustment_reason]"
                                            rows="2"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder="Required when additions or deductions are entered."
                                            @disabled(! $canManageAdjustments)
                                        >{{ old(
                                            "details.{$detailIndex}.adjustment_reason",
                                            $detail->adjustment_reason
                                        ) }}</textarea>
                                    </td>

                                    <td class="px-3 py-3">
                                        <textarea
                                            name="details[{{ $detailIndex }}][remarks]"
                                            rows="2"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder="Optional remarks"
                                            @disabled(! $canManageAdjustments)
                                        >{{ old(
                                            "details.{$detailIndex}.remarks",
                                            $detail->remarks
                                        ) }}</textarea>
                                    </td>

                                </tr>


                                @endforeach
                                <tr class="border-t border-blue-200 bg-blue-50/60">
                                    <td colspan="13" class="px-3 py-3 text-right text-sm font-bold text-blue-900">
                                        {{ $groupName }} - Group Wage Total
                                    </td>
                                    <td class="px-3 py-3 text-right text-sm font-bold text-green-800">
                                        ₹{{ number_format((float) $groupDetails->sum('net_payable'), 2) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            @endforeach

                        </tbody>

                        <tfoot class="border-t-2 border-gray-300 bg-gray-50">
                            <tr>
                                <td
                                    colspan="4"
                                    class="px-3 py-3 text-right text-sm font-bold text-gray-800"
                                >
                                    Wage Sheet Totals
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-bold text-gray-800">
                                    {{ number_format(
                                        (float) $weeklyWageSheet->total_full_days,
                                        2
                                    ) }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-bold text-gray-800">
                                    {{ number_format(
                                        (float) $weeklyWageSheet->total_half_days,
                                        2
                                    ) }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-bold text-blue-700">
                                    {{ number_format(
                                        (float) $weeklyWageSheet->total_payable_days,
                                        2
                                    ) }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-bold text-gray-900">
                                    ₹{{ number_format(
                                        (float) $weeklyWageSheet->total_normal_wages,
                                        2
                                    ) }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-bold text-violet-700">
                                    {{ number_format(
                                        (float) $weeklyWageSheet->total_ot_hours,
                                        2
                                    ) }}
                                </td>

                                <td></td>

                                <td class="px-3 py-3 text-right text-sm font-bold text-violet-700">
                                    ₹{{ number_format(
                                        (float) $weeklyWageSheet->total_ot_wages,
                                        2
                                    ) }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-bold text-green-700">
                                    ₹{{ number_format(
                                        (float) $weeklyWageSheet->total_labour_additions,
                                        2
                                    ) }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-bold text-red-700">
                                    ₹{{ number_format(
                                        (float) $weeklyWageSheet->total_labour_deductions,
                                        2
                                    ) }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-bold text-green-800">
                                    ₹{{ number_format(
                                        (float) $weeklyWageSheet->net_labour_wages,
                                        2
                                    ) }}
                                </td>

                                <td colspan="2"></td>
                            </tr>
                        </tfoot>

                    </table>
                </div>

                @if($canManageAdjustments)
                    <div class="border-t border-gray-200 px-4 py-4">
                        <div class="flex justify-end">
                            <x-rds.button
                                type="submit"
                                variant="primary"
                            >
                                Save Labour Adjustments
                            </x-rds.button>
                        </div>
                    </div>
                @endif

            </form>

        @endif

    </x-rds.card>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        <x-rds.card>

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">
                        Site Additional Charges
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Add auto, transport, food, tool, or other project-level charges.
                    </p>
                </div>

                <div class="text-right">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Total Site Charges
                    </div>

                    <div class="mt-1 text-lg font-bold text-blue-800">
                        ₹{{ number_format(
                            (float) $weeklyWageSheet->total_site_charges,
                            2
                        ) }}
                    </div>
                </div>
            </div>

            @if($weeklyWageSheet->charges->isNotEmpty())
                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[700px] divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600">
                                    Charge
                                </th>

                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600">
                                    Context
                                </th>

                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-gray-600">
                                    Amount
                                </th>

                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-gray-600">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @foreach($weeklyWageSheet->charges as $charge)
                                <tr>
                                    <td class="px-3 py-3">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $charge->charge_type }}
                                        </div>

                                        @if($charge->description)
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $charge->description }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-3 py-3 text-sm text-gray-700">
                                        <div>
                                            {{ $charge->activity?->activity_name ?? 'No Activity' }}
                                        </div>

                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $charge->contractor?->contractor_name ?? 'No Contractor' }}
                                        </div>
                                    </td>

                                    <td class="px-3 py-3 text-right text-sm font-semibold text-gray-900">
                                        ₹{{ number_format(
                                            (float) $charge->amount,
                                            2
                                        ) }}
                                    </td>

                                    <td class="px-3 py-3 text-right">
                                        @if($canManageCharges)
                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'weekly-wage-sheets.charges.destroy',
                                                    [
                                                        $weeklyWageSheet,
                                                        $charge,
                                                    ]
                                                ) }}"
                                                onsubmit="return confirm('Remove this site charge?');"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="text-xs font-semibold text-red-600 hover:text-red-800"
                                                >
                                                    Remove
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">
                                                Locked
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="mt-5 rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500">
                    No site charges added.
                </div>
            @endif

            @if($canManageCharges)
                <form
                    method="POST"
                    action="{{ route(
                        'weekly-wage-sheets.charges.store',
                        $weeklyWageSheet
                    ) }}"
                    class="mt-6 border-t border-gray-200 pt-5"
                >
                    @csrf

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                        <x-rds.input
                            name="charge_type"
                            label="Charge Type"
                            value="{{ old('charge_type') }}"
                            placeholder="Example: Auto Charge"
                            required
                        />

                        <x-rds.input
                            name="amount"
                            label="Amount"
                            type="number"
                            value="{{ old('amount') }}"
                            min="0.01"
                            step="0.01"
                            required
                        />

                        <x-rds.select
                            name="activity_id"
                            label="Activity"
                        >
                            <option value="">
                                No Activity
                            </option>

                            @foreach($activities as $activity)
                                <option
                                    value="{{ $activity->id }}"
                                    @selected(
                                        (string) old('activity_id')
                                        === (string) $activity->id
                                    )
                                >
                                    {{ $activity->activity_name }}
                                </option>
                            @endforeach
                        </x-rds.select>

                        <x-rds.select
                            name="contractor_id"
                            label="Contractor"
                        >
                            <option value="">
                                No Contractor
                            </option>

                            @foreach($contractors as $contractor)
                                <option
                                    value="{{ $contractor->id }}"
                                    @selected(
                                        (string) old('contractor_id')
                                        === (string) $contractor->id
                                    )
                                >
                                    {{ $contractor->contractor_name }}
                                </option>
                            @endforeach
                        </x-rds.select>

                        <div class="md:col-span-2">
                            <x-rds.input
                                name="description"
                                label="Description"
                                value="{{ old('description') }}"
                                placeholder="Optional charge description"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <x-rds.textarea
                                name="remarks"
                                label="Remarks"
                                rows="2"
                                value="{{ old('remarks') }}"
                                placeholder="Optional remarks"
                            />
                        </div>

                    </div>

                    <div class="mt-4 flex justify-end">
                        <x-rds.button
                            type="submit"
                            variant="primary"
                        >
                            Add Site Charge
                        </x-rds.button>
                    </div>
                </form>
            @endif

        </x-rds.card>

        <x-rds.card>

            <h2 class="text-base font-semibold text-gray-900">
                Wage Sheet Summary
            </h2>

            <div class="mt-5 space-y-3">

                @foreach([
                    [
                        'label' => 'Normal Labour Wages',
                        'value' => $weeklyWageSheet->total_normal_wages,
                        'class' => 'text-gray-900',
                    ],
                    [
                        'label' => 'OT Amount',
                        'value' => $weeklyWageSheet->total_ot_wages,
                        'class' => 'text-violet-700',
                    ],
                    [
                        'label' => 'Labour Additions',
                        'value' => $weeklyWageSheet->total_labour_additions,
                        'class' => 'text-green-700',
                    ],
                    [
                        'label' => 'Labour Deductions',
                        'value' => -1 * (float) $weeklyWageSheet->total_labour_deductions,
                        'class' => 'text-red-700',
                    ],
                    [
                        'label' => 'Net Labour Wages',
                        'value' => $weeklyWageSheet->net_labour_wages,
                        'class' => 'text-gray-900',
                    ],
                    [
                        'label' => 'Site Additional Charges',
                        'value' => $weeklyWageSheet->total_site_charges,
                        'class' => 'text-blue-700',
                    ],
                ] as $summaryRow)
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-3">
                        <span class="text-sm text-gray-600">
                            {{ $summaryRow['label'] }}
                        </span>

                        <span class="text-sm font-semibold {{ $summaryRow['class'] }}">
                            ₹{{ number_format(
                                (float) $summaryRow['value'],
                                2
                            ) }}
                        </span>
                    </div>
                @endforeach

                <div class="flex items-center justify-between gap-4 rounded-xl bg-blue-50 px-4 py-4">
                    <span class="text-base font-bold text-blue-900">
                        Total Project Payable
                    </span>

                    <span class="text-xl font-bold text-blue-900">
                        ₹{{ number_format(
                            (float) $weeklyWageSheet->total_project_payable,
                            2
                        ) }}
                    </span>
                </div>

            </div>

        </x-rds.card>

    </div>

    <x-rds.card>

        <div class="flex flex-col gap-5">

            <div>
                <h2 class="text-base font-semibold text-gray-900">
                    Workflow Actions
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Submit the calculated sheet, approve or reject it, and record payment after approval.
                </p>
            </div>

            <div class="flex flex-wrap items-start gap-3">

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
                            variant="primary"
                        >
                            Submit for Approval
                        </x-rds.button>
                    </form>
                @endif

                @if($canApprove)
                    <form
                        method="POST"
                        action="{{ route(
                            'weekly-wage-sheets.approve',
                            $weeklyWageSheet
                        ) }}"
                        onsubmit="return confirm('Approve this weekly wage sheet?');"
                    >
                        @csrf

                        <x-rds.button
                            type="submit"
                            variant="success"
                        >
                            Approve Wage Sheet
                        </x-rds.button>
                    </form>
                @endif

                @if($canReject)
                    <form
                        method="POST"
                        action="{{ route(
                            'weekly-wage-sheets.reject',
                            $weeklyWageSheet
                        ) }}"
                        class="min-w-[320px] flex-1"
                    >
                        @csrf

                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input
                                type="text"
                                name="rejection_reason"
                                value="{{ old('rejection_reason') }}"
                                placeholder="Enter rejection reason"
                                class="min-w-0 flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                required
                            >

                            <x-rds.button
                                type="submit"
                                variant="danger"
                            >
                                Reject
                            </x-rds.button>
                        </div>
                    </form>
                @endif

            </div>

            @if($canMarkPaid)
                <form
                    method="POST"
                    action="{{ route(
                        'weekly-wage-sheets.mark-paid',
                        $weeklyWageSheet
                    ) }}"
                    class="rounded-xl border border-green-200 bg-green-50 p-4"
                >
                    @csrf

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                        <x-rds.input
                            name="payment_date"
                            label="Payment Date"
                            type="date"
                            value="{{ old(
                                'payment_date',
                                now()->toDateString()
                            ) }}"
                            required
                        />

                        <x-rds.select
                            name="payment_method"
                            label="Payment Method"
                            required
                        >
                            <option value="">
                                Select Method
                            </option>

                            @foreach([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'upi' => 'UPI',
                                'cheque' => 'Cheque',
                                'other' => 'Other',
                            ] as $methodValue => $methodLabel)
                                <option
                                    value="{{ $methodValue }}"
                                    @selected(
                                        old('payment_method')
                                        === $methodValue
                                    )
                                >
                                    {{ $methodLabel }}
                                </option>
                            @endforeach
                        </x-rds.select>

                        <x-rds.input
                            name="payment_reference"
                            label="Payment Reference"
                            value="{{ old('payment_reference') }}"
                            placeholder="Transaction or voucher reference"
                        />

                    </div>

                    <div class="mt-4 flex justify-end">
                        <x-rds.button
                            type="submit"
                            variant="success"
                        >
                            Mark as Paid
                        </x-rds.button>
                    </div>
                </form>
            @endif

            @if($status === 'paid')
                <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                    <div class="text-sm font-semibold text-green-900">
                        Payment Recorded
                    </div>

                    <div class="mt-2 grid grid-cols-1 gap-4 text-sm text-green-800 md:grid-cols-4">
                        <div>
                            <span class="block text-xs font-semibold uppercase">
                                Paid By
                            </span>

                            <span class="mt-1 block">
                                {{ $weeklyWageSheet->paidBy?->name ?? '—' }}
                            </span>
                        </div>

                        <div>
                            <span class="block text-xs font-semibold uppercase">
                                Payment Date
                            </span>

                            <span class="mt-1 block">
                                {{ $weeklyWageSheet->payment_date?->format('d M Y') ?? '—' }}
                            </span>
                        </div>

                        <div>
                            <span class="block text-xs font-semibold uppercase">
                                Method
                            </span>

                            <span class="mt-1 block">
                                {{ Str::headline(
                                    $weeklyWageSheet->payment_method
                                    ?? '—'
                                ) }}
                            </span>
                        </div>

                        <div>
                            <span class="block text-xs font-semibold uppercase">
                                Reference
                            </span>

                            <span class="mt-1 block">
                                {{ $weeklyWageSheet->payment_reference ?? '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </x-rds.card>

</div>

@endsection
