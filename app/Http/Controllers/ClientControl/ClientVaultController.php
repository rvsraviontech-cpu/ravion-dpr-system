<?php
namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientVaultController extends Controller
{
    private const ROLES = ['All', 'Owner', 'Owner/Decision Maker', 'Technical Viewer', 'Finance Viewer', 'Read-only Viewer'];

    private function audit(Request $request, string $action, int $documentId, int $projectId, array $meta = []): void
    {
        DB::table('client_audit_events')->insert([
            'project_id' => $projectId, 'actor_type' => 'ERP User', 'actor_id' => $request->user()->id,
            'action' => 'vault.'.$action, 'subject_type' => 'ClientVaultDocument', 'subject_id' => $documentId,
            'metadata' => json_encode($meta), 'ip_address' => $request->ip(), 'created_at' => now(),
        ]);
    }

    public function index(Request $request)
    {
        $this->checkPermission($request,'client_control.documents.view');
        $projects = DB::table('projects')->select('id','project_name','project_code')->orderBy('project_name')->get();
        $categories = DB::table('client_vault_categories')->where('is_active',1)->orderBy('sort_order')->get();
        $documentTypes = DB::table('client_vault_document_types')->where('is_active',1)->orderBy('sort_order')->orderBy('title')->get();
        $documents = DB::table('client_vault_documents as d')
            ->join('projects as p','p.id','=','d.project_id')
            ->join('client_vault_categories as c','c.id','=','d.category_id')
            ->select('d.*','p.project_name','c.name as category_name')
            ->when($request->filled('project_id'),fn($q)=>$q->where('d.project_id',$request->integer('project_id')))
            ->when($request->filled('category_id'),fn($q)=>$q->where('d.category_id',$request->integer('category_id')))
            ->when($request->filled('q'),fn($q)=>$q->where(fn($w)=>$w->where('d.title','like','%'.$request->input('q').'%')->orWhere('d.document_number','like','%'.$request->input('q').'%')))
            ->orderByDesc('d.id')->paginate(20)->withQueryString();
        return view('client-control.admin.vault.index',compact('projects','categories','documents','documentTypes'));
    }

    public function store(Request $request)
    {
        $this->checkPermission($request,'client_control.documents.upload');
        $data = $request->validate([
            'project_id'=>['required','integer','exists:projects,id'],
            'category_id'=>['required','integer',Rule::exists('client_vault_categories','id')->where('is_active',1)],
            'document_type_id'=>['required',Rule::exists('client_vault_document_types','id')->where('is_active',1)->where('category_id',$request->input('category_id'))],
            'custom_title'=>['nullable','string','max:255'],
            'description'=>['nullable','string','max:5000'], 'visibility_role'=>['required',Rule::in(self::ROLES)],
            'file'=>['required','file','max:20480','mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp'],
        ]);
        $type=DB::table('client_vault_document_types')->where('id',$data['document_type_id'])->where('category_id',$data['category_id'])->where('is_active',1)->first();
        abort_unless($type,422,'Select a valid document type for this category.');
        if ($type->is_custom) {
            $request->validate(['custom_title'=>['required','string','max:255']]);
            $title=trim($data['custom_title']??'');
            abort_if($title==='',422,'Custom document title is required.');
        } else {
            $title=$type->title;
        }
        $file=$request->file('file'); $path=null;
        try {
            $path=$file->store('client-vault/'.$data['project_id'].'/'.Str::uuid(),'local');
            if (!$path) throw new \RuntimeException('File storage failed.');
            $hash=hash_file('sha256',$file->getRealPath());
            DB::transaction(function () use ($request,$data,$file,$path,$hash,$type,$title) {
                // Serialize numbering per project under a database row lock.
                // The counter is never decremented and issued numbers are never reused.
                DB::table('client_vault_document_sequences')->insertOrIgnore([
                    'project_id'=>$data['project_id'], 'last_number'=>0,
                    'created_at'=>now(), 'updated_at'=>now(),
                ]);
                $sequence=DB::table('client_vault_document_sequences')
                    ->where('project_id',$data['project_id'])->lockForUpdate()->first();
                if (!$sequence) throw new \RuntimeException('Unable to reserve document number.');
                $next=(int)$sequence->last_number+1;
                $documentNumber='RH-DOC-'.str_pad((string)$next,6,'0',STR_PAD_LEFT);
                DB::table('client_vault_document_sequences')->where('project_id',$data['project_id'])
                    ->update(['last_number'=>$next,'updated_at'=>now()]);
                $id=DB::table('client_vault_documents')->insertGetId([
                    'project_id'=>$data['project_id'],'category_id'=>$data['category_id'],
                    'document_number'=>$documentNumber,'title'=>$title,'document_type_id'=>$type->id,
                    'description'=>$data['description']??null,'visibility_role'=>$data['visibility_role'],
                    'status'=>'Draft','created_by'=>$request->user()->id,'updated_by'=>$request->user()->id,
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
                $version=DB::table('client_vault_document_versions')->insertGetId([
                    'document_id'=>$id,'revision'=>1,'original_filename'=>$file->getClientOriginalName(),
                    'disk'=>'local','storage_path'=>$path,'mime_type'=>$file->getMimeType()?:'application/octet-stream',
                    'file_size'=>$file->getSize(),'sha256'=>$hash,'status'=>'Draft',
                    'uploaded_by'=>$request->user()->id,'created_at'=>now(),'updated_at'=>now(),
                ]);
                DB::table('client_vault_documents')->where('id',$id)->update(['current_version_id'=>$version]);
                $this->audit($request,'created',$id,(int)$data['project_id'],['version'=>$version,'document_number'=>$documentNumber,'document_type_id'=>$type->id,'document_title'=>$title]);
            });
        } catch (\Throwable $e) {
            if($path) Storage::disk('local')->delete($path);
            throw $e;
        }
        return redirect()->route('client-control.admin.vault.index')->with('success','Document saved as draft. Its RH document number was assigned automatically.');
    }

    public function show(Request $request,int $document)
    {
        $this->checkPermission($request,'client_control.documents.view');
        $doc=DB::table('client_vault_documents as d')->join('projects as p','p.id','=','d.project_id')
            ->join('client_vault_categories as c','c.id','=','d.category_id')
            ->select('d.*','p.project_name','c.name as category_name')->where('d.id',$document)->first();
        abort_unless($doc,404);
        $versions=DB::table('client_vault_document_versions')->where('document_id',$document)->orderByDesc('revision')->get();
        $events=DB::table('client_audit_events')->where('subject_type','ClientVaultDocument')->where('subject_id',$document)->orderByDesc('id')->limit(100)->get();
        return view('client-control.admin.vault.show',compact('doc','versions','events'));
    }

    public function revise(Request $request,int $document)
    {
        $this->checkPermission($request,'client_control.documents.upload');
        $request->validate(['file'=>['required','file','max:20480','mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp']]);
        $file=$request->file('file'); $path=null;
        try {
            $path=$file->store('client-vault/revisions/'.Str::uuid(),'local');
            if(!$path) throw new \RuntimeException('File storage failed.');
            $hash=hash_file('sha256',$file->getRealPath());
            DB::transaction(function() use($request,$document,$file,$path,$hash){
                $doc=DB::table('client_vault_documents')->where('id',$document)->lockForUpdate()->first();
                abort_unless($doc,404);
                $current=DB::table('client_vault_document_versions')->where('id',$doc->current_version_id)->first();
                abort_unless($current && $current->document_id===$doc->id,409);
                abort_unless(in_array($current->status,['Published','Withdrawn','Rejected'],true),422,'Finish or reject the current draft before uploading a revision.');
                $next=(int)DB::table('client_vault_document_versions')->where('document_id',$document)->max('revision')+1;
                $id=DB::table('client_vault_document_versions')->insertGetId([
                    'document_id'=>$document,'revision'=>$next,'original_filename'=>$file->getClientOriginalName(),
                    'disk'=>'local','storage_path'=>$path,'mime_type'=>$file->getMimeType()?:'application/octet-stream',
                    'file_size'=>$file->getSize(),'sha256'=>$hash,'status'=>'Draft','uploaded_by'=>$request->user()->id,
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
                DB::table('client_vault_documents')->where('id',$document)->update([
                    'current_version_id'=>$id,'status'=>$doc->published_version_id && !$doc->withdrawn_at?'Published':'Draft',
                    'updated_by'=>$request->user()->id,'updated_at'=>now(),
                ]);
                $this->audit($request,'revision_uploaded',$document,(int)$doc->project_id,['revision'=>$next]);
            });
        } catch(\Throwable $e){if($path) Storage::disk('local')->delete($path);throw $e;}
        return back()->with('success','New draft revision uploaded.');
    }

    public function transition(Request $request,int $document,string $action)
    {
        $rules=['submit'=>['Draft','Submitted','client_control.documents.upload'],
            'verify'=>['Submitted','Verified','client_control.documents.verify'],
            'approve'=>['Verified','Approved','client_control.documents.approve'],
            'publish'=>['Approved','Published','client_control.documents.publish'],
            'reject'=>['Submitted','Rejected','client_control.documents.verify']];
        abort_unless(isset($rules[$action]),404);
        [$from,$to,$permission]=$rules[$action];
        $this->checkPermission($request,$permission);
        $data=$request->validate(['notes'=>['nullable','string','max:2000']]);
        DB::transaction(function()use($request,$document,$action,$from,$to,$data){
            $doc=DB::table('client_vault_documents')->where('id',$document)->lockForUpdate()->first();abort_unless($doc,404);
            $v=DB::table('client_vault_document_versions')->where('id',$doc->current_version_id)->lockForUpdate()->first();
            abort_unless($v && (int)$v->document_id===$document,409);
            abort_unless($v->status===$from,422,'Invalid document workflow transition.');
            $changes=['status'=>$to,'updated_at'=>now()];
            $column=['submit'=>'submitted','verify'=>'verified','approve'=>'approved','publish'=>'published'];
            if(isset($column[$action])){$changes[$column[$action].'_at']=now();$changes[$column[$action].'_by']=$request->user()->id;}
            if($action==='reject'){$changes['review_notes']=$data['notes']??'Rejected for revision';}
            DB::table('client_vault_document_versions')->where('id',$v->id)->update($changes);
            $docUpdate=['updated_at'=>now(),'updated_by'=>$request->user()->id];
            if($action==='publish'){$docUpdate['published_version_id']=$v->id;$docUpdate['withdrawn_at']=null;}
            $docUpdate['status']=($action==='publish'||($doc->published_version_id && !$doc->withdrawn_at))?'Published':$to;
            DB::table('client_vault_documents')->where('id',$document)->update($docUpdate);
            $this->audit($request,$action,$document,(int)$doc->project_id,['revision'=>$v->revision,'notes'=>$data['notes']??null]);
        });
        return back()->with('success',ucfirst($action).' completed.');
    }

    public function withdraw(Request $request,int $document)
    {
        $this->checkPermission($request,'client_control.documents.withdraw');
        $data=$request->validate(['reason'=>['required','string','max:2000']]);
        DB::transaction(function()use($request,$document,$data){
            $doc=DB::table('client_vault_documents')->where('id',$document)->lockForUpdate()->first();abort_unless($doc,404);
            abort_unless($doc->published_version_id && !$doc->withdrawn_at,422,'No active published version.');
            DB::table('client_vault_documents')->where('id',$document)->update(['status'=>'Withdrawn','withdrawn_at'=>now(),'updated_at'=>now(),'updated_by'=>$request->user()->id]);
            $this->audit($request,'withdrawn',$document,(int)$doc->project_id,['reason'=>$data['reason'],'published_version_id'=>$doc->published_version_id]);
        });
        return back()->with('success','Published document withdrawn from client visibility.');
    }

    public function download(Request $request,int $document,int $version)
    {
        $this->checkPermission($request,'client_control.documents.view');
        $doc=DB::table('client_vault_documents')->where('id',$document)->first();abort_unless($doc,404);
        $v=DB::table('client_vault_document_versions')->where('id',$version)->where('document_id',$document)->first();abort_unless($v,404);
        abort_unless(Storage::disk($v->disk)->exists($v->storage_path),404);
        $this->audit($request,'internal_download',$document,(int)$doc->project_id,['version'=>$version]);
        return Storage::disk($v->disk)->download($v->storage_path,$v->original_filename);
    }

    private function checkPermission(Request $request,string $permission): void
    {
        $user=$request->user();
        abort_unless($user && DB::table('permission_role as pr')->join('permissions as p','p.id','=','pr.permission_id')
            ->where('pr.role_id',$user->role_id)->where('p.name',$permission)->where('p.is_active',1)->exists(),403);
    }
}
