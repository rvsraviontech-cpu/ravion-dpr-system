@extends('layouts.app')
@section('content')
@php
$money = fn($n) => '₹'.number_format((float)$n,2);
@endphp
<div class="max-w-7xl mx-auto px-4 py-6 space-y-5">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div><a class="text-sm text-blue-700" href="{{ route('client-control.admin.payments.show', $projectRecord->id) }}">← Payment schedule</a><h1 class="text-2xl font-bold mt-1">{{ $projectRecord->project_name }} — Client Payment Receipts</h1><p class="text-sm text-slate-500">Client payments only · Pending receipts do not count as received</p></div>
  </div>
  @if(session('success'))<div class="rounded-lg bg-green-50 border border-green-200 text-green-800 p-3">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="rounded-lg bg-red-50 border border-red-200 text-red-800 p-3"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50"><tr>
      <th class="text-left p-3">Date / ID</th><th class="text-left p-3">Payment stage</th><th class="text-right p-3">Amount</th><th class="text-left p-3">Method / Reference</th><th class="text-left p-3">Status</th><th class="text-left p-3">Action</th>
    </tr></thead><tbody>
    @forelse($receipts as $receipt)
      <tr class="border-t align-top"><td class="p-3 whitespace-nowrap">{{ $receipt->received_on }}<div class="text-xs text-slate-500">#{{ $receipt->id }}</div></td>
        <td class="p-3">{{ $receipt->milestone_title ?? 'Unallocated' }}</td>
        <td class="p-3 text-right font-semibold whitespace-nowrap">{{ $money($receipt->amount) }}</td>
        <td class="p-3">{{ $receipt->payment_method ?: '—' }}<div class="text-xs text-slate-500">{{ $receipt->transaction_reference ?: ($receipt->receipt_reference ?: 'No reference') }}</div></td>
        <td class="p-3"><span class="rounded px-2 py-1 text-xs {{ $receipt->status === 'verified' ? 'bg-green-100 text-green-800' : ($receipt->status === 'void' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ $receipt->status === 'draft' ? 'Pending verification' : ($receipt->status === 'void' ? 'Rejected' : 'Verified') }}</span></td>
        <td class="p-3">@if($canVerify && $receipt->status === 'draft')
          <div class="flex flex-col gap-2 min-w-[170px]">
            <form method="POST" action="{{ route('client-control.admin.payments.receipts.verify', [$projectRecord->id,$receipt->id]) }}" onsubmit="return confirm('Verify that this payment was actually received?')">@csrf<button class="text-green-700 font-semibold">Verify payment</button></form>
            <form method="POST" action="{{ route('client-control.admin.payments.receipts.void', [$projectRecord->id,$receipt->id]) }}" onsubmit="return confirm('Reject this pending receipt? This will be recorded in the audit history.')">@csrf
              <label class="text-xs text-slate-600" for="reason-{{ $receipt->id }}">Rejection reason</label><input id="reason-{{ $receipt->id }}" name="reason" required minlength="5" maxlength="1000" class="w-full border rounded px-2 py-1 text-xs" placeholder="Why is this receipt incorrect?"><button class="text-red-700 font-semibold mt-1">Reject receipt</button>
            </form>
          </div>
        @else<span class="text-slate-400">—</span>@endif</td>
      </tr>
    @empty<tr><td colspan="6" class="p-8 text-center text-slate-500">No client payment receipts recorded.</td></tr>@endforelse
    </tbody></table></div>
    @if($receipts instanceof \Illuminate\Pagination\LengthAwarePaginator)<div class="p-4 border-t">{{ $receipts->links() }}</div>@endif
  </div>
  <p class="text-xs text-slate-500">Verified receipts cannot be rejected from this screen. Corrections to verified payments require a separate authorized reversal workflow.</p>
</div>
@endsection
