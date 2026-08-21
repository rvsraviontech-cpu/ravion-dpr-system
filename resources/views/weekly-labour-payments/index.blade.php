@extends('layouts.app')

@section('content')

<div class="mx-auto max-w-full">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">Weekly Labour Payment Register</h1>
            <p class="mt-1 text-sm text-gray-500">Consolidated labour payment across all projects for each week.</p>
        </div>

        @if(auth()->user()->hasPermission('weekly_labour_payments.create'))
            <a href="{{ route('weekly-labour-payments.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-[#0F2A52] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#173C70]">
                Create Register
            </a>
        @endif
    </div>

    <x-rds.alert />

    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('weekly-labour-payments.index') }}">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Register number"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Week Start</label>
                    <input type="date" name="week_start_date" value="{{ request('week_start_date') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                    <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                    <a href="{{ route('weekly-labour-payments.index') }}"
                       class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-sm">
                <thead class="bg-[#0F2A52] text-white">
                    <tr>
                        <th class="px-3 py-3 text-left">#</th>
                        <th class="px-3 py-3 text-left">Register</th>
                        <th class="px-3 py-3 text-left">Week</th>
                        <th class="px-3 py-3 text-right">Labours</th>
                        <th class="px-3 py-3 text-right">Payable Days</th>
                        <th class="px-3 py-3 text-right">Net Payable</th>
                        <th class="px-3 py-3 text-center">Status</th>
                        <th class="px-3 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($registers as $register)
                        @php
                            $statusClass = match($register->status) {
                                'paid' => 'bg-emerald-100 text-emerald-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'submitted' => 'bg-blue-100 text-blue-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'calculated' => 'bg-violet-100 text-violet-800',
                                default => 'bg-amber-100 text-amber-800',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-3 text-gray-500">{{ $registers->firstItem() + $loop->index }}</td>
                            <td class="px-3 py-3 font-semibold text-gray-900">{{ $register->register_number }}</td>
                            <td class="whitespace-nowrap px-3 py-3">
                                {{ $register->week_start_date?->format('d M Y') }} – {{ $register->week_end_date?->format('d M Y') }}
                            </td>
                            <td class="px-3 py-3 text-right">{{ $register->total_labours }}</td>
                            <td class="px-3 py-3 text-right">{{ rtrim(rtrim(number_format((float)$register->total_payable_days, 2, '.', ''), '0'), '.') }}</td>
                            <td class="px-3 py-3 text-right font-bold text-[#0F2A52]">₹{{ number_format((float)$register->net_payable, 2) }}</td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ ucfirst($register->status) }}</span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <a href="{{ route('weekly-labour-payments.show', $register) }}"
                                   class="inline-flex rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500">No Weekly Labour Payment Registers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registers->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $registers->links() }}</div>
        @endif
    </div>
</div>

@endsection
