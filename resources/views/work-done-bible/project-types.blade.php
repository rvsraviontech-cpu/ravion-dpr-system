@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
        <div><h1 class="text-2xl font-bold text-slate-900">Work Done Bible · Project Type Assignment</h1>
            <p class="text-sm text-slate-600 mt-1">Admin / PMO configuration · Bible V5 PT01–PT09 · Does not activate WD operations</p></div>
        @if(session('success'))<div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-emerald-900">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-lg bg-red-50 border border-red-200 p-3 text-red-900"><ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4">
            <form method="GET" action="{{ route('work-done-bible.project-types.index') }}" class="flex flex-wrap gap-2 mb-4">
                <input name="search" value="{{ $search }}" placeholder="Search project name or code" class="rounded-lg border-slate-300 flex-1 min-w-48" />
                <button class="rounded-lg bg-slate-800 text-white px-4 py-2">Search</button>
                <a class="rounded-lg border px-4 py-2" href="{{ route('work-done-bible.project-types.index') }}">Clear</a>
            </form>
            <div class="overflow-x-auto"><table class="w-full text-sm text-left"><thead class="bg-slate-100 text-slate-700"><tr><th class="p-3">Project</th><th class="p-3">Existing Project Type (unchanged)</th><th class="p-3">Bible Type</th><th class="p-3">Assignment / Change</th></tr></thead><tbody class="divide-y">
            @forelse($projects as $project)
            <tr class="align-top"><td class="p-3"><div class="font-semibold">{{ $project->project_name }}</div><div class="text-slate-500">{{ $project->project_code }}</div></td>
                <td class="p-3">{{ $project->project_type ?: 'Not specified' }}</td>
                <td class="p-3">@if($project->bible_code)<strong>{{ $project->bible_code }}</strong> — {{ $project->bible_name }}@else<span class="text-amber-700">Unassigned</span>@endif</td>
                <td class="p-3 min-w-64"><form method="POST" action="{{ route('work-done-bible.project-types.store') }}" class="space-y-2">@csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}" />
                    <select name="bible_project_type_id" required class="rounded-lg border-slate-300 w-full text-sm"><option value="">Select Bible project type</option>
                    @foreach($types as $type)<option value="{{ $type->id }}" @selected((int)$project->bible_project_type_id === (int)$type->id)>{{ $type->code }} — {{ $type->project_type }}</option>@endforeach</select>
                    <textarea name="reason" rows="2" placeholder="Reason (required when changing existing type)" class="rounded-lg border-slate-300 w-full text-sm"></textarea>
                    <button class="rounded-lg bg-slate-800 text-white px-3 py-2 text-sm">Save assignment</button></form></td></tr>
            @empty<tr><td colspan="4" class="p-6 text-center text-slate-500">No matching projects.</td></tr>@endforelse
            </tbody></table></div><div class="mt-4">{{ $projects->links() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4"><h2 class="text-lg font-semibold mb-3">Recent assignment audit history</h2><div class="overflow-x-auto"><table class="w-full text-sm text-left"><thead class="bg-slate-100"><tr><th class="p-2">Project</th><th class="p-2">Previous</th><th class="p-2">New</th><th class="p-2">Changed by (User ID)</th><th class="p-2">Date</th><th class="p-2">Reason</th></tr></thead><tbody class="divide-y">@forelse($history as $row)<tr><td class="p-2">{{ $row->project_name }}</td><td class="p-2">{{ $row->old_code ?: 'Initial assignment' }}</td><td class="p-2">{{ $row->new_code }}</td><td class="p-2">{{ $row->changed_by }}</td><td class="p-2">{{ $row->changed_at }}</td><td class="p-2">{{ $row->reason ?: '—' }}</td></tr>@empty<tr><td colspan="6" class="p-4 text-slate-500">No assignment history yet.</td></tr>@endforelse</tbody></table></div></div>
    </div>
@endsection
