<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RfqWorkflowIndexController extends Controller
{
    private function authorizeAccess(Request $request, bool $awards): void
    {
        $user = $request->user();
        abort_unless($user && $user->isActive() && $user->hasPermission('rfqs.view'), 403);
        if ($awards) {
            abort_unless($user->hasPermission('purchase_orders.create'), 403);
        }
    }

    private function records()
    {
        // Each RFQ appears only once. Draft and revoked quotations are excluded.
        return DB::table('rfqs as r')
            ->leftJoin('projects as p', 'p.id', '=', 'r.project_id')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('rfq_vendor_invitations as inv')
                    ->join('rfq_quotations as q', 'q.rfq_vendor_invitation_id', '=', 'inv.id')
                    ->whereColumn('inv.rfq_id', 'r.id')
                    ->where('inv.status', 'Submitted')
                    ->whereNull('inv.revoked_at')
                    ->where('q.status', 'Submitted');
            })
            ->select('r.id', 'r.rfq_number', 'r.status', 'r.rfq_date', 'r.submission_deadline', 'p.project_name')
            ->orderByDesc('r.id')
            ->paginate(20);
    }

    public function comparisons(Request $request)
    {
        $this->authorizeAccess($request, false);
        return view('rfqs.workflow-index', [
            'rfqs' => $this->records(),
            'pageTitle' => 'Quotation Comparison',
            'pageDescription' => 'RFQs with final vendor quotations ready for comparison.',
            'isAwards' => false,
        ]);
    }

    public function awards(Request $request)
    {
        $this->authorizeAccess($request, true);
        return view('rfqs.workflow-index', [
            'rfqs' => $this->records(),
            'pageTitle' => 'Quotation Awards',
            'pageDescription' => 'Review final vendor quotations and continue award decisions.',
            'isAwards' => true,
        ]);
    }
}
