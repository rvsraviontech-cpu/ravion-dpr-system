@extends('layouts.app')
@section('content')
<style>.vd{max-width:1080px;margin:25px auto;padding:0 20px;color:#17334d}.vd-head{background:linear-gradient(110deg,#173c67,#087f8c);color:#fff;padding:26px;border-radius:18px}.vd-card{background:white;border:1px solid #dce7f1;border-radius:15px;padding:22px;margin:17px 0}.vd-btn{background:#087f8c;color:white;border:0;border-radius:8px;padding:10px 15px;font-weight:700;cursor:pointer}.vd-btn.danger{background:#a33b26}.vd-table{width:100%;border-collapse:collapse}.vd-table td,.vd-table th{border-bottom:1px solid #e7edf4;padding:12px;text-align:left;font-size:13px}.vd-form{display:inline-block;margin:5px}.vd-form input{padding:9px;border:1px solid #d1dfeb;border-radius:8px}.vd a{color:#087f8c;font-weight:700}.vd-head a{color:white}.vd-info{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px}.vd-info span{display:block;color:#71869a;font-size:11px}.vd-alert{padding:12px;background:#e6f7ee;border-radius:10px;margin:14px 0}.vd-error{background:#fff1ed;color:#a33b26}</style>
<div class="vd"><div class="vd-head"><a href="{{route('client-control.admin.vault.index')}}">← Document Vault</a><h1 style="margin:12px 0 4px;font-size:26px">{{$doc->title}}</h1><div>{{$doc->document_number}} · {{$doc->project_name}}</div></div>

@if(session('success'))<div class="vd-alert">{{session('success')}}</div>
@endif

@if($errors->any())<div class="vd-alert vd-error">
@foreach($errors->all() as $error)<div>{{$error}}</div>
@endforeach
</div>
@endif
@php $v=$versions->firstWhere('id',$doc->current_version_id);$roleId=auth()->user()?->role_id;$can=function($name)use($roleId){return \Illuminate\Support\Facades\DB::table('permission_role as pr')->join('permissions as p','p.id','=','pr.permission_id')->where('pr.role_id',$roleId)->where('p.name',$name)->where('p.is_active',1)->exists();}; @endphp
<div class="vd-card"><div class="vd-info"><div><span>Project</span><strong>{{$doc->project_name}}</strong></div><div><span>Category</span><strong>{{$doc->category_name}}</strong></div><div><span>Document status</span><strong>{{$doc->status}}</strong></div><div><span>Current revision</span><strong>{{$v?->revision ?? '—'}} ({{$v?->status ?? '—'}})</strong></div><div><span>Published version</span><strong>{{$doc->published_version_id ?: 'None'}} {{$doc->withdrawn_at?'(withdrawn)':''}}</strong></div><div><span>Client visibility</span><strong>{{$doc->visibility_role}}</strong></div></div>
@if($doc->description)<p>{{$doc->description}}</p>
@endif
</div>
<div class="vd-card"><h2>Current revision workflow</h2><p>Each action is recorded in the audit log. A new revision never automatically replaces the published file.</p>

@if($v)

@if($v->status==='Draft' && $can('client_control.documents.upload'))<form class="vd-form" method="POST" action="{{route('client-control.admin.vault.transition',[$doc->id,'submit'])}}">@csrf<button class="vd-btn">Submit for verification</button></form>
@endif

@if($v->status==='Submitted' && $can('client_control.documents.verify'))<form class="vd-form" method="POST" action="{{route('client-control.admin.vault.transition',[$doc->id,'verify'])}}">@csrf<button class="vd-btn">Verify</button></form><form class="vd-form" method="POST" action="{{route('client-control.admin.vault.transition',[$doc->id,'reject'])}}">@csrf<input name="notes" placeholder="Reason for rejection"><button class="vd-btn danger">Reject</button></form>
@endif

@if($v->status==='Verified' && $can('client_control.documents.approve'))<form class="vd-form" method="POST" action="{{route('client-control.admin.vault.transition',[$doc->id,'approve'])}}">@csrf<button class="vd-btn">Approve</button></form>
@endif

@if($v->status==='Approved' && $can('client_control.documents.publish'))<form class="vd-form" method="POST" action="{{route('client-control.admin.vault.transition',[$doc->id,'publish'])}}">@csrf<button class="vd-btn">Publish to client</button></form>
@endif

@endif

@if($doc->published_version_id && !$doc->withdrawn_at && $can('client_control.documents.withdraw'))<form method="POST" style="margin-top:15px" action="{{route('client-control.admin.vault.withdraw',$doc->id)}}">@csrf<input name="reason" required maxlength="2000" placeholder="Mandatory withdrawal reason" style="padding:10px;width:min(100%,400px);border:1px solid #d1dfeb;border-radius:8px"><button class="vd-btn danger">Withdraw publication</button></form>
@endif

@if($v && in_array($v->status,['Published','Withdrawn','Rejected']) && $can('client_control.documents.upload'))<form method="POST" enctype="multipart/form-data" style="margin-top:18px" action="{{route('client-control.admin.vault.revise',$doc->id)}}">@csrf<label><strong>Upload new revision</strong> <small>(max 20 MB)</small><input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp"></label><button class="vd-btn">Upload draft revision</button></form>
@endif
</div>
<div class="vd-card"><h2>File revision history</h2><div style="overflow-x:auto"><table class="vd-table"><thead><tr><th>Revision</th><th>File</th><th>Status</th><th>Uploaded</th><th>Download</th></tr></thead><tbody>
@foreach($versions as $ver)<tr><td>R{{$ver->revision}}</td><td>{{$ver->original_filename}}</td><td>{{$ver->status}}</td><td>{{$ver->created_at}}</td><td><a href="{{route('client-control.admin.vault.download',[$doc->id,$ver->id])}}">Download</a></td></tr>
@endforeach
</tbody></table></div></div>

@if($can('client_control.documents.audit'))<div class="vd-card"><h2>Audit history</h2><div style="overflow-x:auto"><table class="vd-table"><thead><tr><th>Date</th><th>Action</th><th>Actor ID</th><th>Details</th></tr></thead><tbody>
@forelse($events as $event)<tr><td>{{$event->created_at}}</td><td>{{$event->action}}</td><td>{{$event->actor_id}}</td><td><small>{{Str::limit($event->metadata,140)}}</small></td></tr>
@empty
<tr><td colspan="4">No events yet.</td></tr>
@endforelse
</tbody></table></div></div>
@endif
</div>
@endsection
