@extends('client-control.layout')
@section('title', $project['project_name'])
@section('content')
<style>
.published-progress-stages{margin-top:22px}.published-stage-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px;font-size:13px;color:#102b42}.published-stage-heading span{font-size:11px;color:#71869a}.published-stage{border:1px solid #e2eaf1;border-radius:13px;margin-bottom:10px;background:#fff;overflow:hidden}.published-stage-summary{display:flex;align-items:center;gap:12px;padding:15px;cursor:pointer;list-style:none}.published-stage-summary::-webkit-details-marker{display:none}.published-stage-summary:focus-visible{outline:3px solid #55b5be;outline-offset:-3px}.published-stage-number{display:grid;place-items:center;flex:none;width:33px;height:33px;border-radius:10px;background:#e8f5f6;color:#087f8c;font-size:13px;font-weight:800}.published-stage-main{flex:1;min-width:0}.published-stage-name{display:block;color:#102b42;font-weight:800;font-size:13px;line-height:1.45}.published-stage-track{display:block;margin-top:8px;height:6px;background:#edf2f6;border-radius:20px;overflow:hidden}.published-stage-track span{display:block;height:100%;background:#087f8c;border-radius:20px}.published-stage-metric{text-align:right;flex:none;min-width:59px}.published-stage-metric strong{display:block;font-size:15px;color:#087f8c}.published-stage-metric small{font-size:10px;color:#71869a}.published-stage-chevron{color:#71869a;transition:transform .2s}.published-stage[open] .published-stage-chevron{transform:rotate(180deg)}.published-stage-body{padding:4px 15px 14px 60px;border-top:1px solid #edf2f6}.published-task{display:flex;justify-content:space-between;gap:14px;padding:11px 0;border-bottom:1px solid #edf2f6;font-size:12px;color:#38566b}.published-task:last-child{border-bottom:0}.published-task strong{color:#087f8c;white-space:nowrap}.published-task-empty{font-size:12px;color:#71869a;line-height:1.6}@media(max-width:640px){.published-stage-heading{align-items:flex-start;flex-direction:column}.published-stage-summary{gap:9px;padding:12px}.published-stage-body{padding-left:14px}.published-stage-name{font-size:12px}.published-stage-metric{min-width:48px}}

/* Published stage groups: scoped to the client progress card. */
.progress-overview-counts{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;margin:12px 0 20px}
.progress-overview-count{background:#f5f9fb;border:1px solid #e4edf2;border-radius:12px;padding:12px 9px;text-align:center}
.progress-overview-count strong{display:block;font-size:22px;color:#102b42;line-height:1.25}
.progress-overview-count span{display:block;font-size:11px;color:#71869a;margin-top:3px}
.progress-group{margin-top:20px}.progress-group-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:10px}
.progress-group-heading h4{margin:0;font-size:14px;font-weight:800;color:#102b42}.progress-group-heading small{font-size:11px;color:#71869a}
.progress-group-empty{border:1px dashed #d9e5ec;border-radius:12px;padding:15px;color:#71869a;font-size:12px;line-height:1.6}
.progress-upcoming{border:1px solid #dce7ed;border-radius:14px;overflow:hidden;background:#f8fafc}
.progress-upcoming>summary{cursor:pointer;list-style:none;padding:16px;display:flex;align-items:center;justify-content:space-between;gap:12px;color:#102b42;font-weight:800;font-size:13px}
.progress-upcoming>summary::-webkit-details-marker{display:none}.progress-upcoming>summary:focus-visible{outline:3px solid #55b5be;outline-offset:-3px}
.progress-upcoming>summary small{font-size:11px;font-weight:500;color:#71869a}.progress-upcoming-content{padding:0 12px 12px}
.progress-upcoming .published-stage{margin-bottom:8px}.progress-upcoming .published-stage:last-child{margin-bottom:0}
@media(max-width:440px){.progress-overview-counts{gap:5px}.progress-overview-count{padding:10px 4px}.progress-overview-count strong{font-size:19px}.progress-overview-count span{font-size:10px}}

/* Ravion Premium Client Portal — scoped to the project home view. */
.ravion-premium{--rv-navy:#102b4e;--rv-teal:#087f8c;--rv-green:#168552;--rv-amber:#e5a12b;--rv-blue:#1767c8;--rv-ink:#102b42;color:var(--rv-ink)}
.ravion-premium .portal-hero{background:linear-gradient(115deg,#173c67 0%,#126180 75%,#087f8c 100%);border-radius:22px;box-shadow:0 12px 30px rgba(16,43,78,.13);overflow:hidden;position:relative}
.ravion-premium .portal-hero:after{content:'';position:absolute;right:-75px;top:-120px;width:310px;height:310px;border:2px solid rgba(229,161,43,.65);border-radius:50%;pointer-events:none}
.ravion-premium .portal-hero .page-title,.ravion-premium .portal-hero .eyebrow,.ravion-premium .portal-hero .lead{color:#fff}
.ravion-premium .portal-hero .eyebrow{opacity:.8;letter-spacing:.12em}
.ravion-premium .hero-status{background:rgba(7,105,115,.55);color:#fff;border:1px solid rgba(167,235,227,.5);box-shadow:0 4px 15px rgba(0,0,0,.1)}
.ravion-premium .nav{border-radius:14px;gap:5px}.ravion-premium .nav-item.active{background:linear-gradient(120deg,#173c67,#102b4e);color:#fff;box-shadow:0 5px 12px rgba(16,43,78,.18)}
.ravion-premium .info-note{background:linear-gradient(95deg,#e6f6ff,#e2efff);border:1px solid #b8dcfb;border-radius:15px;color:#173c67}
.ravion-premium .section-label h2{color:var(--rv-navy)}
.ravion-premium .dashboard-grid{gap:18px}
.ravion-premium .dash-card{border:1px solid #e0eaf4;border-radius:20px;box-shadow:0 9px 28px rgba(27,67,105,.065);background:linear-gradient(150deg,#fff 65%,#f7fbff 100%)}
.ravion-premium .card-heading{margin-bottom:12px}.ravion-premium .section-title{color:var(--rv-navy)}
.ravion-premium .icon-tile{background:linear-gradient(145deg,#0caeab,#087f8c);color:white;border-radius:15px;box-shadow:0 5px 13px rgba(8,127,140,.18)}
.ravion-premium .icon-tile.blue{background:linear-gradient(145deg,#dcecff,#e8f2ff);color:#1767c8;box-shadow:none}
.ravion-premium .icon-tile.amber{background:linear-gradient(145deg,#fff0cf,#fff8eb);color:#b47709;box-shadow:none}
.ravion-premium .icon-tile.violet{background:linear-gradient(145deg,#ece6ff,#f7f4ff);color:#6555a8;box-shadow:none}
.ravion-premium .rv-progress-top{padding:12px 0 5px}.ravion-premium .rv-progress-topline{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.ravion-premium .rv-progress-value{font-size:clamp(31px,4vw,43px);line-height:1.1;color:#087f65;font-weight:850;letter-spacing:-.04em}
.ravion-premium .rv-revision{font-size:11px;color:#274b76;background:#f3f8ff;border:1px solid #dce9fa;border-radius:12px;padding:9px 12px}
.ravion-premium .rv-overall-track{height:12px;background:#dce5f3;border-radius:100px;overflow:hidden;margin:15px 0 9px}.ravion-premium .rv-overall-track span{display:block;height:100%;background:linear-gradient(90deg,#38b678,#087f8c);border-radius:100px}
.ravion-premium .progress-overview-counts{gap:12px;margin:17px 0 22px}
.ravion-premium .progress-overview-count{display:flex;align-items:center;text-align:left;gap:12px;border-radius:15px;padding:17px 14px;border:1px solid transparent}
.ravion-premium .progress-overview-count:nth-child(1){background:linear-gradient(110deg,#e9f9ee,#d8f5e9);border-color:#d4efe0}
.ravion-premium .progress-overview-count:nth-child(2){background:linear-gradient(110deg,#fff9e9,#fff0db);border-color:#faecd0}
.ravion-premium .progress-overview-count:nth-child(3){background:linear-gradient(110deg,#edf5ff,#dceaff);border-color:#d7e7ff}
.ravion-premium .rv-count-icon{width:38px;height:38px;display:grid;place-items:center;border-radius:50%;font-size:20px;font-weight:900;flex:none}
.ravion-premium .progress-overview-count:nth-child(1) .rv-count-icon{background:#36a66c;color:white}
.ravion-premium .progress-overview-count:nth-child(2) .rv-count-icon{background:#f1ac28;color:#fff}
.ravion-premium .progress-overview-count:nth-child(3) .rv-count-icon{background:#d0e5ff;color:#1767c8}
.ravion-premium .progress-overview-count strong{font-size:25px;color:#102b4e}.ravion-premium .progress-overview-count span{font-size:12px;color:#38566b}
.ravion-premium .progress-group-heading h4{font-size:14px}.ravion-premium .progress-group-heading small{color:#647d9a}
.ravion-premium .progress-group[data-group=completed] .progress-group-heading h4:before{content:'✓';display:inline-grid;place-items:center;margin-right:9px;width:21px;height:21px;border-radius:50%;background:#24a25d;color:#fff}
.ravion-premium .progress-group[data-group=in_progress] .progress-group-heading h4:before{content:'◷';color:#dd950b;margin-right:10px;font-size:20px}
.ravion-premium .progress-group[data-group=upcoming] .progress-group-heading h4:before{content:'▦';color:#1767c8;margin-right:10px;font-size:19px}
.ravion-premium .published-stage{border:1px solid #dce7f3;border-radius:14px;box-shadow:0 3px 8px rgba(16,43,78,.035)}
.ravion-premium .published-stage-number{background:#e2f4f0;color:#087f8c}.ravion-premium .published-stage-track span{background:linear-gradient(90deg,#2ac6a4,#087f65)}
.ravion-premium .published-stage-metric strong{color:#087f65}
.ravion-premium .progress-upcoming{background:linear-gradient(110deg,#f5faff,#eaf3ff);border:1px solid #cde0fa}
.ravion-premium .progress-upcoming>summary{color:#173c67}.ravion-premium .progress-group-empty{background:#f8fbff;border-color:#c9def3}
.ravion-premium .rv-summary{display:grid;gap:8px;margin-top:15px}.ravion-premium .rv-summary-row{display:flex;align-items:center;justify-content:space-between;gap:12px;background:linear-gradient(95deg,#f4f8ff,#eaf2ff);border:1px solid #e1eafb;padding:11px 13px;border-radius:11px;font-size:12px}
.ravion-premium .rv-summary-row span{color:#4b6584}.ravion-premium .rv-summary-row strong{color:#102b4e;text-align:right;overflow-wrap:anywhere}
@media(max-width:700px){.ravion-premium .progress-overview-counts{grid-template-columns:repeat(3,minmax(0,1fr));gap:7px}.ravion-premium .progress-overview-count{flex-direction:column;text-align:center;padding:11px 4px;gap:5px}.ravion-premium .rv-count-icon{width:32px;height:32px;font-size:17px}.ravion-premium .progress-overview-count strong{font-size:22px}.ravion-premium .progress-overview-count span{font-size:10px}.ravion-premium .rv-revision{width:100%}}
@media(prefers-reduced-motion:reduce){.ravion-premium *{transition:none!important}}

/* Exact-reference desktop composition — uses only published snapshot data. */
body:has(.rv-exact){background:#f1f6ff;color:#102448}
body:has(.rv-exact) .container{max-width:1600px;padding:16px 30px 65px}
body:has(.rv-exact) .topbar{background:linear-gradient(100deg,#fff 0%,#edf5ff 100%);border-bottom:1px solid #dce7f6}
body:has(.rv-exact) .top-inner{max-width:1600px;padding:12px 30px;min-height:68px}
body:has(.rv-exact) .brand{display:flex;align-items:center;gap:12px;font-size:24px;letter-spacing:-.9px;color:#10264e}
body:has(.rv-exact) .brand .rv-brand-portal{max-width:none}
body:has(.rv-exact) .signout{border-radius:10px;color:#163d65;border-color:#d7e5f7}
.rv-exact{--rv-navy:#102b4e;--rv-teal:#087f8c;--rv-green:#198754;--rv-amber:#e5a12b;--rv-blue:#1767c8;font-size:14px}
.rv-exact .portal-hero{height:176px;border-radius:21px;background:linear-gradient(100deg,#244e7e 0%,#0e567d 76%,#0b7284 100%);box-shadow:0 10px 24px #123a6c20;position:relative;overflow:hidden}
.rv-exact .portal-hero:after{display:none}
.rv-exact .rv-hero-image{position:absolute;right:0;top:0;bottom:0;width:34%;background-image:url('{{ asset('images/client-control/ravion-hero-illustration.jpg') }}');background-size:cover;background-position:center;clip-path:polygon(19% 0,100% 0,100% 100%,0 100%);border-radius:0 20px 20px 0}
.rv-exact .rv-hero-image:before{content:'';position:absolute;left:0;top:0;bottom:0;width:10px;background:#e2b33e;transform:skew(-37deg);transform-origin:bottom left}
.rv-exact .rv-hero-content{position:relative;z-index:1;padding:17px 42px;height:100%;display:flex;flex-direction:column;align-items:flex-start}
.rv-exact .rv-hero-back{color:#fff;font-weight:700;font-size:13px;opacity:.95}
.rv-exact .rv-hero-copy{margin:8px 0 0 125px}
.rv-exact .portal-hero .eyebrow{font-size:12px;font-weight:700;letter-spacing:.7px;color:#dceeff}
.rv-exact .portal-hero .page-title{font-size:38px;line-height:1.1;letter-spacing:-1px;margin:8px 0 10px;color:#fff}
.rv-exact .portal-hero .lead{font-size:15px;margin:0;color:#f1f7ff}
.rv-exact .hero-status{position:absolute;right:35%;top:54px;padding:11px 17px;border-radius:16px;background:#0a6d7cbb;white-space:nowrap;font-size:13px;font-weight:700}
.rv-exact .nav{margin:10px 0 14px;padding:5px 15px 8px;border:0;display:flex;align-items:center;justify-content:space-between;gap:10px;overflow-x:auto}
.rv-exact .nav-item{display:flex;align-items:center;gap:8px;padding:10px 15px;border-radius:22px;color:#294977;font-size:13px;font-weight:700}
.rv-exact .nav-item.active{background:linear-gradient(135deg,#245e93,#0f315d);box-shadow:0 4px 9px #17456b2e}
.rv-exact .nav-icon{width:19px;height:19px}
.rv-exact .info-note{border-radius:15px;padding:15px 24px;background:linear-gradient(110deg,#e0f4ff,#e0eeff);border:1px solid #b8dbff;color:#123563;margin:0 0 22px;display:flex;align-items:center;gap:12px}
.rv-exact .section-label{margin:0 0 15px;display:flex;align-items:center;justify-content:space-between}
.rv-exact .section-label h2{font-size:22px;letter-spacing:-.3px;color:#0e2447}
.rv-exact .section-label .status{background:#d7f5e9;color:#08744b;border-radius:30px;padding:9px 17px}
.rv-exact .rv-dashboard-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(320px,1fr);gap:18px;align-items:start}
.rv-exact .rv-dashboard-main,.rv-exact .rv-dashboard-aside{min-width:0}
.rv-exact .rv-dashboard-aside{display:flex;flex-direction:column;gap:16px}
.rv-exact .dash-card{background:linear-gradient(160deg,#fff 65%,#f7fbff);border-radius:21px;border:1px solid #e1eaf7;box-shadow:0 8px 26px #193d6b12;padding:25px 27px}
.rv-exact .rv-dashboard-main .dash-card{padding:25px 30px}
.rv-exact .rv-dashboard-aside .dash-card{min-height:188px}
.rv-exact .card-heading{display:flex;align-items:center;gap:14px;margin:0 0 18px}
.rv-exact .section-title{font-size:19px;font-weight:800;margin:0 0 5px}
.rv-exact .card-subtitle{font-size:13px;color:#537096}
.rv-exact .icon-tile{width:50px;height:50px;border-radius:16px;display:grid;place-items:center;flex:none}
.rv-exact .icon-tile svg{width:25px;height:25px}
.rv-exact .rv-progress-top{padding:0}
.rv-exact .rv-progress-topline{align-items:center;gap:18px}
.rv-exact .rv-progress-value{font-size:48px;color:#087b61;letter-spacing:-1.6px;line-height:1.1}
.rv-exact .rv-revision{display:flex;align-items:center;gap:12px;border:1px solid #d9e8fa;background:#f6faff;border-radius:14px;padding:11px 14px;color:#143d72}
.rv-exact .rv-revision strong,.rv-exact .rv-revision small{display:block;white-space:nowrap}
.rv-exact .rv-revision small{font-size:11px;margin-top:3px;color:#57749a}
.rv-exact .rv-calendar-icon{font-size:27px;color:#245ca3}
.rv-exact .rv-overall-track{height:12px;margin:12px 0 21px;background:#d5e1f3}
.rv-exact .published-progress-stages{margin-top:0}
.rv-exact .published-stage-heading{margin-bottom:12px;font-size:14px}
.rv-exact .progress-overview-counts{gap:14px;margin:15px 0 22px}
.rv-exact .progress-overview-count{min-height:80px;border-radius:15px;gap:15px;padding:14px 17px}
.rv-exact .rv-count-icon{width:48px;height:48px}
.rv-exact .rv-count-icon svg{width:24px;height:24px}
.rv-exact .progress-overview-count strong{font-size:29px}
.rv-exact .progress-overview-count span{font-size:13px}
.rv-exact .progress-group{margin-top:16px}
.rv-exact .progress-group-heading h4{font-size:15px}
.rv-exact .progress-group-heading small{font-size:12px}
.rv-exact .published-stage{border-radius:15px}
.rv-exact .published-stage-summary{padding:14px 17px}
.rv-exact .published-stage-number{border-radius:50%;width:33px;height:33px}
.rv-exact .published-stage-name{font-size:14px}
.rv-exact .published-stage-metric strong{font-size:17px}
.rv-exact .progress-upcoming>summary{background:#edf5ff}
.rv-exact .rv-dashboard-aside .empty{padding:8px 0 12px;line-height:1.7}
.rv-exact .rv-summary{gap:7px}
.rv-exact .rv-summary-row{font-size:13px;padding:11px 12px;background:linear-gradient(100deg,#f4f8ff,#e8f1ff)}
.rv-exact .rv-summary-row span{display:flex;align-items:center;gap:10px}
.rv-exact .rv-row-icon{font-size:18px;color:#2c5fb5;width:21px;text-align:center}
.rv-exact .rv-dashboard-extras{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin-top:20px}
.rv-exact .rv-dashboard-extras .dash-card{min-height:180px}
@media(max-width:1100px){.rv-exact .rv-hero-copy{margin-left:45px}.rv-exact .rv-dashboard-grid{grid-template-columns:minmax(0,1.6fr) minmax(280px,1fr)}.rv-exact .hero-status{right:34%;top:25px}.rv-exact .progress-overview-count{flex-direction:column;gap:4px;text-align:center}.rv-exact .rv-revision{font-size:11px}}
@media(max-width:800px){body:has(.rv-exact) .container{padding:12px 16px 50px}.rv-exact .rv-dashboard-grid{grid-template-columns:1fr}.rv-exact .rv-dashboard-aside{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.rv-exact .rv-hero-image{width:42%;opacity:.8}.rv-exact .hero-status{right:12px;top:16px}.rv-exact .rv-hero-copy{margin-left:0}.rv-exact .portal-hero .page-title{font-size:30px}.rv-exact .rv-hero-content{padding:15px 22px}.rv-exact .rv-dashboard-extras{grid-template-columns:1fr 1fr}}
@media(max-width:580px){.rv-exact .portal-hero{height:200px}.rv-exact .rv-hero-image{width:53%;opacity:.35}.rv-exact .hero-status{position:static;margin-top:10px;font-size:11px;padding:7px 11px}.rv-exact .rv-hero-copy{margin-top:12px}.rv-exact .rv-dashboard-aside,.rv-exact .rv-dashboard-extras{grid-template-columns:1fr}.rv-exact .rv-dashboard-main .dash-card,.rv-exact .dash-card{padding:18px}.rv-exact .rv-progress-value{font-size:38px}.rv-exact .rv-revision{width:auto}.rv-exact .rv-count-icon{width:34px;height:34px}.rv-exact .progress-overview-count{padding:10px 4px}.rv-exact .progress-overview-count strong{font-size:21px}.rv-exact .progress-overview-count span{font-size:10px}.rv-exact .nav{justify-content:flex-start}.rv-exact .section-label h2{font-size:19px}}
</style>

<div class="ravion-premium rv-exact">

<section class="portal-hero" aria-label="Project introduction">
    <div class="rv-hero-image" aria-hidden="true"></div>
    <div class="rv-hero-content">
      <a class="rv-hero-back" href="{{ route('client.projects.index') }}">← &nbsp;All Projects</a>
      <div class="rv-hero-copy"><div class="eyebrow">Your project workspace</div><h1 class="page-title">{{ $project['project_name'] }}</h1><p class="lead">Project Reference: {{ $project['project_code'] }}</p></div>
      <span class="hero-status"><span class="hero-dot"></span>Secure access verified</span>
    </div>
</section>
@include('client-control.partials.project-navigation')
<div class="info-note" role="status"><svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8"><use href="#ri-shield"/></svg><span><strong>Verified information, delivered securely.</strong> Project updates and documents appear here only after Ravion completes the required checks, approvals and publication.</span></div>
<div class="section-label"><h2>Project at a glance</h2><span class="status">{{ $publishedProgress ? 'Progress published' : 'Publication pending' }}</span></div>
<div class="dashboard-grid rv-dashboard-grid">
<div class="rv-dashboard-main">
<section id="published-progress" class="card dash-card wide"><div class="card-heading"><span class="icon-tile"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><use href="#ri-chart"/></svg></span><div><h3 class="section-title">Progress & milestones</h3><div class="card-subtitle">Verified construction performance</div></div></div>@if($publishedProgress)
<div class="rv-progress-top"><div class="rv-progress-topline"><div><div class="rv-progress-stat"><div class="rv-progress-value">{{ number_format($publishedProgress->overall_completion_percentage, 2) }}%</div><div class="card-subtitle">Overall construction progress</div></div></div><div class="rv-revision"><span class="rv-calendar-icon">▦</span><span><strong>Published Revision {{ $publishedProgress->revision }}</strong><small>{{ $publishedProgress->published_at }}</small></span></div></div><div class="rv-overall-track" role="progressbar" aria-label="Overall construction progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ min(100, max(0, $publishedProgress->overall_completion_percentage)) }}"><span style="width:{{ min(100, max(0, $publishedProgress->overall_completion_percentage)) }}%"></span></div></div>
<div class="published-progress-stages" aria-label="Published construction stages">
@php
    // Work exclusively from the approved, published revision; never read live ERP progress here.
    $publishedStages = json_decode($publishedProgress->stage_snapshot ?? '[]', true);
    $publishedStages = is_array($publishedStages) ? array_values($publishedStages) : [];
    $groupedStages = ['completed' => [], 'in_progress' => [], 'upcoming' => []];
    foreach ($publishedStages as $stageIndex => $stage) {
        if (!is_array($stage)) continue;
        $completion = min(100, max(0, (float) ($stage['completion_percentage'] ?? 0)));
        $stage['_display_number'] = $stageIndex + 1;
        $stage['_display_completion'] = $completion;
        // Until a PMO-controlled published stage status exists, group by measurable completion.
        $group = $completion >= 100 ? 'completed' : ($completion > 0 ? 'in_progress' : 'upcoming');
        $groupedStages[$group][] = $stage;
    }
    $stageCount = count($groupedStages['completed']) + count($groupedStages['in_progress']) + count($groupedStages['upcoming']);
@endphp
@if($stageCount)
<div class="published-stage-heading"><strong>Construction journey</strong><span>{{ $stageCount }} published stages · Select to view tasks</span></div>
<div class="progress-overview-counts" aria-label="Stage completion summary">
    <div class="progress-overview-count"><span class="rv-count-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 4 4L19 6"/></svg></span><div><strong>{{ count($groupedStages['completed']) }}</strong><span>Completed</span></div></div>
    <div class="progress-overview-count"><span class="rv-count-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18h18M5 18v-5a7 7 0 0 1 14 0v5M12 6V4"/></svg></span><div><strong>{{ count($groupedStages['in_progress']) }}</strong><span>In progress</span></div></div>
    <div class="progress-overview-count"><span class="rv-count-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 10h18"/></svg></span><div><strong>{{ count($groupedStages['upcoming']) }}</strong><span>Upcoming</span></div></div>
</div>
@foreach(['completed' => 'Completed stages', 'in_progress' => 'In-progress stages'] as $groupKey => $groupTitle)
<section class="progress-group" data-group="{{ $groupKey }}" aria-label="{{ $groupTitle }}">
    <div class="progress-group-heading"><h4>{{ $groupTitle }}</h4><small>{{ count($groupedStages[$groupKey]) }} {{ count($groupedStages[$groupKey]) === 1 ? 'stage' : 'stages' }}</small></div>
    @forelse($groupedStages[$groupKey] as $stage)
        <details class="published-stage">
<summary class="published-stage-summary">
    <span class="published-stage-number">{{ $stage['_display_number'] }}</span>
    <span class="published-stage-main"><span class="published-stage-name">{{ $stage['name'] ?? $stage['stage_name'] ?? 'Construction stage' }}</span><span class="published-stage-track"><span style="width:{{ $stage['_display_completion'] }}%"></span></span></span>
    <span class="published-stage-metric"><strong>{{ number_format($stage['_display_completion'], 1) }}%</strong><small>Complete</small></span>
    <span class="published-stage-chevron" aria-hidden="true">⌄</span>
</summary>
<div class="published-stage-body">
@php
    $stageTasks = $stage['tasks'] ?? $stage['stage_tasks'] ?? [];
    $stageTasks = is_array($stageTasks) ? array_values($stageTasks) : [];
@endphp
@forelse($stageTasks as $task)
    @php $taskCompletion = min(100, max(0, (float) ($task['completion_percentage'] ?? 0))); @endphp
    <div class="published-task"><span>{{ $task['name'] ?? $task['task_name'] ?? 'Task' }}</span><strong>{{ number_format($taskCompletion, 1) }}%</strong></div>
@empty
    <p class="published-task-empty">No individual task details are included in this published revision.</p>
@endforelse
</div>
</details>
    @empty
        <div class="progress-group-empty">{{ $groupKey === 'completed' ? 'No stages have reached 100% in this published revision.' : 'No stages currently show partial measurable progress in this published revision.' }}</div>
    @endforelse
</section>
@endforeach
<section class="progress-group" data-group="upcoming" aria-label="Upcoming stages">
    <div class="progress-group-heading"><h4>Upcoming / Not started</h4><small>{{ count($groupedStages['upcoming']) }} {{ count($groupedStages['upcoming']) === 1 ? 'stage' : 'stages' }}</small></div>
    @if(count($groupedStages['upcoming']))
    <details class="progress-upcoming">
        <summary><span>View remaining construction stages</span><small>{{ count($groupedStages['upcoming']) }} stages · Expand / collapse</small></summary>
        <div class="progress-upcoming-content">
            @foreach($groupedStages['upcoming'] as $stage)
                <details class="published-stage">
<summary class="published-stage-summary">
    <span class="published-stage-number">{{ $stage['_display_number'] }}</span>
    <span class="published-stage-main"><span class="published-stage-name">{{ $stage['name'] ?? $stage['stage_name'] ?? 'Construction stage' }}</span><span class="published-stage-track"><span style="width:{{ $stage['_display_completion'] }}%"></span></span></span>
    <span class="published-stage-metric"><strong>{{ number_format($stage['_display_completion'], 1) }}%</strong><small>Complete</small></span>
    <span class="published-stage-chevron" aria-hidden="true">⌄</span>
</summary>
<div class="published-stage-body">
@php
    $stageTasks = $stage['tasks'] ?? $stage['stage_tasks'] ?? [];
    $stageTasks = is_array($stageTasks) ? array_values($stageTasks) : [];
@endphp
@forelse($stageTasks as $task)
    @php $taskCompletion = min(100, max(0, (float) ($task['completion_percentage'] ?? 0))); @endphp
    <div class="published-task"><span>{{ $task['name'] ?? $task['task_name'] ?? 'Task' }}</span><strong>{{ number_format($taskCompletion, 1) }}%</strong></div>
@empty
    <p class="published-task-empty">No individual task details are included in this published revision.</p>
@endforelse
</div>
</details>
            @endforeach
        </div>
    </details>
    @else
    <div class="progress-group-empty">No upcoming stages in this published revision.</div>
    @endif
</section>
<p class="published-task-empty" style="margin-top:14px">Stage groups reflect measurable completion in the published revision. A stage at 0% may not yet have a separately verified work-start status.</p>
@else
<p class="published-task-empty">No stage details are included in this published revision.</p>
@endif
</div>
@else<div class="placeholder-chart">Progress visualisations will appear once verified milestones and approved measurements are published.</div><div class="mini-divider"></div><span class="status">Awaiting verified data</span>@endif</section>
</div>
<aside class="rv-dashboard-aside">
<section class="card dash-card narrow"><div class="card-heading"><span class="icon-tile blue"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><use href="#ri-info"/></svg></span><div><h3 class="section-title">Project overview</h3><div class="card-subtitle">Official project summary</div></div></div><p class="empty">Your approved project overview will be available after internal review and publication.</p><span class="status">Publication pending</span></section>
<section class="card dash-card narrow"><div class="card-heading"><span class="icon-tile amber"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><use href="#ri-file"/></svg></span><div><h3 class="section-title">Project summary</h3><div class="card-subtitle">Your project at a glance</div></div></div><div class="rv-summary"><div class="rv-summary-row"><span><b class="rv-row-icon">▥</b> Project name</span><strong>{{ $project['project_name'] }}</strong></div><div class="rv-summary-row"><span><b class="rv-row-icon">◇</b> Project code</span><strong>{{ $project['project_code'] }}</strong></div><div class="rv-summary-row"><span><b class="rv-row-icon">▤</b> Total stages</span><strong>{{ $publishedProgress ? $stageCount : 'Awaiting publication' }}</strong></div><div class="rv-summary-row"><span><b class="rv-row-icon">◎</b> Overall progress</span><strong>{{ $publishedProgress ? number_format($publishedProgress->overall_completion_percentage, 2).'%' : 'Awaiting publication' }}</strong></div></div></section>
</aside>
</div>
<div class="rv-dashboard-extras">
<section class="card dash-card"><div class="card-heading"><span class="icon-tile amber"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><use href="#ri-file"/></svg></span><div><h3 class="section-title">Latest project update</h3><div class="card-subtitle">Approved project communication</div></div></div><p class="empty">No approved project update has been published yet. Once released, the latest verified update will be shown here.</p><span class="status">Awaiting publication</span></section>
<section class="card dash-card"><div class="card-heading"><span class="icon-tile violet"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><use href="#ri-camera"/></svg></span><div><h3 class="section-title">Documents & project proof</h3><div class="card-subtitle">Approved drawings, quality and photos</div></div></div><p class="empty">Current approved drawing revisions, quality evidence and curated site photographs will appear here when published.</p><span class="status">Awaiting publication</span></section>
</div>
</div>
@endsection
