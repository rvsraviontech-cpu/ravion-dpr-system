@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto p-4 space-y-5">
  <div class="flex items-center justify-between"><h1 class="text-2xl font-bold text-slate-800">PMO · Work Done Review</h1><a class="text-blue-700 underline" href="{{ route('work-done.index') }}">Work Done Register</a></div>
  <div class="grid grid-cols-3 gap-3">
    @foreach(['Submitted'=>'Pending Review','Verified'=>'Verified','Returned'=>'Returned'] as $key=>$label)
      <div class="rounded-xl border bg-white p-4"><div class="text-sm text-slate-500">{{ $label }}</div><div class="text-2xl font-bold">{{ $counts[$key] ?? 0 }}</div></div>
    @endforeach
  </div>
  @if(session('success'))<div class="rounded bg-green-50 p-3 text-green-800">{{ session('success') }}</div>@endif
  <div class="overflow-x-auto rounded-xl border bg-white"><table class="w-full text-sm"><thead class="bg-slate-100 text-left"><tr><th class="p-3">Date</th><th class="p-3">Project</th><th class="p-3">Engineer</th><th class="p-3">Activities</th><th class="p-3">Status</th><th class="p-3">Review</th></tr></thead><tbody>
  @forelse($entries as $entry)
    <tr class="border-t align-top"><td class="p-3 whitespace-nowrap">{{ $entry->work_date?->format('d/m/Y') }}</td><td class="p-3">{{ $entry->project?->project_name }}</td><td class="p-3">{{ $entry->engineer?->name }}</td><td class="p-3">{{ $entry->items_count }}</td><td class="p-3">{{ $entry->status }}</td><td class="p-3 space-y-2">
      <a class="text-blue-700 underline" href="{{ route('work-done.show',$entry) }}">View full report</a>
      @if($entry->status === 'Submitted')
        <form method="POST" action="{{ route('work-done.review.verify',$entry) }}" onsubmit="return confirm('Verify and lock this Work Done entry?')">@csrf <button class="rounded bg-emerald-700 px-3 py-1 text-white">Verify</button></form>
        <form method="POST" action="{{ route('work-done.review.return',$entry) }}">@csrf<textarea class="w-full rounded border p-2" name="review_remarks" required minlength="5" placeholder="Correction remarks for Engineer"></textarea><button class="rounded bg-amber-600 px-3 py-1 text-white">Return</button></form>
      @elseif($entry->status === 'Returned')<div class="text-amber-800">{{ $entry->review_remarks }}</div>@endif
    </td></tr>
  @empty <tr><td colspan="6" class="p-6 text-center text-slate-500">No submitted entries yet.</td></tr> @endforelse
  </tbody></table></div>{{ $entries->links() }}
</div>
@endsection
