@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">
    <a href="{{ route('client-control.admin.progress.show', $projectRecord->id) }}" class="text-sm font-semibold text-teal-700">&larr; {{ $projectRecord->project_name }}</a>
    <div><p class="text-xs font-bold text-teal-700 uppercase">Client Control / Import</p><h1 class="text-3xl font-bold text-slate-900 mt-2">Apply Common Stages</h1><p class="text-slate-500 mt-2">{{ $projectRecord->project_name }} · Current assigned weight {{ number_format($assignedWeight, 2) }} / 100%</p></div>
    @if($errors->any())<div class="rounded-xl bg-red-50 text-red-800 p-4"><ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('client-control.admin.progress.master.import', $projectRecord->id) }}" class="space-y-4">@csrf
        <div class="bg-white border border-slate-200 rounded-2xl divide-y">
            @forelse($stages as $stage)
                @php($duplicate = in_array(mb_strtolower(trim($stage->name)), $existingNames, true))
                <label class="flex gap-3 items-center p-4 {{ $duplicate ? 'opacity-60' : '' }}">
                    <input type="checkbox" name="stage_ids[]" value="{{ $stage->id }}" {{ $duplicate ? 'disabled' : '' }} class="rounded border-slate-300 text-teal-700">
                    <span class="flex-1"><strong class="text-slate-900">{{ $stage->name }}</strong><span class="block text-sm text-slate-500">Suggested weight: {{ number_format($stage->suggested_weightage, 2) }}%</span></span>
                    @if($duplicate)<span class="text-xs text-amber-700 font-semibold">Already in project</span>@endif
                </label>
            @empty<div class="p-6 text-slate-500">No active common stages. Create them in the Common Master first.</div>@endforelse
        </div>
        <p class="text-sm text-amber-800">Selected stage weights plus the current {{ number_format($assignedWeight, 2) }}% must not exceed 100%. New tasks start at 0%. Existing progress and published snapshots remain unchanged.</p>
        <label class="flex gap-2 items-start text-sm text-slate-700"><input type="checkbox" name="confirm_import" value="1" required class="rounded mt-1">I have checked the selected stages and understand they will be added to this project.</label>
        <button class="bg-teal-700 text-white px-6 py-3 rounded-xl font-semibold">Import Selected Stages</button>
    </form>
</div>
@endsection
