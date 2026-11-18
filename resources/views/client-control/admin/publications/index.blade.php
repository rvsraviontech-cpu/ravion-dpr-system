@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-7" style="font-family:Inter,ui-sans-serif,system-ui,sans-serif">
  <div class="rounded-2xl p-6 md:p-8 mb-6 text-white" style="background:linear-gradient(115deg,#102b42,#174a60 72%,#087f8c)">
    <div class="text-xs uppercase font-bold tracking-widest mb-2" style="color:#a7e6e8">Client Control · Internal Administration</div>
    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Publication Registry</h1>
    <p class="text-sm mt-2 max-w-3xl" style="color:#d1e0e9">Controlled release of client-visible project information. Source verification and departmental signoffs are required before approval and publication can be enabled.</p>
  </div>
  @if(session('success'))<div class="p-4 mb-4 rounded-xl border border-green-200 bg-green-50 text-green-900">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="p-4 mb-4 rounded-xl border border-red-200 bg-red-50 text-red-900">{{ $errors->first() }}</div>@endif
  <div class="rounded-xl p-4 mb-5 text-sm border" style="background:#eef8fa;border-color:#d8e9ef;color:#45687a"><strong>Release safeguard:</strong> New publication preparation, approval and publishing remain disabled until verified ERP source adapters and departmental signoffs are implemented. Existing withdrawal actions remain available to authorised users.</div>
  <div class="overflow-x-auto bg-white rounded-2xl shadow-sm border border-gray-200">
    <table class="w-full text-sm text-left" style="min-width:950px">
      <thead style="background:#f4f7fb;color:#516c80"><tr><th class="p-4">Project</th><th class="p-4">Source</th><th class="p-4">Revision</th><th class="p-4">Visibility</th><th class="p-4">Approval</th><th class="p-4">Withdrawal</th><th class="p-4">Published</th><th class="p-4">Action</th></tr></thead>
      <tbody>
        @forelse($publications as $publication)
        <tr class="border-t border-gray-100 hover:bg-gray-50">
          <td class="p-4 font-semibold" style="color:#17334b">{{ $publication->project_name }}<div class="text-xs font-normal text-gray-500 mt-1">{{ $publication->project_code }}</div></td>
          <td class="p-4">{{ $publication->source_module }} #{{ $publication->source_record_id }}</td><td class="p-4">{{ $publication->source_revision }}</td><td class="p-4">{{ $publication->visibility_role }}</td>
          <td class="p-4"><span class="inline-block rounded-lg bg-blue-50 text-blue-800 px-2.5 py-1 text-xs font-semibold">{{ $publication->approval_status }}</span></td>
          <td class="p-4"><span class="inline-block rounded-lg bg-gray-100 text-gray-700 px-2.5 py-1 text-xs font-semibold">{{ $publication->withdrawal_status }}</span></td>
          <td class="p-4">{{ $publication->published_at ?: 'Not published' }}</td>
          <td class="p-4">@if($publication->withdrawal_status !== 'Withdrawn')<form method="POST" action="{{ route('client-control.admin.publications.withdraw', $publication->id) }}" onsubmit="return confirm('Withdraw this publication?')">@csrf<label class="sr-only" for="reason-{{ $publication->id }}">Withdrawal reason</label><input id="reason-{{ $publication->id }}" name="reason" type="text" required maxlength="2000" placeholder="Reason for withdrawal" class="border border-gray-300 rounded-lg px-3 py-2 w-44 text-xs"><button type="submit" class="rounded-lg px-3 py-2 mt-2 text-xs font-bold text-white" style="background:#102b42">Withdraw</button></form>@else<span class="text-gray-400 text-xs">Withdrawn</span>@endif</td>
        </tr>
        @empty
        <tr><td colspan="8" class="p-12 text-center"><div class="font-bold text-lg" style="color:#17334b">No publications recorded</div><div class="text-sm text-gray-500 mt-2">Verified and approved project content will appear in this registry when the publication workflow is released.</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-5">{{ $publications->links() }}</div>
</div>
@endsection
