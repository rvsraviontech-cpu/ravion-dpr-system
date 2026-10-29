@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('rfqs.index') }}" class="text-sm font-medium text-blue-700 hover:underline">← RFQ Register</a>
            <h1 class="mt-2 text-xl font-semibold text-gray-900">{{ $rfq->rfq_number }}</h1>
            <p class="mt-1 text-sm text-gray-500">Project: {{ $rfq->project?->project_name ?? '-' }} · Deadline: {{ $rfq->submission_deadline ? \Illuminate\Support\Carbon::parse($rfq->submission_deadline)->format('d/m/Y h:i A') : '-' }}</p>
        </div>
        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ $rfq->status }}</span>
    </div>

    @if (session('rfq_invitation_error'))
        <div role="alert" class="mb-5 flex items-start justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 shadow-sm">
            <div><strong class="block font-semibold">Invitation Link Unavailable</strong><span>{{ session('rfq_invitation_error') }}</span></div>
            <a href="{{ route('rfqs.index') }}" class="shrink-0 rounded-md border border-amber-300 bg-white px-3 py-1.5 font-semibold text-amber-900 hover:bg-amber-100">← RFQ Register</a>
        </div>
    @endif

    <section class="mb-5 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <h2 class="border-b border-gray-200 px-4 py-3 text-sm font-semibold text-gray-900">Requested Materials</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr><th class="px-4 py-3 text-left">Material</th><th class="px-4 py-3 text-left">Specification</th><th class="px-4 py-3 text-right">Requested Qty</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rfq->items as $item)
                        <tr><td class="px-4 py-3 font-medium text-gray-900">{{ $item->product_name }}</td><td class="px-4 py-3 text-gray-600">{{ $item->specification_text ?: '-' }}</td><td class="px-4 py-3 text-right tabular-nums">{{ number_format((float) $item->requested_quantity, 3) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No materials assigned.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <h2 class="border-b border-gray-200 px-4 py-3 text-sm font-semibold text-gray-900">Vendor Assignments</h2>
        <div class="divide-y divide-gray-100">
            @forelse ($rfq->invitations as $invitation)
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm">
                    <div class="min-w-0 flex-1">
                        <span class="font-semibold text-gray-900">{{ $invitation->vendor?->vendor_name ?? 'Vendor' }}</span>
                        <span class="ml-2 text-xs text-gray-500">{{ $invitation->items->count() }} assigned item(s)</span>
                        <span class="ml-2 inline-block rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700">{{ $invitation->status }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        @php
                            $invitationSubmitted = $invitation->status === 'Submitted';
                            $invitationRevoked = $invitation->status === 'Revoked' || (bool) $invitation->revoked_at;
                            $rfqUnavailable = in_array($rfq->status, ['Closed', 'Cancelled'], true);
                            $deadlinePassed = !$rfq->submission_deadline || now()->greaterThanOrEqualTo($rfq->submission_deadline);
                            $noAssignedItems = $invitation->items->isEmpty();
                            $cannotIssue = $invitationSubmitted || $invitationRevoked || $rfqUnavailable || $deadlinePassed || $noAssignedItems;
                            $issueLabel = $invitationSubmitted ? 'Quotation Submitted' : ($invitationRevoked ? 'Invitation Revoked' : ($rfqUnavailable ? 'RFQ Closed' : ($deadlinePassed ? 'Deadline Passed' : ($noAssignedItems ? 'No Materials' : 'Generate link'))));
                        @endphp
                        @if ($cannotIssue)
                            <span aria-disabled="true" title="{{ $issueLabel }}" class="inline-flex cursor-not-allowed items-center rounded-md border border-gray-200 bg-gray-100 px-2.5 py-1.5 font-medium text-gray-500">{{ $issueLabel }}</span>
                        @else
                            <form method="POST" action="{{ route('rfq-invitations.issue-link', $invitation->id) }}" class="inline-flex">
                                @csrf
                                <button type="submit" title="Generate a private vendor quotation link" class="inline-flex items-center gap-1 rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1.5 font-medium text-blue-800 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                    Generate link
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('rfqs.invitations.print', ['rfq' => $rfq->id, 'invitation' => $invitation->id]) }}" target="_blank" rel="noopener noreferrer" title="Open the blank quotation form to print or save as PDF" class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
                            Print / PDF
                        </a>
                        @if (\Illuminate\Support\Facades\Route::has('rfqs.invitations.editable-pdf'))
                            <a href="{{ route('rfqs.invitations.editable-pdf', ['rfq' => $rfq->id, 'invitation' => $invitation->id]) }}" title="Download fillable quotation PDF" class="inline-flex items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 font-medium text-emerald-800 hover:bg-emerald-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                Editable PDF
                            </a>
                        @else
                            <span title="Editable PDF generation will be enabled in the next phase" class="inline-flex cursor-not-allowed items-center gap-1 rounded-md border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-gray-400" aria-disabled="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                Editable PDF (soon)
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-sm text-gray-500">No vendors assigned.</div>
            @endforelse
        </div>
    </section>

</div>
@endsection
