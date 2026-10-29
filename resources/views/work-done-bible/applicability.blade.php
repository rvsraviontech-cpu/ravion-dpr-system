@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto space-y-5">
    <div><h1 class="text-2xl font-bold text-slate-900">Work Done Bible · Project Applicability</h1>
    <p class="text-sm text-slate-600">Admin / PMO only · Explicit WD-code decisions · No automatic activation</p></div>
    @if(session('success'))<div class="p-3 rounded-lg border border-green-200 bg-green-50 text-green-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="p-3 rounded-lg border border-red-200 bg-red-50 text-red-800"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="bg-white rounded-xl border border-slate-200 p-4">
      <form method="GET" action="{{ route('work-done-bible.applicability.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <label class="text-sm font-medium">Project
          <select name="project_id" required class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
            <option value="">Select assigned project</option>
            @foreach($projects as $project)<option value="{{ $project->id }}" @selected($selected && $selected->id === $project->id)>{{ $project->project_name }} ({{ $project->type_code }})</option>@endforeach
          </select>
        </label>
        <label class="text-sm font-medium">Operation search
          <input name="search" value="{{ $search }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm" placeholder="Search operation name, trade or element">
        </label>
        <label class="text-sm font-medium">Bible stage ID
          <select name="stage_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"><option value="">All stages</option>@foreach($stages as $stage)<option value="{{ $stage }}" @selected($stageId === (int)$stage)>Stage {{ $stage }}</option>@endforeach</select>
        </label>
        <button class="bg-slate-900 text-white rounded-lg px-4 py-2.5">Load operations</button>
      </form>
      @if(!$projects->count())<p class="mt-3 text-amber-800 text-sm">No projects have a Bible project type assigned yet. Complete Step 2B first.</p>@endif
    </div>
    @if($selected)
      <div class="bg-white rounded-xl border border-slate-200 p-4">
        <h2 class="font-semibold text-slate-900">{{ $selected->project_name }} · {{ $selected->type_code }} — {{ $selected->type_name }}</h2>
        <p class="text-xs text-slate-500 mt-1">Active Bible operations inherit Enabled by default. Template and project overrides can disable them. This page records project-specific applicability; it does not modify existing Work Done records.</p>
        {{-- Compact, one-operation-per-row configuration table. Each control targets its own form. --}}
        <div class="mt-4 w-full">
          <table class="w-full table-fixed text-xs lg:text-sm text-left">
            <colgroup>
              <col style="width:27%"><col style="width:16%"><col style="width:18%"><col style="width:14%"><col style="width:17%"><col style="width:8%">
            </colgroup>
            <thead class="bg-slate-100 text-slate-700">
              <tr>
                <th class="px-2 py-3">Bible operation</th>
                <th class="px-2 py-3">Current configuration</th>
                <th class="px-2 py-3">Select applicability</th>
                <th class="px-2 py-3">Enabled / Disabled</th>
                <th class="px-2 py-3">Reason</th>
                <th class="px-2 py-3">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              @forelse($operations as $op)
                @php($formId = 'wd-applicability-form-' . $op->id)
                <tr class="align-middle hover:bg-slate-50">
                  <td class="px-2 py-3">
                    <div class="font-semibold text-slate-900 break-words">{{ $op->micro_operation }}</div>
                    <div class="text-xs text-slate-500 mt-1 break-words">Stage {{ $op->stage_id }} · {{ $op->trade }} · {{ $op->element }} · {{ $op->record_type }} · {{ $op->uom }}</div>
                  </td>
                  <td class="px-2 py-3">
                    <span class="font-medium break-words">{{ $op->applicability ?? 'Applicable (inherited)' }}</span>
                    <div class="text-xs mt-1 {{ $op->is_enabled ? 'text-green-700' : 'text-slate-500' }}">{{ $op->applicability !== null ? ($op->is_enabled ? 'Enabled' : 'Disabled') : (($op->template_review_status === 'Approved' && !$op->template_enabled) ? 'Disabled (template)' : 'Enabled (template)') }}</div>
                    @unless($op->is_active)<div class="text-xs text-red-700 mt-1">Inactive Bible operation</div>@endunless
                  </td>
                  <td class="px-2 py-3">
                    @if($op->is_active)
                      <select form="{{ $formId }}" name="applicability" required aria-label="Applicability for {{ $op->micro_operation }}" class="block w-full min-w-0 rounded-md border-slate-300 text-xs px-1.5 py-2">
                        <option value="">Select applicability</option>
                        @foreach(['Applicable', 'Optional', 'Not Applicable'] as $value)
                          <option value="{{ $value }}" @selected(($op->applicability ?? 'Applicable') === $value)>{{ $value }}</option>
                        @endforeach
                      </select>
                    @else
                      <span class="text-xs text-slate-500">Unavailable</span>
                    @endif
                  </td>
                  <td class="px-2 py-3">
                    @if($op->is_active)
                      <select form="{{ $formId }}" name="is_enabled" required aria-label="Enabled status for {{ $op->micro_operation }}" class="block w-full min-w-0 rounded-md border-slate-300 text-xs px-1.5 py-2">
                        <option value="0" @selected($op->applicability !== null ? !$op->is_enabled : ($op->template_review_status === 'Approved' && !$op->template_enabled))>Disabled</option>
                        <option value="1" @selected($op->applicability !== null ? (bool)$op->is_enabled : !($op->template_review_status === 'Approved' && !$op->template_enabled))>Enabled</option>
                      </select>
                    @else
                      <span class="text-xs text-slate-500">Unavailable</span>
                    @endif
                  </td>
                  <td class="px-2 py-3">
                    @if($op->is_active)
                      <input form="{{ $formId }}" name="reason" type="text" required minlength="3" maxlength="1000" aria-label="Reason for {{ $op->micro_operation }}" placeholder="Reason for decision" class="block w-full min-w-0 rounded-md border-slate-300 text-xs px-1.5 py-2">
                    @else
                      <span class="text-xs text-red-700">Cannot configure</span>
                    @endif
                  </td>
                  <td class="px-2 py-3">
                    @if($op->is_active)
                      <form id="{{ $formId }}" method="POST" action="{{ route('work-done-bible.applicability.store') }}">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $selected->id }}">
                        <input type="hidden" name="operation_id" value="{{ $op->id }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="stage_id" value="{{ $stageId ?: '' }}">
                        <button type="submit" class="bg-slate-900 text-white rounded-md px-2 py-2 whitespace-nowrap text-xs hover:bg-slate-700">Save</button>
                      </form>
                    @else
                      <span class="text-xs text-slate-500">—</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="p-6 text-center text-slate-500">No matching Bible operations.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="mt-4">{{ $operations->links() }}</div>
      </div>
      <div class="bg-white rounded-xl border border-slate-200 p-4"><h2 class="font-semibold mb-3">Recent applicability audit history</h2><div class="overflow-x-auto"><table class="w-full text-sm text-left"><thead class="bg-slate-100"><tr><th class="p-2">Operation</th><th class="p-2">Previous</th><th class="p-2">New</th><th class="p-2">Changed by</th><th class="p-2">Date</th><th class="p-2">Reason</th></tr></thead><tbody class="divide-y">@forelse($history as $h)<tr><td class="p-2">{{ $h->operation_name }}</td><td class="p-2">{{ $h->old_applicability ?? 'Unconfigured' }} / {{ is_null($h->old_is_enabled) ? '—' : ($h->old_is_enabled ? 'Enabled' : 'Disabled') }}</td><td class="p-2">{{ $h->new_applicability }} / {{ $h->new_is_enabled ? 'Enabled' : 'Disabled' }}</td><td class="p-2">{{ $h->changed_by }}</td><td class="p-2">{{ $h->changed_at }}</td><td class="p-2">{{ $h->reason }}</td></tr>@empty<tr><td colspan="6" class="p-4 text-slate-500">No configuration history yet.</td></tr>@endforelse</tbody></table></div></div>
    @endif
</div>
@endsection
