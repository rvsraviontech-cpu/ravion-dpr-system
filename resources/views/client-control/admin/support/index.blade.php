@extends('layouts.app')
@section('content')
<div class="p-6 space-y-4">
<h1 class="text-2xl font-bold">Client Support Desk</h1>
@if(auth()->user()->hasPermission('client_control.support.export'))
<div class="flex flex-wrap justify-end gap-2">
@foreach(['pdf'=>'PDF','xlsx'=>'Excel'] as $format=>$label)
<a class="border bg-white rounded px-4 py-2" href="{{ route('client-control.admin.support.tickets.export',array_merge(request()->only(['project_id','category_id','status','priority','search']),['format'=>$format])) }}">Export {{ $label }}</a>
@endforeach
</div>
@endif

<a class="text-teal-700" href="{{ route('client-control.admin.support.tickets.create') }}">+ Create ticket</a>
<div class="grid grid-cols-2 md:grid-cols-6 gap-3">@foreach(['Open','In Progress','Awaiting Client','Resolved','Closed'] as $s)<div class="bg-white border rounded p-4">{{ $s }}<div class="text-2xl font-bold">{{ $counts[$s] ?? 0 }}</div></div>@endforeach</div>
<form method="GET" class="flex flex-wrap gap-2"><select name="project_id"><option value="">All projects</option>@foreach($projects as $p)<option value="{{ $p->id }}" @selected(request('project_id')==$p->id)>{{ $p->project_name }}</option>@endforeach</select><select name="category_id"><option value="">All categories</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>@endforeach</select>@foreach(['status'=>['Open','In Progress','Awaiting Client','Resolved','Closed'],'priority'=>['Normal','High','Urgent']] as $field=>$options)<select name="{{ $field }}"><option value="">All {{ $field }}</option>@foreach($options as $o)<option @selected(request($field)===$o)>{{ $o }}</option>@endforeach</select>@endforeach<input name="search" value="{{ request('search') }}" placeholder="Ticket number or subject"><button class="bg-teal-700 text-white rounded px-4">Filter</button></form>
<div class="bg-white border rounded overflow-x-auto"><table class="w-full text-sm"><thead><tr>@foreach(['Ticket','Project','Subject','Category','Priority','Status','Assigned to'] as $h)<th class="p-3 text-left">{{ $h }}</th>@endforeach</tr></thead><tbody>@forelse($tickets as $t)<tr class="border-t"> <td class="p-3"><a class="text-teal-700" href="{{ route('client-control.admin.support.tickets.show',$t->id) }}">{{ $t->ticket_number }}</a></td>@foreach([$t->project_name,$t->subject,$t->category,$t->priority,$t->status,$t->assignee ?? 'Unassigned'] as $v)<td class="p-3">{{ $v }}</td>@endforeach</tr>@empty<tr><td colspan="7" class="p-8 text-center">No support tickets found.</td></tr>@endforelse</tbody></table></div>{{ $tickets->links() }}
</div>
@endsection
