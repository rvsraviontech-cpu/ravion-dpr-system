@extends('client-control.layout')
@section('content')
@php
    $money = function ($number) {
        $value = number_format((float) ($number ?? 0), 2, '.', '');
        [$whole, $decimal] = explode('.', $value);
        $negative = str_starts_with($whole, '-');
        $digits = ltrim($whole, '-');
        if (strlen($digits) > 3) {
            $digits = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($digits, 0, -3)) . ',' . substr($digits, -3);
        }
        return ($negative ? '-' : '') . '₹' . $digits . '.' . $decimal;
    };
    $stages = collect($statement['stages'] ?? []);
    $receipts = collect($statement['receipts'] ?? []);
    $stageNames = $stages->keyBy('id');
    $agreement = (float) ($statement['agreement_amount'] ?? 0);
    $variations = (float) ($statement['approved_variations'] ?? 0);
    $contractTotal = $agreement + $variations;
    $received = (float) ($statement['total_received'] ?? 0);
    $outstanding = (float) ($statement['total_outstanding'] ?? max(0, $contractTotal - $received));
    $progress = $contractTotal > 0 ? max(0, min(100, ($received / $contractTotal) * 100)) : 0;
@endphp
<style>
    .rpay { --rp-ink:#142943; --rp-muted:#64748b; --rp-line:#e2e8f0; --rp-bg:#f5f8fc; --rp-blue:#1e40af; color:var(--rp-ink); max-width:1120px; margin:0 auto; padding:24px 18px 44px; font-family:inherit; }
    .rpay *, .rpay *::before, .rpay *::after { box-sizing:border-box; }
    .rpay a { color:#1d4ed8; text-decoration:none; }
    .rpay a:hover { text-decoration:underline; }
    .rpay-back { display:inline-flex; align-items:center; gap:7px; font-size:13px; font-weight:600; margin-bottom:17px; }
    .rpay-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:22px; }
    .rpay h1 { font-size:clamp(23px,3vw,30px); line-height:1.2; letter-spacing:-.025em; margin:0 0 8px; font-weight:750; color:#112b4b; }
    .rpay-subtitle { color:var(--rp-muted); font-size:14px; margin:0; }
    .rpay-version { display:inline-flex; align-items:center; gap:6px; color:#475569; background:#edf2f8; border:1px solid #e1e8f1; padding:8px 11px; border-radius:9px; font-size:12px; white-space:nowrap; }
    .rpay-card { background:#fff; border:1px solid var(--rp-line); border-radius:14px; box-shadow:0 2px 10px rgba(20,41,67,.035); }
    .rpay-summary { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin-bottom:16px; }
    .rpay-metric { padding:19px 20px; min-width:0; }
    .rpay-metric-label { color:var(--rp-muted); font-size:12px; font-weight:600; margin-bottom:8px; }
    .rpay-metric-value { color:#132b4a; font-size:clamp(19px,2.1vw,26px); line-height:1.2; font-weight:750; font-variant-numeric:tabular-nums; overflow-wrap:anywhere; }
    .rpay-metric--paid .rpay-metric-value { color:#087f5b; }
    .rpay-contract-note { color:var(--rp-muted); font-size:12px; margin-top:9px; line-height:1.5; }
    .rpay-progress { padding:17px 20px 19px; margin-bottom:24px; }
    .rpay-progress-heading { display:flex; align-items:baseline; justify-content:space-between; gap:10px; margin-bottom:12px; }
    .rpay-progress-heading strong { font-size:13px; }
    .rpay-progress-heading span { font-size:13px; color:var(--rp-muted); font-weight:600; }
    .rpay-track { height:9px; background:#eaf0f6; border-radius:99px; overflow:hidden; }
    .rpay-fill { height:100%; border-radius:99px; background:#15966e; }
    .rpay-section { margin-top:22px; overflow:hidden; }
    .rpay-section-heading { padding:19px 20px 16px; display:flex; align-items:baseline; justify-content:space-between; gap:12px; border-bottom:1px solid var(--rp-line); }
    .rpay-section-heading h2 { font-size:17px; font-weight:750; margin:0; color:#142b4a; }
    .rpay-section-heading p { margin:0; font-size:12px; color:var(--rp-muted); }
    .rpay-table { width:100%; border-collapse:collapse; table-layout:fixed; font-size:13px; }
    .rpay-table th { text-transform:uppercase; letter-spacing:.045em; font-size:10px; color:#64748b; background:#f8fafc; font-weight:750; text-align:left; padding:13px 14px; }
    .rpay-table td { padding:15px 14px; border-top:1px solid #edf1f5; vertical-align:middle; }
    .rpay-table tbody tr:first-child td { border-top:0; }
    .rpay-table tbody tr:nth-child(even) { background:#fcfdff; }
    .rpay-table .rpay-num { text-align:right; white-space:nowrap; font-variant-numeric:tabular-nums; }
    .rpay-table .rpay-stage { font-weight:650; overflow-wrap:anywhere; }
    .rpay-date { display:block; margin-top:4px; font-size:11px; font-weight:400; color:var(--rp-muted); }
    .rpay-status { display:inline-flex; align-items:center; justify-content:center; padding:5px 9px; border-radius:99px; font-size:11px; font-weight:700; white-space:nowrap; }
    .rpay-status--paid { color:#08764e; background:#dcfce7; }
    .rpay-status--part { color:#995200; background:#fff0cc; }
    .rpay-status--pending { color:#516175; background:#eef2f7; }
    .rpay-empty { text-align:center; padding:28px 16px !important; color:var(--rp-muted); }
    .rpay-footer { margin:22px 2px 0; font-size:12px; line-height:1.6; color:var(--rp-muted); }
    .rpay-unpublished { padding:25px; color:#475569; line-height:1.6; }
    @media (max-width:800px) {
      .rpay { padding:20px 12px 35px; }
      .rpay-header { display:block; }
      .rpay-version { margin-top:13px; white-space:normal; }
      .rpay-summary { gap:9px; }
      .rpay-metric { padding:15px 13px; }
      .rpay-metric-value { font-size:19px; }
      .rpay-table { font-size:12px; }
      .rpay-table th,.rpay-table td { padding:12px 8px; }
    }
    @media (max-width:620px) {
      .rpay-summary { grid-template-columns:repeat(2,minmax(0,1fr)); }
      .rpay-metric:first-child { grid-column:span 2; }
      .rpay-metric-value { font-size:21px; }
      .rpay-section-heading { display:block; padding:16px; }
      .rpay-section-heading p { margin-top:5px; }
      .rpay-table, .rpay-table tbody, .rpay-table tr, .rpay-table td { display:block; width:100%; }
      .rpay-table thead { display:none; }
      .rpay-table tbody tr { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px 14px; padding:16px; border-top:1px solid var(--rp-line); }
      .rpay-table tbody tr:first-child { border-top:0; }
      .rpay-table td { padding:0; border:0; min-width:0; }
      .rpay-table td::before { content:attr(data-label); display:block; margin-bottom:4px; color:var(--rp-muted); font-size:10px; text-transform:uppercase; letter-spacing:.045em; font-weight:700; }
      .rpay-table .rpay-stage { grid-column:span 2; font-size:14px; margin-bottom:3px; }
      .rpay-table .rpay-stage::before { display:none; }
      .rpay-table .rpay-num { text-align:left; white-space:normal; }
      .rpay-table .rpay-status-cell { grid-column:span 2; }
      .rpay-table .rpay-empty { display:block; }
      .rpay-receipts-table td:first-child { grid-column:span 2; }
    }

    /* Match the existing premium project dashboard navigation without changing payment content. */
    .rpay .nav { display:flex; align-items:center; justify-content:space-between; gap:10px; overflow-x:auto; margin:10px 0 20px; padding:5px 15px 8px; border:0; border-radius:14px; scrollbar-width:thin; }
    .rpay .nav-item { display:flex; align-items:center; gap:8px; flex:none; white-space:nowrap; padding:10px 15px; border-radius:22px; color:#294977; font-size:13px; font-weight:700; text-decoration:none; }
    .rpay .nav-item:hover:not(.active):not(.disabled) { background:#e3edfa; text-decoration:none; }
    .rpay .nav-item.active { background:linear-gradient(135deg,#245e93,#0f315d); color:#fff; box-shadow:0 4px 9px #17456b2e; }
    .rpay .nav-item.disabled { opacity:.55; cursor:not-allowed; }
    .rpay .nav-item:focus-visible { outline:3px solid #0e8897; outline-offset:2px; }
    .rpay .nav-icon { width:19px; height:19px; flex:none; }
    @media(max-width:620px) { .rpay .nav { justify-content:flex-start; padding-left:0; padding-right:0; } .rpay .nav-item { padding:10px 12px; } }
</style>
<div class="rpay">
    <nav class="nav" aria-label="Client project navigation">
        <a class="nav-item" href="{{ route('client.projects.show', $projectRecord->id) }}"><svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><use href="#ri-home"/></svg>Home</a>
        <a class="nav-item" href="{{ route('client.projects.show', $projectRecord->id) }}#published-progress"><svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><use href="#ri-chart"/></svg>Progress</a>
        <a class="nav-item" href="{{ route('client.quality.index', $projectRecord->id) }}"><svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><use href="#ri-camera"/></svg>Quality &amp; Testing</a>
        @if(Route::has('client.vault.index'))
            <a class="nav-item" href="{{ route('client.vault.index', $projectRecord->id) }}"><svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><use href="#ri-file"/></svg>Documents</a>
        @else
            <span class="nav-item disabled" aria-disabled="true">Documents</span>
        @endif
        @if(Route::has('client.decisions.index'))
            <a class="nav-item" href="{{ route('client.decisions.index', $projectRecord->id) }}"><svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><use href="#ri-check"/></svg>My Decisions</a>
        @else
            <span class="nav-item disabled" aria-disabled="true">My Decisions</span>
        @endif
        <a class="nav-item active" href="{{ route('client.payments.show', $projectRecord->id) }}" aria-current="page"><svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><use href="#ri-wallet"/></svg>Payments</a>
        <span class="nav-item disabled" aria-disabled="true" title="Coming soon"><svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><use href="#ri-help"/></svg>Contact &amp; Support</span>
    </nav>

    <a class="rpay-back" href="{{ route('client.projects.show', $projectRecord->id) }}">&larr; Back to project</a>
    <div class="rpay-header">
        <div>
            <h1>{{ $projectRecord->project_name }} — Payments</h1>
            <p class="rpay-subtitle">Your payment schedule, payments received and balance payable</p>
        </div>
        @if($statement)
            <div class="rpay-version">Updated {{ $statement['published_date'] ?? '—' }} @if(isset($publication) && $publication) · Version {{ $publication->revision }} @endif</div>
        @endif
    </div>

    @if(!$statement)
        <div class="rpay-card rpay-unpublished">Your payment statement has not been published yet. Please check again later.</div>
    @else
        <div class="rpay-summary">
            <div class="rpay-card rpay-metric">
                <div class="rpay-metric-label">Agreed amount</div>
                <div class="rpay-metric-value">{{ $money($contractTotal) }}</div>
                @if(abs($variations) > 0.001)
                    <div class="rpay-contract-note">Original {{ $money($agreement) }} + approved changes {{ $money($variations) }}</div>
                @endif
            </div>
            <div class="rpay-card rpay-metric rpay-metric--paid">
                <div class="rpay-metric-label">Payments received</div>
                <div class="rpay-metric-value">{{ $money($received) }}</div>
            </div>
            <div class="rpay-card rpay-metric">
                <div class="rpay-metric-label">Balance payable</div>
                <div class="rpay-metric-value">{{ $money($outstanding) }}</div>
            </div>
        </div>

        <div class="rpay-card rpay-progress">
            <div class="rpay-progress-heading"><strong>Payment progress</strong><span>{{ number_format($progress, 1) }}% received</span></div>
            <div class="rpay-track" role="progressbar" aria-label="Payment progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($progress, 1) }}">
                <div class="rpay-fill" style="width:{{ number_format($progress, 2, '.', '') }}%"></div>
            </div>
        </div>

        <section class="rpay-card rpay-section">
            <div class="rpay-section-heading"><h2>Agreed payment schedule</h2><p>{{ $stages->count() }} payment stages</p></div>
            <table class="rpay-table">
                <colgroup><col style="width:38%"><col style="width:17%"><col style="width:16%"><col style="width:17%"><col style="width:12%"></colgroup>
                <thead><tr><th>Payment stage</th><th class="rpay-num">Agreed</th><th class="rpay-num">Paid</th><th class="rpay-num">Balance</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($stages as $stage)
                        @php
                            $stageAmount = (float) ($stage['amount'] ?? 0);
                            $stagePaid = (float) ($stage['received'] ?? 0);
                            $stageBalance = (float) ($stage['balance'] ?? max(0, $stageAmount - $stagePaid));
                            $stageStatus = $stageAmount > 0 && $stageBalance <= 0.005 ? 'Paid' : ($stagePaid > 0 ? 'Part paid' : 'Pending');
                            $stageStatusClass = $stageStatus === 'Paid' ? 'paid' : ($stageStatus === 'Part paid' ? 'part' : 'pending');
                        @endphp
                        <tr>
                            <td class="rpay-stage" data-label="Payment stage">{{ $stage['name'] ?? 'Payment stage' }}
                                @if(!empty($stage['expected_date']))<span class="rpay-date">Expected: {{ $stage['expected_date'] }}</span>@endif
                            </td>
                            <td class="rpay-num" data-label="Agreed">{{ $money($stageAmount) }}</td>
                            <td class="rpay-num" data-label="Paid">{{ $money($stagePaid) }}</td>
                            <td class="rpay-num" data-label="Balance">{{ $money($stageBalance) }}</td>
                            <td class="rpay-status-cell" data-label="Status"><span class="rpay-status rpay-status--{{ $stageStatusClass }}">{{ $stageStatus }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="rpay-empty">No payment stages have been published yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="rpay-card rpay-section">
            <div class="rpay-section-heading"><h2>Payment history</h2><p>Payments recorded in your published statement</p></div>
            <table class="rpay-table rpay-receipts-table">
                <colgroup><col style="width:22%"><col style="width:53%"><col style="width:25%"></colgroup>
                <thead><tr><th>Date</th><th>Payment stage</th><th class="rpay-num">Amount received</th></tr></thead>
                <tbody>
                    @forelse($receipts as $receipt)
                        <tr>
                            <td data-label="Date">{{ $receipt['date'] ?? '—' }}</td>
                            <td data-label="Payment stage">{{ data_get($stageNames->get($receipt['milestone_id'] ?? null), 'name', 'Payment') }}</td>
                            <td class="rpay-num" data-label="Amount received">{{ $money($receipt['amount'] ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="rpay-empty">No payments have been published yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <p class="rpay-footer">This statement is provided for project payment information. It is not a tax invoice or bank confirmation. Please contact your project team if any details require clarification.</p>
    @endif
</div>
@endsection
