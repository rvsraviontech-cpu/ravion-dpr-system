@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-5">
    <a class="text-sm text-teal-700" href="{{ route('client-control.admin.decisions.index') }}">← Decision register</a>

    @if(session('success'))
        <div class="p-3 bg-teal-50 text-teal-800 rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="p-3 bg-amber-50 text-amber-800 rounded-xl">{{ session('warning') }}</div>
    @endif

    <div class="bg-white rounded-2xl border p-6 space-y-4">
        <div class="flex justify-between flex-wrap gap-4">
            <div>
                <div class="text-teal-700 font-semibold text-sm">{{ $decision->reference }} · {{ $project->project_name ?? '' }}</div>
                <h1 class="text-3xl font-bold text-[#0F2A52] mt-1">{{ $decision->title }}</h1>
                <div class="text-slate-500 mt-1">{{ $decision->category }} · Due {{ $decision->response_due_at ?? 'not set' }}</div>
            </div>
            <span class="self-start bg-slate-100 px-4 py-2 rounded-full font-semibold">{{ $decision->status }}</span>
        </div>
        <div class="whitespace-pre-wrap text-slate-700">{{ $decision->description }}</div>
        @if($decision->has_cost_impact || $decision->has_time_impact)
            <div class="bg-amber-50 rounded-xl p-4">
                <strong>Declared implications:</strong>
                <div class="whitespace-pre-wrap">{{ $decision->impact_details }}</div>
            </div>
        @endif
    </div>

    <div class="grid md:grid-cols-2 gap-5">
        <section class="bg-white rounded-2xl border p-5">
            <h2 class="font-bold text-[#0F2A52] mb-3">Options</h2>
            @forelse($options as $o)
                <div class="border-t py-3">
                    <strong>{{ $o->label }}</strong>
                    <p class="text-sm text-slate-500">{{ $o->details }}</p>
                </div>
            @empty
                <p class="text-slate-500">No options provided.</p>
            @endforelse
        </section>
        <section class="bg-white rounded-2xl border p-5">
            <h2 class="font-bold text-[#0F2A52] mb-3">Supporting files</h2>
            @forelse($files as $f)
                <div class="border-t py-3">
                    <a class="text-teal-700 underline" href="{{ route('client-control.admin.decisions.files.download', [$decision->id, $f->id]) }}">{{ $f->original_name }}</a>
                </div>
            @empty
                <p class="text-slate-500">No files attached.</p>
            @endforelse
        </section>
    </div>

    <section class="bg-white rounded-2xl border p-5">
        <h2 class="font-bold text-[#0F2A52] mb-3">Workflow actions</h2>
        <div class="flex flex-wrap gap-2">
            @php
                $decisionActions = [
                    'submit' => ['Draft', 'update'],
                    'approve' => ['Submitted', 'review'],
                    'publish' => ['Approved', 'publish'],
                    'withdraw' => ['Published|Responded|Acknowledged', 'withdraw'],
                    'close' => ['Acknowledged', 'close'],
                ];
            @endphp
            @foreach($decisionActions as $action => $config)
                @if(in_array($decision->status, explode('|', $config[0]), true) && auth()->user()?->hasPermission('client_control.decisions.'.$config[1]))
                    <form method="POST" action="{{ route('client-control.admin.decisions.transition', [$decision->id, $action]) }}" onsubmit="return confirm('Confirm {{ $action }}?')">
                        @csrf
                        <button type="submit" class="rounded-xl bg-[#0F2A52] text-white px-4 py-2 capitalize">{{ $action }}</button>
                    </form>
                @endif
            @endforeach
        </div>
    </section>

    <section class="bg-white rounded-2xl border p-5">
        <h2 class="font-bold text-[#0F2A52] mb-3">Clarification conversation</h2>
        @forelse($clarifications as $c)
            <div class="border-t py-4 space-y-2">
                <div class="text-sm font-semibold">Client question · {{ $c->requested_at }} · Client #{{ $c->client_user_id }}</div>
                <div class="whitespace-pre-wrap text-slate-700">{{ $c->question }}</div>
                @if($c->replied_at)
                    <div class="bg-teal-50 rounded-xl p-3"><strong>Ravion Homes replied · {{ $c->replied_at }}</strong><p class="whitespace-pre-wrap">{{ $c->reply }}</p></div>
                @elseif($decision->status === 'Published' && auth()->user()?->hasPermission('client_control.decisions.update'))
                    <form method="POST" action="{{ route('client-control.admin.decisions.clarifications.reply', [$decision->id, $c->id]) }}" class="space-y-2">
                        @csrf
                        <label for="reply-{{ $c->id }}" class="font-semibold text-sm">PMO/Admin reply</label>
                        <textarea id="reply-{{ $c->id }}" name="reply" required maxlength="10000" rows="4" class="w-full rounded-xl border p-3">{{ old('reply') }}</textarea>
                        @error('reply')<p class="text-red-700 text-sm">{{ $message }}</p>@enderror
                        <button type="submit" class="rounded-xl bg-teal-700 text-white px-4 py-2" onclick="return confirm('Send this clarification reply?')">Send clarification reply</button>
                    </form>
                @else
                    <p class="text-amber-700">Awaiting PMO/Admin reply.</p>
                @endif
            </div>
        @empty
            <p class="text-slate-500">No clarification requests.</p>
        @endforelse
    </section>

    <section class="bg-white rounded-2xl border p-5">
        <h2 class="font-bold text-[#0F2A52] mb-3">Client responses</h2>
        @forelse($responses as $r)
            <div class="border-t py-4">
                <div class="font-semibold">{{ $r->response }} · {{ $r->responded_at }} · Client #{{ $r->client_user_id }}</div>
                @if($r->selected_option_label)<p class="text-teal-800 font-semibold">Selected option: {{ $r->selected_option_label }}</p>@endif
                <p class="text-slate-600 whitespace-pre-wrap">{{ $r->comments }}</p>
                @if(in_array($r->response, ['Approve', 'Reject'], true) && !$r->acknowledged_at && $decision->status === 'Responded' && auth()->user()?->hasPermission('client_control.decisions.acknowledge'))
                    <form class="mt-2" method="POST" action="{{ route('client-control.admin.decisions.acknowledge', [$decision->id, $r->id]) }}">
                        @csrf
                        <button type="submit" class="bg-teal-700 text-white px-4 py-2 rounded-xl">Acknowledge</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-slate-500">No client responses yet. Client response portal will be added in the next phase.</p>
        @endforelse
    </section>

    <section class="bg-white rounded-2xl border p-5">
        <h2 class="font-bold text-[#0F2A52] mb-3">Audit timeline</h2>
        @foreach($events as $e)
            <div class="border-t py-3 text-sm">
                <span class="font-semibold">{{ ucfirst($e->action) }}</span> · {{ $e->created_at }}
                <span class="text-slate-500">({{ $e->actor_guard }} #{{ $e->actor_id }})</span>
            </div>
        @endforeach
    </section>
</div>
@endsection
