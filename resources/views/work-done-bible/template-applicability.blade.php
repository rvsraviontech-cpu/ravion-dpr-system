@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl space-y-5">
  <div><h1 class="text-2xl font-bold text-slate-900">Work Done Bible · Template Operations</h1>
    <p class="mt-1 text-sm text-slate-600">Admin / PMO only · Review Bible operations for each project type · Active Bible operations are enabled by default; disable only exceptions</p></div>
  @if(session('success'))<div class="rounded-lg border border-green-200 bg-green-50 p-3 text-green-800">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 p-3 text-red-800"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <div class="rounded-xl border border-slate-200 bg-white p-4">
    <form method="GET" action="{{ route('work-done-bible.templates.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-5 md:items-end">
      <label class="text-sm font-medium">Project template
        <select name="project_type_id" required class="mt-1 block w-full rounded-lg border-slate-300 text-sm"><option value="">Select template</option>
          @foreach($templates as $template)<option value="{{ $template->id }}" @selected($selected && $selected->id === $template->id)>{{ $template->code }} — {{ $template->project_type }}</option>@endforeach
        </select>
      </label>
      <label class="text-sm font-medium">Search operations<input name="search" value="{{ $search }}" placeholder="Operation, trade or element" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"></label>
      <label class="text-sm font-medium">Bible stage<select name="stage_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"><option value="">All stages</option>@foreach($stages as $stage)<option value="{{ $stage }}" @selected($stageId === (int) $stage)>Stage {{ $stage }}</option>@endforeach</select></label>
      <label class="text-sm font-medium">Operation type<select name="record_type" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"><option value="">All types</option>@foreach(['PHYSICAL','CONTROL','TEST'] as $type)<option value="{{ $type }}" @selected($recordType === $type)>{{ ucfirst(strtolower($type)) }}</option>@endforeach</select></label>
      <button class="rounded-lg bg-slate-900 px-4 py-2.5 font-semibold text-white">Load operations</button>
    </form>
  </div>
  @if($selected)
    <div class="rounded-xl border border-slate-200 bg-white p-4">
      <div class="mb-3"><h2 class="font-semibold text-slate-900">{{ $selected->code }} — {{ $selected->project_type }}</h2>
        <p class="mt-1 text-xs text-slate-600">All active operations are enabled by default. Select only exceptions to disable or restore (current page only). WD codes are internal and are not displayed.</p></div>
      <form id="template-review-form" method="POST" action="{{ route('work-done-bible.templates.store') }}">@csrf
        <input type="hidden" name="project_type_id" value="{{ $selected->id }}">
        <input type="hidden" name="stage_id" value="{{ $stageId ?: '' }}">
        <input type="hidden" name="record_type" value="{{ $recordType }}">
        <input type="hidden" name="search" value="{{ $search }}">
        <input type="hidden" name="page" value="{{ $operations->currentPage() }}">
        <div class="grid grid-cols-1 gap-3 rounded-lg bg-slate-50 p-3 md:grid-cols-4 md:items-end">
          <label class="text-sm font-medium">Action<select name="decision" required class="mt-1 block w-full rounded-lg border-slate-300 text-sm"><option value="Disable">Disable selected</option><option value="Approve">Enable selected</option></select></label>
          <label class="text-sm font-medium md:col-span-2">Reason / verification note<input name="reason" required minlength="3" maxlength="1000" placeholder="Reason for enabling or disabling these operations" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"></label>
          <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 font-semibold text-white">Save selected & audit</button>
        </div>
        <div class="mt-4 overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-100 text-slate-700"><tr>
          <th class="w-10 p-2"><input type="checkbox" aria-label="Select all operations on this page" onclick="document.querySelectorAll('.wd-template-row').forEach(e => e.checked = this.checked)"></th>
          <th class="p-2">Bible operation</th><th class="p-2">Type</th><th class="p-2">Unit</th><th class="p-2">Template status</th>
        </tr></thead><tbody class="divide-y divide-slate-200">
          @forelse($operations as $op)<tr class="align-top hover:bg-slate-50">
            <td class="p-2"><input class="wd-template-row" type="checkbox" name="operation_ids[]" value="{{ $op->id }}" aria-label="Select {{ $op->micro_operation }}"></td>
            <td class="p-2"><div class="font-semibold text-slate-900">{{ $op->micro_operation }}</div><div class="mt-1 text-xs text-slate-500">Stage {{ $op->stage_id }} · {{ $op->trade }} · {{ $op->element }}</div></td>
            <td class="p-2">{{ $op->record_type }}</td><td class="p-2">{{ $op->uom }}</td>
            <td class="p-2"><span class="font-semibold">{{ $op->review_status === 'Approved' && !$op->is_enabled ? 'Template override' : 'Template default' }}</span><div class="text-xs {{ $op->review_status === 'Approved' && !$op->is_enabled ? 'text-red-700' : 'text-green-700' }}">{{ $op->review_status === 'Approved' && !$op->is_enabled ? 'Disabled' : 'Enabled' }}</div></td>
          </tr>@empty<tr><td colspan="5" class="p-6 text-center text-slate-500">No matching Bible operations.</td></tr>@endforelse
        </tbody></table></div>
      </form>
      <div class="mt-4">{{ $operations->links() }}</div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4"><h2 class="mb-3 font-semibold">Recent template audit history</h2>
      <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-100"><tr><th class="p-2">Operation</th><th class="p-2">Previous</th><th class="p-2">New</th><th class="p-2">User ID</th><th class="p-2">Reason</th><th class="p-2">Date</th></tr></thead><tbody class="divide-y">
        @forelse($history as $h)<tr><td class="p-2">{{ $h->operation_name }}</td><td class="p-2">{{ $h->old_review_status ?? 'Unreviewed' }} / {{ $h->old_is_enabled ? 'Enabled' : 'Disabled' }}</td><td class="p-2">{{ $h->new_review_status }} / {{ $h->new_is_enabled ? 'Enabled' : 'Disabled' }}</td><td class="p-2">{{ $h->changed_by }}</td><td class="p-2">{{ $h->reason }}</td><td class="p-2">{{ $h->created_at }}</td></tr>
        @empty<tr><td colspan="6" class="p-4 text-slate-500">No review decisions yet.</td></tr>@endforelse
      </tbody></table></div>
    </div>
  @endif
</div>
@endsection
