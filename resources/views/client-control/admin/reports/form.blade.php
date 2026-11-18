@extends('layouts.app')
@section('content')
@php
  $isDraft = !$report || $report->status === 'Draft';
  $canEdit = $actions['draft'];
@endphp
<div class="max-w-5xl mx-auto px-4 py-8">
  <a href="{{ route('client-control.admin.reports.index') }}" class="text-teal-700 font-semibold">← Back to Reports</a>
  <header class="my-5">
    <h1 class="text-2xl font-bold text-slate-900">{{ $report ? 'Weekly report · '.$report->report_number : 'New weekly client progress report' }}</h1>
    <p class="mt-1 text-slate-500">Internal preparation and verification · Client publication disabled</p>
  </header>
  @if(session('success'))<div class="mb-4 rounded-xl bg-emerald-50 text-emerald-800 p-4">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="mb-4 rounded-xl bg-red-50 text-red-800 p-4"><strong>Please correct:</strong><ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <form class="bg-white rounded-2xl border border-slate-200 p-6 space-y-6" method="POST" action="{{ $report ? route('client-control.admin.reports.update',$report->id) : route('client-control.admin.reports.store') }}">
    @csrf
    @if($report) @method('PUT')<input type="hidden" name="revision" value="{{ $report->revision }}"> @endif
    <fieldset @disabled(!$canEdit) class="space-y-6">
      <div class="grid md:grid-cols-3 gap-4">
        <label class="block text-sm font-semibold text-slate-700">Project
          <select name="project_id" required @disabled($report) class="block mt-2 w-full rounded-xl border-slate-300">
            <option value="">Select project</option>
            @foreach($projects as $project)<option value="{{ $project->id }}" @selected(old('project_id',$report->project_id ?? null)==$project->id)>{{ $project->project_name }} ({{ $project->project_code }})</option>@endforeach
          </select>
          @if($report)<input type="hidden" name="project_id" value="{{ $report->project_id }}">@endif
        </label>
        <label class="block text-sm font-semibold text-slate-700">Week starts (Monday)<input type="date" name="period_start" required @readonly($report) value="{{ old('period_start',$report->period_start ?? '') }}" class="block mt-2 w-full rounded-xl border-slate-300"></label>
        <label class="block text-sm font-semibold text-slate-700">Week ends (Sunday)<input type="date" name="period_end" required @readonly($report) value="{{ old('period_end',$report->period_end ?? '') }}" class="block mt-2 w-full rounded-xl border-slate-300"></label>
      </div>
      <label class="block text-sm font-semibold text-slate-700">Report title<input name="title" required maxlength="200" value="{{ old('title',$report->title ?? 'Weekly Project Progress Update') }}" class="block mt-2 w-full rounded-xl border-slate-300"></label>
      @foreach(['executive_summary'=>'Executive summary','completed_work'=>'Work completed this week','ongoing_work'=>'Work in progress','next_week_outlook'=>'Next week outlook','risks_and_actions'=>'Risks, constraints & actions'] as $key=>$label)
        <label class="block text-sm font-semibold text-slate-700">{{ $label }}<textarea name="{{ $key }}" rows="4" maxlength="10000" class="block mt-2 w-full rounded-xl border-slate-300" placeholder="Write only verified, client-appropriate information">{{ old($key,$report->{$key} ?? '') }}</textarea></label>
      @endforeach
    </fieldset>
    <div class="flex flex-wrap justify-between items-center gap-4 border-t border-slate-200 pt-5">
      <p class="text-sm text-amber-800">{{ $isDraft ? 'Draft can be edited until submitted.' : 'Submitted report is read-only. Return it to Draft for corrections.' }}</p>
      <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('client-control.admin.reports.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700">← Back to Reports</a>
        @if($canEdit)<button class="rounded-xl bg-teal-700 text-white px-6 py-3 font-semibold" type="submit">{{ $report ? 'Save draft' : 'Create draft' }}</button>@endif
      </div>
    </div>
  </form>
  @if($report)
  <section class="mt-6 bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
    <h2 class="text-xl font-bold text-slate-900">Verification workflow</h2>
    <p class="text-sm text-slate-600">Status: <strong>{{ $report->status }}</strong> · Revision {{ $report->revision }} · Submission cycle {{ $report->submission_cycle }}</p>
    @if($actions['submit'])
      <form method="POST" action="{{ route('client-control.admin.reports.submit',$report->id) }}">@csrf<input type="hidden" name="revision" value="{{ $report->revision }}"><button class="bg-teal-700 text-white rounded-xl px-5 py-3">Submit for PM verification</button></form>
    @elseif($actions['pm_verify'])
      <form method="POST" action="{{ route('client-control.admin.reports.pm-verify',$report->id) }}" class="space-y-3">@csrf<input type="hidden" name="revision" value="{{ $report->revision }}"><textarea name="remarks" maxlength="2000" placeholder="Remarks (mandatory when returning)" class="w-full rounded-xl border-slate-300"></textarea><div class="flex flex-wrap gap-2"><button name="decision" value="Approve" class="bg-teal-700 text-white rounded-xl px-4 py-2">PM Verify</button><button name="decision" value="Return" class="bg-amber-100 rounded-xl px-4 py-2">Return to Draft</button></div></form>
    @elseif(count($actions['departments']))
      <form method="POST" action="{{ route('client-control.admin.reports.review',$report->id) }}" class="space-y-3">@csrf<input type="hidden" name="revision" value="{{ $report->revision }}"><label class="block text-sm font-semibold">Department signoff<select name="department" required class="block mt-2 w-full rounded-xl border-slate-300">@foreach($actions['departments'] as $department)<option value="{{ $department }}">{{ $department }}</option>@endforeach</select></label><textarea name="remarks" maxlength="2000" placeholder="Remarks (mandatory when returning)" class="w-full rounded-xl border-slate-300"></textarea><div class="flex flex-wrap gap-2"><button name="decision" value="Approve" class="bg-teal-700 text-white rounded-xl px-4 py-2">Sign off</button><button name="decision" value="Return" class="bg-amber-100 rounded-xl px-4 py-2">Return to Draft</button></div></form>
    @else
      <p class="text-sm text-slate-500">No workflow action is available to your account at the current stage.</p>
    @endif
    <h3 class="font-semibold">Current cycle review history</h3>
    @forelse($reviews ?? [] as $review)<p class="text-sm text-slate-700">{{ $review->department }} — {{ $review->decision }} · {{ $review->acted_at }} @if($review->remarks) — {{ $review->remarks }} @endif</p>@empty<p class="text-sm text-slate-500">No reviews recorded.</p>@endforelse
    <p class="text-sm text-amber-800">Internal approval only. This report is not published to the client.</p>
  </section>
  @endif
</div>
@endsection
