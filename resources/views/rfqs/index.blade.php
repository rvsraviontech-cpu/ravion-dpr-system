@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Requests for Quotation</h1>
            <p class="mt-1 text-sm text-gray-500">Manage vendor quotation requests for approved material requirements.</p>
        </div>
        <a href="{{ route('rfqs.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#10212F] px-4 py-2 text-sm font-medium text-white hover:opacity-90">+ Create RFQ</a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-900">RFQ Register</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">RFQ Number</th>
                        <th class="px-4 py-3">Project Name</th>
                        <th class="px-4 py-3">Deadline</th>
                        <th class="px-4 py-3 text-center">Materials</th>
                        <th class="px-4 py-3 text-center">Vendors</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rfqs as $rfq)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-gray-900">{{ $rfq->rfq_number }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $rfq->project?->project_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $rfq->submission_deadline ? \Illuminate\Support\Carbon::parse($rfq->submission_deadline)->format('d/m/Y') : '—' }}</td>
                            <td class="px-4 py-3 text-center tabular-nums">{{ $rfq->items_count }}</td>
                            <td class="px-4 py-3 text-center tabular-nums">{{ $rfq->invitations_count }}</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-700">{{ $rfq->status }}</span></td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('rfqs.show', $rfq) }}" class="font-medium text-blue-700 hover:underline">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No RFQs yet. Create your first request for quotation.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($rfqs->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $rfqs->links() }}</div>
        @endif
    </section>
</div>
@endsection
