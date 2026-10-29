@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('rfqs.index') }}" class="text-sm font-medium text-blue-700 hover:underline">← RFQ Register</a>
            <h1 class="mt-2 text-2xl font-semibold text-gray-900">{{ $pageTitle }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $pageDescription }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">RFQ Number</th>
                        <th class="px-4 py-3">Project</th>
                        <th class="px-4 py-3">RFQ Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rfqs as $rfq)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $rfq->rfq_number }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $rfq->project_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $rfq->rfq_date ? \Illuminate\Support\Carbon::parse($rfq->rfq_date)->format('d/m/Y') : '-' }}</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ $rfq->status }}</span></td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('rfqs.comparison', ['rfq' => $rfq->id]) }}" class="inline-flex rounded-md bg-blue-700 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-800">
                                    {{ $isAwards ? 'Review & Award' : 'Compare Quotations' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">No RFQs with submitted vendor quotations are available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rfqs->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $rfqs->links() }}</div>
        @endif
    </div>
</div>
@endsection
