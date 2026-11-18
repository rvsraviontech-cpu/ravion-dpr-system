@extends('layouts.app')

@section('content')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">Client Control / Quality &amp; Testing / Test Master</h2>
    <div class="py-6"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
        @if(session('success'))<div class="rounded-lg bg-green-50 border border-green-200 text-green-800 p-4">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-lg bg-red-50 border border-red-200 text-red-800 p-4"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="bg-white shadow-sm rounded-xl p-5">
            <h3 class="font-semibold text-lg mb-1">Construction Quality Test Master</h3>
            <p class="text-sm text-gray-500 mb-4">Configurable reference catalogue. Tests are not automatically mandatory for any project. Standards and acceptance limits must be confirmed for each project.</p>
            <form method="GET" class="flex flex-wrap gap-3 mb-4">
                <input name="q" value="{{ request('q') }}" placeholder="Search code, test or category" class="rounded-lg border-gray-300 flex-1 min-w-48" />
                <select name="category" class="rounded-lg border-gray-300"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>@endforeach</select>
                <select name="status" class="rounded-lg border-gray-300"><option value="">All statuses</option><option value="active" @selected(request('status')==='active')>Active</option><option value="archived" @selected(request('status')==='archived')>Archived</option></select>
                <button class="bg-slate-800 text-white rounded-lg px-4 py-2">Filter</button>
            </form>
            @if(auth()->user() && auth()->user()->hasPermission('client_control.quality.master.manage'))
            <details class="border rounded-lg p-4 mb-5"><summary class="font-medium cursor-pointer">Add a quality test</summary>
                <form method="POST" action="{{ route('client-control.admin.quality.tests.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">@csrf
                    <input required name="code" maxlength="24" placeholder="Unique code, e.g. RQT-091" class="rounded-lg border-gray-300" />
                    <input required name="name" maxlength="255" placeholder="Test name" class="rounded-lg border-gray-300" />
                    <input required name="category" maxlength="120" list="quality-categories" placeholder="Category" class="rounded-lg border-gray-300" />
                    <select required name="kind" class="rounded-lg border-gray-300"><option>Laboratory</option><option>Field</option><option>Inspection</option></select>
                    <input name="reference_standard" placeholder="Reference standard (optional)" class="rounded-lg border-gray-300" />
                    <input name="default_frequency" placeholder="Typical frequency (optional)" class="rounded-lg border-gray-300" />
                    <textarea name="acceptance_criteria" placeholder="Acceptance criteria (project-specific confirmation required)" class="rounded-lg border-gray-300"></textarea>
                    <textarea name="description" placeholder="Description" class="rounded-lg border-gray-300"></textarea>
                    <label class="text-sm"><input type="checkbox" name="requires_evidence" value="1" checked /> Evidence required</label>
                    <button class="bg-emerald-700 text-white rounded-lg px-4 py-2">Save test</button>
                </form>
            </details>
            @endif
            <datalist id="quality-categories">@foreach($categories as $category)<option value="{{ $category }}">@endforeach</datalist>
            <div class="overflow-x-auto"><table class="w-full text-sm text-left"><thead class="bg-slate-100 text-slate-700"><tr><th class="p-3">Code</th><th class="p-3">Test / Category</th><th class="p-3">Type</th><th class="p-3">Status</th><th class="p-3">Actions</th></tr></thead><tbody class="divide-y">
            @forelse($tests as $test)<tr><td class="p-3 font-mono">{{ $test->code }}</td><td class="p-3"><div class="font-medium">{{ $test->name }}</div><div class="text-gray-500">{{ $test->category }}</div></td><td class="p-3">{{ $test->kind }}</td><td class="p-3">{{ $test->is_active ? 'Active' : 'Archived' }}</td><td class="p-3">
                @if(auth()->user() && auth()->user()->hasPermission('client_control.quality.master.manage'))
                <details><summary class="text-blue-700 cursor-pointer">Edit</summary><form method="POST" action="{{ route('client-control.admin.quality.tests.update', $test->id) }}" class="grid gap-2 min-w-64 mt-2">@csrf @method('PUT')
                    <input name="code" required value="{{ $test->code }}" class="rounded border-gray-300" /><input name="name" required value="{{ $test->name }}" class="rounded border-gray-300" /><input name="category" required value="{{ $test->category }}" class="rounded border-gray-300" />
                    <select name="kind" class="rounded border-gray-300">@foreach(['Laboratory','Field','Inspection'] as $kind)<option @selected($test->kind === $kind)>{{ $kind }}</option>@endforeach</select>
                    <input name="reference_standard" value="{{ $test->reference_standard }}" placeholder="Standard" class="rounded border-gray-300" /><input name="default_frequency" value="{{ $test->default_frequency }}" placeholder="Frequency" class="rounded border-gray-300" />
                    <textarea name="acceptance_criteria" placeholder="Acceptance criteria" class="rounded border-gray-300">{{ $test->acceptance_criteria }}</textarea><textarea name="description" placeholder="Description" class="rounded border-gray-300">{{ $test->description }}</textarea>
                    <label><input type="checkbox" name="requires_evidence" value="1" @checked($test->requires_evidence) /> Evidence required</label><button class="rounded bg-slate-800 text-white px-3 py-2">Update</button>
                </form></details>
                <form method="POST" action="{{ route('client-control.admin.quality.tests.status', $test->id) }}" class="mt-2">@csrf @method('PATCH')<button onclick="return confirm('Change this test status?')" class="text-amber-700">{{ $test->is_active ? 'Archive' : 'Reactivate' }}</button></form>
                @endif
            </td></tr>@empty<tr><td colspan="5" class="p-5 text-center text-gray-500">No tests found.</td></tr>@endforelse
            </tbody></table></div><div class="mt-4">{{ $tests->links() }}</div>
        </div>
    </div></div>
@endsection
