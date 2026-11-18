@extends('layouts.app')
@section('content')
<style>.vault-wrap{max-width:1380px;margin:25px auto;padding:0 20px;color:#17334d}.vault-hero{padding:27px 32px;border-radius:20px;background:linear-gradient(110deg,#173c67,#087f8c);color:white;margin-bottom:20px}.vault-hero h1{font-size:27px;font-weight:800;margin:0 0 5px}.vault-panel{background:white;border:1px solid #dfe8f1;border-radius:17px;padding:22px;margin-bottom:18px;box-shadow:0 5px 20px #173c6710}.vault-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.vault-grid label{font-size:12px;font-weight:700;color:#425e77}.vault-grid input,.vault-grid select,.vault-grid textarea,.vault-filter input,.vault-filter select{width:100%;border:1px solid #d2dfeb;border-radius:9px;padding:10px;margin-top:5px;background:white}.vault-grid .wide{grid-column:span 3}.vault-btn{border:0;border-radius:9px;background:#087f8c;color:white;padding:10px 16px;font-weight:700;cursor:pointer}.vault-table{width:100%;border-collapse:collapse}.vault-table th,.vault-table td{text-align:left;padding:13px 10px;border-bottom:1px solid #e8eef4;font-size:13px}.vault-table th{color:#57728b;font-size:11px;text-transform:uppercase}.vault-table a{color:#087f8c;font-weight:750}.vault-filter{display:flex;gap:10px;flex-wrap:wrap;align-items:end}.vault-filter>*{flex:1;min-width:130px}.vault-alert{padding:12px 15px;border-radius:9px;margin-bottom:15px;background:#e6f7ee;color:#176844}.vault-errors{background:#fff1ed;color:#a33b26}@media(max-width:700px){.vault-grid{grid-template-columns:1fr}.vault-grid .wide{grid-column:auto}.vault-table{display:block;overflow-x:auto}.vault-hero{padding:20px}}</style>
<div class="vault-wrap"><div class="vault-hero"><div style="font-size:11px;letter-spacing:2px;opacity:.8">RAVION / CLIENT CONTROL</div><h1>Document Vault</h1><div>Project documents · controlled revisions · secure publication</div></div>
@if(session('success'))<div class="vault-alert">{{session('success')}}</div>@endif
@if($errors->any())<div class="vault-alert vault-errors"><strong>Please review:</strong><ul>@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
@php $vaultRole=auth()->user()?->role_id; $vaultHas=function($name)use($vaultRole){return \Illuminate\Support\Facades\DB::table('permission_role as pr')->join('permissions as p','p.id','=','pr.permission_id')->where('pr.role_id',$vaultRole)->where('p.name',$name)->where('p.is_active',1)->exists();}; @endphp
@if($vaultHas('client_control.documents.upload'))<section class="vault-panel"><h2 style="font-size:18px;margin:0 0 17px">Upload a new document</h2><form method="POST" enctype="multipart/form-data" action="{{route('client-control.admin.vault.store')}}">@csrf<div class="vault-grid"><label>Project *<select name="project_id" required><option value="">Select project</option>@foreach($projects as $p)<option value="{{$p->id}}" @selected(old('project_id')==$p->id)>{{$p->project_name}} ({{$p->project_code}})</option>@endforeach</select></label><label>Category *<select name="category_id" id="vault-category" required><option value="">Select category</option>@foreach($categories as $c)<option value="{{$c->id}}" @selected(old('category_id')==$c->id)>{{$c->name}}</option>@endforeach</select></label><label>Document number<div style="margin-top:5px;padding:11px 12px;border:1px solid #d2dfeb;border-radius:9px;background:#f4f8fb;color:#48647b;font-weight:600">RH-DOC-###### · Auto-assigned on save</div></label><label>Document title *
<select name="document_type_id" id="vault-document-type" required>
<option value="">Select document title</option>
@foreach($documentTypes as $type)
<option value="{{ $type->id }}" data-category="{{ $type->category_id }}" data-custom="{{ $type->is_custom ? '1' : '0' }}" @selected(old('document_type_id') == $type->id)>{{ $type->is_custom ? 'Other / Custom Document' : $type->title }}</option>
@endforeach
</select>
</label><label id="vault-custom-title-wrap" style="display:none">Custom document title *<input name="custom_title" id="vault-custom-title" value="{{old('custom_title')}}" maxlength="255" placeholder="Enter the actual document name"></label><label>Client visibility *<select name="visibility_role" required>@foreach(['All','Owner','Owner/Decision Maker','Technical Viewer','Finance Viewer','Read-only Viewer'] as $role)<option @selected(old('visibility_role','All')===$role)>{{$role}}</option>@endforeach</select></label><label>File * (max 20 MB)<input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp" required></label><label class="wide">Description<textarea name="description" rows="2">{{old('description')}}</textarea></label></div><button class="vault-btn" style="margin-top:15px">Save as draft</button></form></section>@endif
<section class="vault-panel"><h2 style="font-size:18px;margin:0 0 15px">Document register</h2><form method="GET" class="vault-filter"><select name="project_id"><option value="">All projects</option>@foreach($projects as $p)<option value="{{$p->id}}" @selected(request('project_id')==$p->id)>{{$p->project_name}}</option>@endforeach</select><select name="category_id"><option value="">All categories</option>@foreach($categories as $c)<option value="{{$c->id}}" @selected(request('category_id')==$c->id)>{{$c->name}}</option>@endforeach</select><input name="q" value="{{request('q')}}" placeholder="Search title or number"><button class="vault-btn">Filter</button></form><div style="overflow-x:auto;margin-top:18px"><table class="vault-table"><thead><tr><th>Document</th><th>Project</th><th>Category</th><th>Status</th><th>Published</th><th></th></tr></thead><tbody>@forelse($documents as $d)<tr><td><strong>{{$d->document_number}}</strong><div>{{$d->title}}</div></td><td>{{$d->project_name}}</td><td>{{$d->category_name}}</td><td>{{$d->status}}</td><td>{{$d->published_version_id ? ($d->withdrawn_at ? 'Withdrawn' : 'Available') : 'Not published'}}</td><td><a href="{{route('client-control.admin.vault.show',$d->id)}}">Manage →</a></td></tr>@empty<tr><td colspan="6">No documents found.</td></tr>@endforelse</tbody></table></div><div style="margin-top:16px">{{$documents->links()}}</div></section></div>

<script>
(function(){
const category=document.getElementById('vault-category');
const title=document.getElementById('vault-document-type');
const customWrap=document.getElementById('vault-custom-title-wrap');
const custom=document.getElementById('vault-custom-title');
if(!category||!title)return;
function sync(reset){
  const categoryId=category.value;
  Array.from(title.options).forEach(function(option){
    if(!option.value)return;
    option.hidden=option.dataset.category!==categoryId;
    option.disabled=option.hidden;
  });
  if(reset||!title.selectedOptions.length||title.selectedOptions[0].disabled)title.value='';
  const chosen=title.selectedOptions[0];
  const isCustom=!!(chosen&&chosen.dataset.custom==='1');
  customWrap.style.display=isCustom?'':'none';
  custom.required=isCustom;
  if(!isCustom)custom.value='';
}
category.addEventListener('change',function(){sync(true)});
title.addEventListener('change',function(){sync(false)});
sync(false);
})();
</script>
@endsection
