@extends('layouts.app')

@section('content')

@php
    $statusClass = match($register->status) {
        'paid' => 'bg-emerald-100 text-emerald-800',
        'approved' => 'bg-green-100 text-green-800',
        'submitted' => 'bg-blue-100 text-blue-800',
        'rejected' => 'bg-red-100 text-red-800',
        'calculated' => 'bg-violet-100 text-violet-800',
        default => 'bg-amber-100 text-amber-800',
    };

    $canAdjust = in_array($register->status, ['draft', 'calculated', 'rejected'], true)
        && auth()->user()->hasPermission('weekly_labour_payments.manage_adjustments');
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-5 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">Weekly Labour Payment Register</h1>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                    {{ ucfirst($register->status) }}
                </span>
            </div>

            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-600">
                <span><strong>Register:</strong> {{ $register->register_number }}</span>
                <span><strong>Week:</strong> {{ $register->week_start_date?->format('d M Y') }} – {{ $register->week_end_date?->format('d M Y') }}</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(in_array($register->status, ['draft', 'calculated', 'rejected'], true) && auth()->user()->hasPermission('weekly_labour_payments.calculate'))
                <form method="POST" action="{{ route('weekly-labour-payments.generate', $register) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-blue-600 bg-white px-4 py-2.5 text-sm font-semibold text-blue-700 shadow-sm transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:border-gray-300 disabled:bg-gray-100 disabled:text-gray-400">
                        {{ $register->status === 'draft' ? 'Calculate' : 'Recalculate' }}
                    </button>
                </form>
            @endif

            @if(in_array($register->status, ['calculated', 'rejected'], true) && auth()->user()->hasPermission('weekly_labour_payments.submit'))
                <form method="POST" action="{{ route('weekly-labour-payments.submit', $register) }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Submit</button>
                </form>
            @endif

            @if($register->status === 'submitted' && auth()->user()->hasPermission('weekly_labour_payments.approve'))
                <form method="POST" action="{{ route('weekly-labour-payments.approve', $register) }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white">Approve</button>
                </form>
            @endif

            @if($register->details->isNotEmpty() && auth()->user()->hasPermission('weekly_labour_payments.export'))
    <a
        href="{{ route('weekly-labour-payments.export-pdf', $register) }}"
        class="inline-flex items-center justify-center rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-900"
    >
        Download PDF
    </a>
@endif

            <a
    href="{{ route('weekly-labour-payments.index') }}"
    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
>
    Back
</a>
        </div>
    </div>

    <x-rds.alert />

    <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Labours</p>
            <p class="mt-1 text-xl font-bold text-gray-900">{{ $register->total_labours }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Payable Days</p>
            <p class="mt-1 text-xl font-bold text-gray-900">{{ rtrim(rtrim(number_format((float)$register->total_payable_days, 2, '.', ''), '0'), '.') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Normal Wages</p>
            <p class="mt-1 text-lg font-bold">₹{{ number_format((float)$register->total_normal_wages, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">OT Wages</p>
            <p class="mt-1 text-lg font-bold">₹{{ number_format((float)$register->total_ot_wages, 2) }}</p>
        </div>
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-green-700">Additions</p>
            <p class="mt-1 text-lg font-bold text-green-800">₹{{ number_format((float)$register->total_additions, 2) }}</p>
        </div>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-red-700">Deductions</p>
            <p class="mt-1 text-lg font-bold text-red-800">₹{{ number_format((float)$register->total_deductions, 2) }}</p>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-700">Net Payable</p>
            <p class="mt-1 text-xl font-bold text-[#0F2A52]">₹{{ number_format((float)$register->net_payable, 2) }}</p>
        </div>
    </div>

    @if($register->details->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white px-5 py-12 text-center shadow-sm">
            <p class="font-semibold text-gray-700">Register has not been calculated yet.</p>
            <p class="mt-1 text-sm text-gray-500">Click Calculate to consolidate approved attendance across all projects.</p>
        </div>
    @else
        <form method="POST" action="{{ route('weekly-labour-payments.adjustments.update', $register) }}">
            @csrf
            @method('PUT')

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 px-4 py-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="font-bold text-gray-800">Labour Payment Summary</h2>
                        <p class="text-xs text-gray-500">One payment row per labour. Expand Projects to see site-wise allocation.</p>
                    </div>

                    @if($canAdjust)
                        <button type="submit" class="rounded-lg bg-[#0F2A52] px-4 py-2 text-sm font-semibold text-white">
                            Save Adjustments
                        </button>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[1250px] w-full border-collapse text-xs">
                        <thead class="bg-[#0F2A52] text-white">
                            <tr>
                                <th class="px-3 py-3 text-left">#</th>
                                <th class="px-3 py-3 text-left">Labour</th>
                                <th class="px-3 py-3 text-left">Group / Designation</th>
                                <th class="px-3 py-3 text-right">Days</th>
                                <th class="px-3 py-3 text-right">Rate</th>
                                <th class="px-3 py-3 text-right">Normal</th>
                                <th class="px-3 py-3 text-right">OT Hrs</th>
                                <th class="px-3 py-3 text-right">OT Wage</th>
                                <th class="px-3 py-3 text-right">Additions</th>
                                <th class="px-3 py-3 text-right">Deductions</th>
                                <th class="px-3 py-3 text-right">Net Payable</th>
                                <th class="px-3 py-3 text-center">Projects</th>
                            </tr>
                        </thead>

                        @foreach($register->details->groupBy(fn($detail) => $detail->labourGroup?->name ?? $detail->labour?->labourGroup?->name ?? 'Un-grouped Labour') as $groupName => $groupDetails)
                            <tbody>
                                <tr class="bg-blue-50">
                                    <td colspan="12" class="border-y border-blue-200 px-3 py-2 font-bold uppercase tracking-wide text-[#0F2A52]">
                                        {{ $groupName }}
                                        <span class="ml-2 font-medium normal-case tracking-normal text-blue-700">
                                            {{ $groupDetails->count() }} labour{{ $groupDetails->count() === 1 ? '' : 's' }}
                                        </span>
                                    </td>
                                </tr>

                                @foreach($groupDetails as $detail)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="px-3 py-3 text-gray-500">{{ $loop->iteration }}</td>
                                        <td class="px-3 py-3">
                                            <div class="font-semibold text-gray-900">{{ $detail->labour?->full_name ?? '-' }}</div>
                                            <div class="mt-0.5 text-[10px] text-gray-500">{{ $detail->labour?->labour_code ?? '' }}</div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="font-medium text-gray-700">{{ $detail->labourGroup?->name ?? $detail->labour?->labourGroup?->name ?? '-' }}</div>
                                            <div class="mt-0.5 text-[10px] text-gray-500">{{ $detail->designationRole?->name ?? '-' }}</div>
                                        </td>
                                        <td class="px-3 py-3 text-right font-semibold">{{ rtrim(rtrim(number_format((float)$detail->payable_days, 2, '.', ''), '0'), '.') }}</td>
                                        <td class="px-3 py-3 text-right">₹{{ number_format((float)$detail->daily_wage_rate, 2) }}</td>
                                        <td class="px-3 py-3 text-right">₹{{ number_format((float)$detail->normal_wage, 2) }}</td>
                                        <td class="px-3 py-3 text-right">{{ rtrim(rtrim(number_format((float)$detail->ot_hours, 2, '.', ''), '0'), '.') }}</td>
                                        <td class="px-3 py-3 text-right">₹{{ number_format((float)$detail->ot_wage, 2) }}</td>
                                        <td class="px-3 py-3 text-right">
                                            <input type="hidden" name="details[{{ $detail->id }}][id]" value="{{ $detail->id }}">
                                            <input type="number" step="0.01" min="0"
                                                   name="details[{{ $detail->id }}][additions]"
                                                   value="{{ old('details.'.$detail->id.'.additions', $detail->additions) }}"
                                                   @disabled(!$canAdjust)
                                                   class="w-24 rounded-md border border-gray-300 px-2 py-1.5 text-right text-xs disabled:bg-gray-100">
                                        </td>
                                        <td class="px-3 py-3 text-right">
                                            <input type="number" step="0.01" min="0"
                                                   name="details[{{ $detail->id }}][deductions]"
                                                   value="{{ old('details.'.$detail->id.'.deductions', $detail->deductions) }}"
                                                   @disabled(!$canAdjust)
                                                   class="w-24 rounded-md border border-gray-300 px-2 py-1.5 text-right text-xs disabled:bg-gray-100">
                                            <input type="hidden" name="details[{{ $detail->id }}][adjustment_reason]" value="{{ $detail->adjustment_reason }}">
                                            <input type="hidden" name="details[{{ $detail->id }}][remarks]" value="{{ $detail->remarks }}">
                                        </td>
                                        <td class="px-3 py-3 text-right font-bold text-[#0F2A52]">₹{{ number_format((float)$detail->net_payable, 2) }}</td>
                                        <td class="px-3 py-3 text-center">
                                            <button type="button"
                                                    class="project-toggle rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-[11px] font-semibold text-blue-700"
                                                    data-target="allocation-{{ $detail->id }}">
                                                View ({{ $detail->allocations->count() }})
                                            </button>
                                        </td>
                                    </tr>

                                    <tr id="allocation-{{ $detail->id }}" class="hidden bg-slate-50">
                                        <td colspan="12" class="px-5 py-4">
                                            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                                                <table class="w-full text-xs">
                                                    <thead class="bg-slate-100 text-slate-700">
                                                        <tr>
                                                            <th class="px-3 py-2 text-left">Project</th>
                                                            <th class="px-3 py-2 text-left">Attendance Dates</th>
                                                            <th class="px-3 py-2 text-right">Days</th>
                                                            <th class="px-3 py-2 text-right">Normal Wage</th>
                                                            <th class="px-3 py-2 text-right">OT Hrs</th>
                                                            <th class="px-3 py-2 text-right">OT Wage</th>
                                                            <th class="px-3 py-2 text-right">Project Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100">
                                                        @foreach($detail->allocations as $allocation)
                                                            <tr>
                                                                <td class="px-3 py-2 font-semibold text-gray-800">{{ $allocation->project_name }}</td>
                                                                <td class="px-3 py-2 text-gray-600">
                                                                    {{ collect($allocation->attendance_dates ?? [])->map(fn($date) => \Carbon\Carbon::parse($date)->format('d M'))->implode(', ') ?: '-' }}
                                                                </td>
                                                                <td class="px-3 py-2 text-right">{{ rtrim(rtrim(number_format((float)$allocation->payable_days, 2, '.', ''), '0'), '.') }}</td>
                                                                <td class="px-3 py-2 text-right">₹{{ number_format((float)$allocation->normal_wage, 2) }}</td>
                                                                <td class="px-3 py-2 text-right">{{ rtrim(rtrim(number_format((float)$allocation->ot_hours, 2, '.', ''), '0'), '.') }}</td>
                                                                <td class="px-3 py-2 text-right">₹{{ number_format((float)$allocation->ot_wage, 2) }}</td>
                                                                <td class="px-3 py-2 text-right font-bold">₹{{ number_format((float)$allocation->total_wage, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @endforeach
                    </table>
                </div>
            </div>
        </form>
    @endif

    @if($register->status === 'submitted' && auth()->user()->hasPermission('weekly_labour_payments.reject'))
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4">
            <form method="POST" action="{{ route('weekly-labour-payments.reject', $register) }}">
                @csrf
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                    <div class="flex-1">
                        <label class="mb-1 block text-sm font-semibold text-red-800">Rejection Reason</label>
                        <input type="text" name="rejection_reason" required minlength="3"
                               class="w-full rounded-lg border border-red-300 px-3 py-2 text-sm"
                               placeholder="Reason for rejection">
                    </div>
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white">Reject</button>
                </div>
            </form>
        </div>
    @endif

    @if($register->status === 'approved' && auth()->user()->hasPermission('weekly_labour_payments.mark_paid'))
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <h3 class="font-bold text-emerald-900">Mark Register as Paid</h3>

            <form method="POST" action="{{ route('weekly-labour-payments.mark-paid', $register) }}"
                  class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-emerald-800">Payment Date</label>
                    <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required
                           class="w-full rounded-lg border border-emerald-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-emerald-800">Method</label>
                    <select name="payment_method" required class="w-full rounded-lg border border-emerald-300 px-3 py-2 text-sm">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="upi">UPI</option>
                        <option value="cheque">Cheque</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-emerald-800">Reference</label>
                    <input type="text" name="payment_reference"
                           class="w-full rounded-lg border border-emerald-300 px-3 py-2 text-sm"
                           placeholder="Optional">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white">Mark Paid</button>
                </div>
            </form>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.project-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            const target = document.getElementById(button.dataset.target);
            if (!target) return;

            const opening = target.classList.contains('hidden');
            target.classList.toggle('hidden');

            button.textContent = opening
                ? button.textContent.replace('View', 'Hide')
                : button.textContent.replace('Hide', 'View');
        });
    });
});
</script>
@endpush

@endsection
