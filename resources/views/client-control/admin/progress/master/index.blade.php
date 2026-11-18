@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 space-y-6">
    <a class="text-teal-700 text-sm font-semibold" href="{{ route('client-control.admin.progress.index') }}">&larr; Work Progress Projects</a>
    <div><p class="text-xs font-bold tracking-wider uppercase text-teal-700">Client Control / Master Data</p><h1 class="text-3xl font-bold text-slate-900 mt-2">Common Stages &amp; Tasks</h1><p class="text-slate-500 mt-2">Reusable definitions. Changes here do not alter existing project records.</p></div>
    @if(session('success'))<div class="p-4 rounded-xl bg-emerald-50 text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="p-4 rounded-xl bg-red-50 text-red-800"><ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <section class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="font-bold text-lg mb-4">Create Common Stage</h2>
        <form method="POST" action="{{ route('client-control.admin.progress.master.stages.store') }}" class="grid md:grid-cols-4 gap-3">@csrf
            <input name="name" required maxlength="255" placeholder="Stage name" class="rounded-xl border-slate-300 md:col-span-2">
            <input name="suggested_weightage" type="number" step="0.0001" min="0" max="100" required placeholder="Suggested weight %" onwheel="this.blur()" class="rounded-xl border-slate-300">
            <button class="rounded-xl bg-teal-700 text-white font-semibold px-4 py-3">Add Stage</button>
            <textarea name="description" rows="2" maxlength="5000" placeholder="Description (optional)" class="rounded-xl border-slate-300 md:col-span-4"></textarea>
        </form>
    </section>
    @forelse($stages as $stage)
        <section class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <div class="flex justify-between gap-3"><h2 class="font-bold text-xl text-slate-900">{{ $stage->name }}</h2><span class="text-teal-700 font-semibold">{{ number_format($stage->suggested_weightage, 2) }}%</span></div>
            <form method="POST" action="{{ route('client-control.admin.progress.master.stages.update', $stage->id) }}" class="grid md:grid-cols-6 gap-3">@csrf @method('PATCH')
                <label class="md:col-span-2 text-xs text-slate-600">Name<input name="name" value="{{ $stage->name }}" required maxlength="255" class="block w-full rounded-xl border-slate-300 mt-1"></label>
                <label class="text-xs text-slate-600">Weight %<input name="suggested_weightage" value="{{ $stage->suggested_weightage }}" type="number" step="0.0001" min="0" max="100" onwheel="this.blur()" required class="block w-full rounded-xl border-slate-300 mt-1"></label>
                <label class="text-xs text-slate-600">Order<input name="sort_order" value="{{ $stage->sort_order }}" type="number" min="0" onwheel="this.blur()" required class="block w-full rounded-xl border-slate-300 mt-1"></label>
                <label class="md:col-span-2 text-xs text-slate-600">Description<input name="description" value="{{ $stage->description }}" maxlength="5000" class="block w-full rounded-xl border-slate-300 mt-1"></label>
                <button class="md:col-span-6 justify-self-start bg-slate-900 text-white px-5 py-2 rounded-xl">Save Stage</button>
            </form>
            <div class="border-t pt-4 space-y-3"><h3 class="font-bold text-slate-800">Common Tasks</h3>
                @forelse($tasks->get($stage->id, collect()) as $task)
                    <div class="bg-slate-50 rounded-xl p-3 space-y-2">
                        <form method="POST" action="{{ route('client-control.admin.progress.master.tasks.update', [$stage->id, $task->id]) }}" class="grid md:grid-cols-6 gap-2 items-end">@csrf @method('PATCH')
                            <label class="md:col-span-2 text-xs">Task<input name="name" value="{{ $task->name }}" required maxlength="255" class="block w-full rounded-lg border-slate-300 mt-1"></label>
                            <label class="text-xs">Relative weight<input name="suggested_weightage" value="{{ $task->suggested_weightage }}" type="number" min="0.0001" max="10000" step="0.0001" onwheel="this.blur()" required class="block w-full rounded-lg border-slate-300 mt-1"></label>
                            <label class="text-xs">Order<input name="sort_order" value="{{ $task->sort_order }}" type="number" min="0" onwheel="this.blur()" required class="block w-full rounded-lg border-slate-300 mt-1"></label>
                            <label class="text-xs">Description<input name="description" value="{{ $task->description }}" maxlength="5000" class="block w-full rounded-lg border-slate-300 mt-1"></label>
                            <button class="bg-teal-700 text-white rounded-lg py-2">Save</button>
                        </form>
                        <form method="POST" action="{{ route('client-control.admin.progress.master.tasks.archive', [$stage->id, $task->id]) }}" onsubmit="return confirm('Archive this common task? Existing projects will not change.')">@csrf @method('PATCH')<button class="text-xs text-rose-700">Archive task</button></form>
                    </div>
                @empty<p class="text-sm text-slate-500">No tasks yet.</p>@endforelse
                <form method="POST" action="{{ route('client-control.admin.progress.master.tasks.store', $stage->id) }}" class="grid md:grid-cols-4 gap-3">@csrf
                    <input name="name" required maxlength="255" placeholder="New task" class="rounded-xl border-slate-300">
                    <input name="description" maxlength="5000" placeholder="Description" class="rounded-xl border-slate-300">
                    <input name="suggested_weightage" value="1" type="number" min="0.0001" max="10000" step="0.0001" onwheel="this.blur()" required class="rounded-xl border-slate-300">
                    <button class="border border-teal-700 text-teal-700 rounded-xl px-4 py-2 font-semibold">Add Task</button>
                </form>
            </div>
            <form method="POST" action="{{ route('client-control.admin.progress.master.stages.archive', $stage->id) }}" onsubmit="return confirm('Archive this common stage? Existing projects will not change.')">@csrf @method('PATCH')<button class="text-sm text-rose-700">Archive common stage</button></form>
        </section>
    @empty<div class="border border-dashed rounded-2xl p-8 text-center text-slate-500">No common stages configured yet.</div>@endforelse
</div>
<style>input[type=number]{appearance:textfield;-moz-appearance:textfield}input[type=number]::-webkit-inner-spin-button,input[type=number]::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}</style>
@endsection
