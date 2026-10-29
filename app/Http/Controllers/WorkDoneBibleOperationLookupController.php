<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkDoneBibleOperationLookupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $v = $request->validate([
            'project_id' => ['required','integer','exists:projects,id'],
            'stage_id' => ['nullable','integer'],
            'trade' => ['nullable','string','max:255'],
            'assembly_id' => ['nullable','integer'],
            'element' => ['nullable','string','max:255'],
            'work_item_id' => ['nullable','integer'],
            'search' => ['nullable','string','max:150'],
        ]);
        $projectId = (int)$v['project_id'];
        $user = $request->user();
        abort_unless($user, 403);
        $roles = method_exists($user,'roles') ? $user->roles()->pluck('name')->all() : [(string)($user->role->name ?? (is_string($user->role ?? null) ? $user->role : ''))];
        $manager = collect($roles)->contains(fn($role) => in_array(strtolower(trim((string)$role)), ['admin','pmo'],true));
        if (!$manager) abort_unless(method_exists($user,'projects') && $user->projects()->where('projects.id',$projectId)->exists(),403,'You are not assigned to this project.');

        $assignment = DB::table('work_done_bible_project_type_assignments')->where('project_id',$projectId)->first();
        $query = DB::table('work_done_bible_operations as o')
            ->leftJoin('work_done_bible_assemblies as ass','ass.id','=','o.assembly_id')
            ->leftJoin('work_done_bible_work_items as wi','wi.id','=','o.work_item_id')
            ->leftJoin('work_done_bible_stages as s','s.id','=','o.stage_id')
            ->leftJoin('work_done_bible_project_applicabilities as a',fn($join) => $join->on('a.operation_id','=','o.id')->where('a.project_id',$projectId))
            ->where('o.is_active',1)->where('o.record_type','PHYSICAL')
            ->where(function($q) { $q->whereNull('a.id')->orWhere(function($p) { $p->where('a.is_enabled',1)->whereIn('a.applicability',['Applicable','Optional']); }); });
        if ($assignment) {
            $query->leftJoin('work_done_bible_template_operations as t',fn($join) => $join->on('t.operation_id','=','o.id')->where('t.project_type_id',$assignment->bible_project_type_id));
            $query->where(function($q) { $q->whereNotNull('a.id')->orWhereNull('t.id')->orWhere('t.review_status','!=','Approved')->orWhere('t.is_enabled',1); });
        }
        foreach (['stage_id','assembly_id','work_item_id'] as $field) if (!empty($v[$field])) $query->where('o.'.$field,$v[$field]);
        foreach (['trade','element'] as $field) if (isset($v[$field]) && $v[$field] !== '') $query->where('o.'.$field,$v[$field]);
        if (!empty($v['search'])) {
            $term = '%'.addcslashes(trim($v['search']),'%_\\').'%';
            $query->where(function($q) use($term) {
                foreach (['o.micro_operation','o.trade','o.element','ass.assembly_name','wi.work_item_name','s.stage_name','o.wd_code'] as $col) $q->orWhere($col,'like',$term);
            });
        }
        $ops = $query->orderBy('o.stage_id')->orderBy('o.micro_operation')->limit(5000)->get([
            'o.id','o.stage_id','s.stage_name','o.trade','o.assembly_id','ass.assembly_name','o.element',
            'o.work_item_id','wi.work_item_name','o.micro_operation as name','o.uom as unit','o.wd_code'
        ]);
        return response()->json(['operations'=>$ops]);
    }
}
