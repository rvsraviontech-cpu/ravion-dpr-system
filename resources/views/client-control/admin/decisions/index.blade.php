@extends('layouts.app')
@section('content')
@php
    $projectFilter = request('project_id');
    $statusFilter = request('status');
    $attentionFilter = request('attention');
    $baseFilters = array_filter(['project_id' => $projectFilter], fn ($value) => $value !== null && $value !== '');
    $cards = [
        ['label' => 'Awaiting client response', 'count' => $summary['awaiting_client'] ?? 0, 'caption' => 'Published · ready for client', 'accent' => 'text-blue-800'],
        ['label' => 'Awaiting PMO reply', 'count' => $summary['awaiting_pmo'] ?? 0, 'caption' => 'Unanswered clarification', 'accent' => 'text-amber-700'],
        ['label' => 'Responded / acknowledged', 'count' => $summary['responded'] ?? 0, 'caption' => 'Client has made a decision', 'accent' => 'text-teal-700'],
        ['label' => 'Closed', 'count' => $summary['closed'] ?? 0, 'caption' => 'Completed decisions', 'accent' => 'text-slate-700'],
        ['label' => 'Overdue', 'count' => $summary['overdue'] ?? 0, 'caption' => 'Published · client reply overdue', 'accent' => 'text-rose-700'],
    ];
@endphp
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-wrap justify-between gap-4 items-center">
        <div>
            <p class="text-sm font-semibold text-teal-700">CLIENT CONTROL</p>
            <h1 class="text-3xl font-bold text-[#0F2A52]">My Decisions · Register</h1>
            <p class="text-slate-500 mt-1">Manage formal client approvals, clarifications and responses.</p>
        </div>
        @if(auth()->user()?->hasPermission('client_control.decisions.create'))
            <a href="{{ route('client-control.admin.decisions.create') }}" class="rounded-xl bg-[#0F2A52] text-white px-5 py-3 font-semibold">+ New decision</a>
        @endif
    </div>

    @if(session('success'))<div class="p-3 bg-teal-50 text-teal-800 rounded-xl">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="p-3 bg-amber-50 text-amber-800 rounded-xl">{{ session('warning') }}</div>@endif

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @foreach($cards as $card)
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <p class="text-xs sm:text-sm font-semibold text-slate-600">{{ $card['label'] }}</p>
                <p class="text-3xl font-bold mt-2 {{ $card['accent'] }}">{{ number_format($card['count']) }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $card['caption'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-4">
        <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label for="decision-project-filter" class="block text-xs font-semibold text-slate-600 mb-1">Project</label>
                <select id="decision-project-filter" name="project_id" class="w-full rounded-xl border-slate-300">
                    <option value="">All projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected((string)$projectFilter === (string)$p->id)>{{ $p->project_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="decision-status-filter" class="block text-xs font-semibold text-slate-600 mb-1">Workflow status</label>
                <select id="decision-status-filter" name="status" class="w-full rounded-xl border-slate-300">
                    <option value="">All statuses</option>
                    @foreach(['Draft','Submitted','Approved','Published','Responded','Acknowledged','Closed','Withdrawn'] as $s)
                        <option value="{{ $s }}" @selected($statusFilter === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="decision-attention-filter" class="block text-xs font-semibold text-slate-600 mb-1">Requires attention</label>
                <select id="decision-attention-filter" name="attention" class="w-full rounded-xl border-slate-300">
                    <option value="">All decisions</option>
                    <option value="clarifications" @selected($attentionFilter === 'clarifications')>Awaiting PMO reply</option>
                    <option value="overdue" @selected($attentionFilter === 'overdue')>Overdue client responses</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-teal-700 text-white font-semibold px-4 py-2.5">Apply filters</button>
                <a href="{{ route('client-control.admin.decisions.index') }}" class="rounded-xl border border-slate-300 text-slate-700 px-4 py-2.5 font-semibold">Reset</a>
            </div>
        </form>
        <div class="flex flex-wrap gap-2 items-center text-sm">
            <span class="text-slate-500 mr-1">Quick filters:</span>
            <a href="{{ route('client-control.admin.decisions.index', $baseFilters) }}" class="rounded-full px-3 py-1.5 border {{ !$attentionFilter && !$statusFilter ? 'bg-[#0F2A52] text-white border-[#0F2A52]' : 'border-slate-300 text-slate-700' }}">All</a>
            <a href="{{ route('client-control.admin.decisions.index', array_merge($baseFilters, ['attention' => 'clarifications'])) }}" class="rounded-full px-3 py-1.5 border {{ $attentionFilter === 'clarifications' ? 'bg-amber-100 border-amber-300 text-amber-900' : 'border-slate-300 text-slate-700' }}">Awaiting PMO reply ({{ $summary['awaiting_pmo'] ?? 0 }})</a>
            <a href="{{ route('client-control.admin.decisions.index', array_merge($baseFilters, ['attention' => 'overdue'])) }}" class="rounded-full px-3 py-1.5 border {{ $attentionFilter === 'overdue' ? 'bg-rose-100 border-rose-300 text-rose-900' : 'border-slate-300 text-slate-700' }}">Overdue ({{ $summary['overdue'] ?? 0 }})</a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="p-4 text-left">Reference / Subject</th>
                    <th class="p-4 text-left">Project</th>
                    <th class="p-4 text-left">Due</th>
                    <th class="p-4 text-left">Workflow / Attention</th>
                    <th class="p-4 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($decisions as $d)
                    @php
                        $needsPmo = (bool) ($d->awaiting_pmo_reply ?? false);
                        $overdue = $d->status === 'Published' && !$needsPmo && $d->response_due_at && \Illuminate\Support\Carbon::parse($d->response_due_at)->startOfDay()->lt(today());
                    @endphp
                    <tr class="border-t border-slate-100 hover:bg-slate-50/70">
                        <td class="p-4">
                            <div class="font-semibold text-[#0F2A52]">{{ $d->title }}</div>
                            <div class="text-slate-500 text-xs mt-1">{{ $d->reference }}</div>
                        </td>
                        <td class="p-4 text-slate-700">{{ $d->project_name }}</td>
                        <td class="p-4 text-slate-700 whitespace-nowrap">{{ $d->response_due_at ? \Illuminate\Support\Carbon::parse($d->response_due_at)->format('d M Y') : '—' }}</td>
                        <td class="p-4">
                            <span class="inline-flex rounded-full bg-slate-100 text-slate-700 px-3 py-1 text-xs font-semibold">{{ $d->status }}</span>
                            @if($needsPmo)
                                <span class="inline-flex rounded-full bg-amber-100 text-amber-900 px-3 py-1 text-xs font-semibold mt-1">Awaiting PMO reply</span>
                            @elseif($overdue)
                                <span class="inline-flex rounded-full bg-rose-100 text-rose-800 px-3 py-1 text-xs font-semibold mt-1">Overdue</span>
                            @endif
                        </td>
                        <td class="p-4"><a class="font-semibold text-teal-700 hover:underline" href="{{ route('client-control.admin.decisions.show', $d->id) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-slate-500">No decisions match the selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $decisions->links() }}
</div>
@endsection
