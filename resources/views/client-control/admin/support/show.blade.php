@extends('layouts.app')
@section('content')
<div class="p-6 space-y-4">
<a href="{{ route('client-control.admin.support.tickets.index') }}">Back to Support Desk</a>
@if(auth()->user()->hasPermission('client_control.support.export'))
<div class="flex flex-wrap justify-end gap-2">
@foreach(['pdf'=>'PDF','xlsx'=>'Excel'] as $format=>$label)
<a class="border bg-white rounded px-4 py-2" href="{{ route('client-control.admin.support.tickets.export-ticket',['ticket'=>$ticket->id,'format'=>$format]) }}">Export {{ $label }}</a>
@endforeach
</div>
@endif
<h1 class="text-2xl font-bold">{{ $ticket->ticket_number }} — {{ $ticket->subject }}</h1><div class="bg-white border rounded p-4">{{ $ticket->project_name }} · {{ $ticket->category }} · {{ $ticket->request_type }} · {{ $ticket->priority }} · {{ $ticket->status }}<p>Assigned to: {{ $ticket->assignee ?? 'Unassigned' }}</p></div>
@if($errors->any())<div class="card" role="alert">
@foreach($errors->all() as $error)<p>{{ $error }}</p>
@endforeach</div>
@endif
@if(session('success'))<p>{{ session('success') }}</p>
@endif<div class="bg-white border rounded p-4 space-y-3"><h2 class="font-bold">Ticket management</h2>
@if($ticket->expected_resolution_date)<p>Expected resolution: {{ $ticket->expected_resolution_date }} @if(!in_array($ticket->status,['Resolved','Closed']) && $ticket->expected_resolution_date < now()->toDateString()) <strong class="text-red-700">Overdue</strong>
@endif</p> @endif
@if(auth()->user()->hasPermission('client_control.support.update') && !in_array($ticket->status,['Resolved','Closed']))
<form method="POST" action="{{ route('client-control.admin.support.tickets.manage',$ticket->id) }}" class="space-y-2">
@csrf<input type="hidden" name="action" value="update"><label>Subject<input class="w-full border rounded p-2" name="subject" value="{{ $ticket->subject }}" required maxlength="255"></label><div class="grid grid-cols-1 md:grid-cols-2 gap-3"><label>Priority<select class="w-full border rounded p-2" name="priority">
@foreach(['Normal','High','Urgent'] as $priority)<option
@selected($ticket->priority===$priority)>{{ $priority }}</option>
@endforeach</select></label><label>Expected resolution date<input class="w-full border rounded p-2" type="date" name="expected_resolution_date" value="{{ $ticket->expected_resolution_date }}"></label></div><button class="bg-teal-700 text-white rounded px-4 py-2">Save details</button></form> @endif
@php $actions = in_array($ticket->status,['Resolved','Closed']) ? ($ticket->status==='Resolved'?['close'=>['close','Close ticket'],'reopen'=>['reopen','Reopen ticket']]:['reopen'=>['reopen','Reopen ticket']]) : ['progress'=>['update','In Progress'],'await'=>['update','Awaiting Client'],'resolve'=>['resolve','Resolve ticket']];
@endphp
@php
    $allowedActions = array_filter($actions, fn($spec) => auth()->user()->hasPermission('client_control.support.'.$spec[0]));
    @endphp
@if(count($allowedActions))
<form method="POST" action="{{ route('client-control.admin.support.tickets.manage',$ticket->id) }}" class="border-t pt-3 space-y-3" id="support-status-form">
    @csrf
    <h3 class="font-bold">Update ticket status</h3>
    <label class="block">Action
        <select name="action" id="support-status-action" class="w-full border rounded p-2" required>
            <option value="">Select an action</option>
            @foreach($allowedActions as $action => $spec)
                <option value="{{ $action }}">{{ $spec[1] }}</option>
            @endforeach
        </select>
    </label>
    <label class="block" for="support-status-remarks" id="support-remarks-label">Internal remarks (optional)</label>
    <textarea id="support-status-remarks" class="w-full border rounded p-2" name="remarks" rows="2" maxlength="5000"></textarea>
    <p id="support-remarks-help" class="text-sm text-gray-600">Internal remarks are not visible to the client.</p>
    <button id="support-status-submit" class="bg-teal-700 text-white rounded px-4 py-2" disabled>Update status</button>
</form>
<script>
(function () {
    const action = document.getElementById('support-status-action');
    const remarks = document.getElementById('support-status-remarks');
    const label = document.getElementById('support-remarks-label');
    const help = document.getElementById('support-remarks-help');
    const button = document.getElementById('support-status-submit');
    function sync() {
        const value = action.value;
        const required = ['resolve', 'close', 'reopen'].includes(value);
        remarks.required = required;
        label.textContent = value === 'resolve' ? 'Resolution remarks (visible to client)' : 'Internal remarks (' + (required ? 'required' : 'optional') + ')';
        help.textContent = value === 'resolve' ? 'These remarks will be visible to the client.' : 'Internal remarks are not visible to the client.';
        button.disabled = !value;
        button.textContent = ({resolve:'Resolve ticket',close:'Close ticket',reopen:'Reopen ticket'})[value] || 'Update status';
    }
    action.addEventListener('change', function () { remarks.value = ''; sync(); });
    sync();
})();
</script>
@endif
@if(in_array(auth()->user()->role?->name,['Admin','CEO']) && auth()->user()->hasPermission('client_control.support.assign'))<details class="border-t pt-3"><summary class="cursor-pointer text-sm">Assignment options</summary><form method="POST" action="{{ route('client-control.admin.support.tickets.manage',$ticket->id) }}" class="mt-2">
@csrf
<input type="hidden" name="action" value="assign"><button class="border rounded px-4 py-2">Assign to current project PMO</button></form></details>
@endif
</div><h2 class="font-bold">Conversation</h2>
@foreach($messages as $m)<div class="bg-white border rounded p-4"><small>{{ $m->author ?? 'Client' }} · {{ $m->visibility }} · {{ $m->created_at }}</small><p class="whitespace-pre-wrap">{{ $m->body }}</p>
@foreach($attachments->get($m->id,[]) as $a)
@if(auth()->user()->hasPermission('client_control.support.download'))<p><a href="{{ route('client-control.admin.support.tickets.attachment',[$ticket->id,$a->id]) }}">{{ $a->original_filename }}</a></p>
@endif
@endforeach</div>
@endforeach<div class="bg-white border rounded p-4"><h2 class="font-bold">Add message</h2><form method="POST" enctype="multipart/form-data" action="{{ route('client-control.admin.support.tickets.reply',$ticket->id) }}" class="space-y-3">
@csrf<label>Visibility<select name="visibility" required>
@if(auth()->user()->hasPermission('client_control.support.reply') && !in_array($ticket->status,['Resolved','Closed']))<option value="Public">Reply to client</option>
@endif
@if(auth()->user()->hasPermission('client_control.support.internal_notes'))<option value="Internal">Confidential internal note</option>
@endif</select></label><textarea class="w-full border rounded p-2" name="body" rows="4" required maxlength="20000">{{ old('body') }}</textarea><label>Attachments (optional, up to 5 PDF/JPG/PNG files, 10 MB each)<input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png"></label><button class="bg-teal-700 text-white rounded px-4 py-2">Save message</button></form></div><h2 class="font-bold">Activity history</h2>
@foreach($events as $e)<div class="border rounded p-3">{{ $e->action }} · {{ $e->created_at }} · ERP user #{{ $e->user_id }}
    @if($e->details)<pre class="whitespace-pre-wrap break-all text-xs">{{ $e->details }}</pre>
@endif</div>
@endforeach
</div>
@endsection
