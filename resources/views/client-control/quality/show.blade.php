@extends('client-control.layout')
@section('title', 'Quality Test Report | ' . $project->project_name)
@section('content')
<style>
body:has(.rv-quality){background:#f1f6ff}body:has(.rv-quality) .container{max-width:1600px;padding:16px 30px 65px}body:has(.rv-quality) .topbar{background:linear-gradient(100deg,#fff,#edf5ff)}
.rv-quality{color:#102b4e;font-size:14px}.rv-quality *{box-sizing:border-box}.rv-quality .q-hero{position:relative;overflow:hidden;border-radius:21px;min-height:166px;padding:24px 42px;background:linear-gradient(105deg,#244e7e,#0e567d 77%,#0b7284);color:#fff}.rv-quality .q-hero:after{content:'';position:absolute;right:0;top:0;bottom:0;width:33%;background:url('{{ asset('images/client-control/ravion-hero-illustration.jpg') }}') center/cover;clip-path:polygon(20% 0,100% 0,100% 100%,0 100%);opacity:.9}.rv-quality .q-hero>*{position:relative;z-index:1}.rv-quality .q-back{color:#fff;text-decoration:none;font-weight:700;font-size:12px}.rv-quality .q-eyebrow{margin:17px 0 5px;color:#d9eaff;text-transform:uppercase;letter-spacing:.07em;font-weight:700;font-size:11px}.rv-quality .q-hero h1{color:#fff;font-size:34px;margin:0 0 7px;line-height:1.15}.rv-quality .q-hero p{margin:0;color:#eef6ff}.rv-quality .q-nav{display:flex;align-items:center;gap:8px;overflow-x:auto;margin:12px 0 22px;padding:5px 0}.rv-quality .q-nav a{white-space:nowrap;text-decoration:none;color:#294977;font-weight:700;padding:10px 18px;border-radius:24px}.rv-quality .q-nav a.active{color:#fff;background:linear-gradient(135deg,#245e93,#0f315d);box-shadow:0 4px 10px #17456b2e}.rv-quality .q-panel{background:#fff;border:1px solid #e0eaf4;border-radius:20px;padding:25px 28px;box-shadow:0 9px 28px #1b436911}.rv-quality .q-heading{display:flex;align-items:center;justify-content:space-between;gap:15px;margin:0 0 22px}.rv-quality .q-heading h2{font-size:22px;margin:0;color:#102b4e}.rv-quality .q-sub{font-size:13px;color:#607a98;margin-top:5px}.rv-quality .q-tag{display:inline-flex;align-items:center;border-radius:30px;padding:7px 12px;background:#e4f6ed;color:#08744b;font-weight:800;font-size:12px;white-space:nowrap}.rv-quality .q-item{display:flex;align-items:center;justify-content:space-between;gap:18px;border:1px solid #e1eaf4;border-radius:15px;padding:18px 20px;margin-bottom:12px;background:linear-gradient(130deg,#fff,#f7fbff)}.rv-quality .q-item h3{font-size:17px;margin:6px 0;color:#102b4e}.rv-quality .q-meta{font-size:12px;color:#607a98;line-height:1.8}.rv-quality .q-code{font-weight:800;color:#087f8c;font-size:12px}.rv-quality .q-link{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;background:#174e80;color:#fff!important;border-radius:10px;padding:11px 17px;font-weight:800;white-space:nowrap}.rv-quality .q-link:hover{background:#0e365e}.rv-quality .q-empty{padding:48px 15px;text-align:center;background:#f7fbff;border:1px dashed #c9daed;border-radius:16px;color:#607a98;line-height:1.8}.rv-quality .q-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.rv-quality .q-field{background:#f5f9fe;border:1px solid #e1eaf4;border-radius:13px;padding:15px 17px;min-width:0}.rv-quality .q-field small{display:block;color:#607a98;font-size:12px;margin-bottom:7px}.rv-quality .q-field strong{display:block;color:#102b4e;overflow-wrap:anywhere}.rv-quality .q-section{margin:27px 0 13px;font-size:17px;color:#102b4e}.rv-quality .q-notes{white-space:pre-wrap;overflow-wrap:anywhere;background:#f5f9fe;border:1px solid #e1eaf4;border-radius:13px;padding:17px;line-height:1.7}.rv-quality .q-evidence{display:flex;justify-content:space-between;align-items:center;gap:15px;border:1px solid #e1eaf4;border-radius:13px;padding:15px 17px;margin:10px 0}.rv-quality .q-evidence-name{font-weight:700;overflow-wrap:anywhere}.rv-quality .q-note{font-size:12px;color:#607a98;margin-top:18px}.rv-quality .q-pages{margin-top:20px}.rv-quality .q-pages nav{max-width:100%;overflow-x:auto}@media(max-width:700px){body:has(.rv-quality) .container{padding:12px 15px 45px}.rv-quality .q-hero{padding:22px;min-height:165px}.rv-quality .q-hero:after{width:55%;opacity:.3}.rv-quality .q-hero h1{font-size:26px}.rv-quality .q-panel{padding:18px}.rv-quality .q-item{align-items:flex-start;flex-direction:column;padding:16px}.rv-quality .q-link{width:100%}.rv-quality .q-grid{grid-template-columns:1fr}.rv-quality .q-heading{align-items:flex-start;flex-direction:column}.rv-quality .q-evidence{align-items:stretch;flex-direction:column}}

/* Consistent navigation and project hero from client-control/home.blade.php */
body:has(.rv-quality) .top-inner{max-width:1600px;padding:12px 30px;min-height:68px}
body:has(.rv-quality) .brand{display:flex;align-items:center;gap:12px;font-size:24px;letter-spacing:-.9px;color:#10264e}
body:has(.rv-quality) .brand .rv-brand-portal{max-width:none}
body:has(.rv-quality) .signout{border-radius:10px;color:#163d65;border-color:#d7e5f7}
.rv-quality .q-hero{height:176px;min-height:176px;border-radius:21px;background:linear-gradient(100deg,#244e7e 0%,#0e567d 76%,#0b7284 100%);box-shadow:0 10px 24px #123a6c20;padding:17px 42px}
.rv-quality .q-hero:after{width:34%;opacity:1}
.rv-quality .q-hero .q-back{font-size:13px}
.rv-quality .q-hero .q-eyebrow{margin:8px 0 0 125px;font-size:12px;letter-spacing:.7px}
.rv-quality .q-hero h1{font-size:38px;letter-spacing:-1px;margin:8px 0 10px 125px}
.rv-quality .q-hero p{font-size:15px;margin-left:125px}
.rv-quality .q-home-nav{margin:10px 0 14px;padding:5px 15px 8px;border:0;display:flex;align-items:center;justify-content:space-between;gap:10px;overflow-x:auto;border-radius:14px}
.rv-quality .q-home-nav .nav-item{display:flex;align-items:center;gap:8px;padding:10px 15px;border-radius:22px;color:#294977;font-size:13px;font-weight:700;white-space:nowrap;text-decoration:none;flex:none}
.rv-quality .q-home-nav .nav-item.active{background:linear-gradient(135deg,#245e93,#0f315d);color:#fff;box-shadow:0 4px 9px #17456b2e}
.rv-quality .q-home-nav .nav-item.disabled{color:#8ba0bd;cursor:not-allowed}
.rv-quality .q-home-nav .nav-icon{width:19px;height:19px;flex:none}
@media(max-width:1100px){.rv-quality .q-hero .q-eyebrow,.rv-quality .q-hero h1,.rv-quality .q-hero p{margin-left:45px}}
@media(max-width:800px){body:has(.rv-quality) .top-inner{padding:12px 16px}.rv-quality .q-hero:after{width:42%;opacity:.8}.rv-quality .q-hero .q-eyebrow,.rv-quality .q-hero h1,.rv-quality .q-hero p{margin-left:0}.rv-quality .q-hero h1{font-size:30px}.rv-quality .q-hero{padding:15px 22px}}
@media(max-width:580px){.rv-quality .q-hero{height:200px;min-height:200px}.rv-quality .q-hero:after{width:53%;opacity:.35}.rv-quality .q-home-nav{justify-content:flex-start}.rv-quality .q-hero h1{font-size:30px}}
</style><div class="rv-quality">
<section class="q-hero">
<a class="q-back" href="{{ route('client.projects.show', $project->id) }}">← Back to project dashboard</a>
<div class="q-eyebrow">Your project workspace</div>
<h1>{{ $project->project_name }}</h1>
<p>Project Reference: {{ $project->project_code }}</p>
</section>
@include('client-control.partials.project-navigation')
<section class="q-panel">
<div class="q-heading"><div><div class="q-code">{{ $report->test_code }} · {{ $report->category }}</div><h2 style="margin-top:8px">{{ $report->test_name }}</h2><div class="q-sub">Published quality test report</div></div><span class="q-tag">{{ $report->result ?: 'Result recorded' }}</span></div>
<div class="q-grid">
<div class="q-field"><small>Test date</small><strong>{{ $report->tested_on ? \Illuminate\Support\Carbon::parse($report->tested_on)->format('d M Y') : 'Not specified' }}</strong></div>
<div class="q-field"><small>Published on</small><strong>{{ $report->published_at ? \Illuminate\Support\Carbon::parse($report->published_at)->format('d M Y') : 'Not specified' }}</strong></div>
<div class="q-field"><small>Location</small><strong>{{ $report->location_label ?: 'Not specified' }}</strong></div>
<div class="q-field"><small>Construction stage</small><strong>{{ $report->stage_label ?: 'Not specified' }}</strong></div>
<div class="q-field"><small>Result</small><strong>{{ $report->result ?: 'Not specified' }}</strong></div>
<div class="q-field"><small>Test reference</small><strong>{{ $report->test_code }}</strong></div>
</div>
<h3 class="q-section">Observations</h3>
<div class="q-notes">{{ $report->observations ?: 'No additional observations were published for this test.' }}</div>
<h3 class="q-section">Supporting evidence</h3>
@forelse($evidence as $file)
<div class="q-evidence"><div><div class="q-evidence-name">{{ $file->original_name ?: 'Test evidence' }}</div><div class="q-meta">{{ $file->mime_type === 'application/pdf' ? 'PDF document' : 'Image' }}@if($file->size_bytes) · {{ number_format($file->size_bytes / 1024, 1) }} KB @endif</div></div><a class="q-link" target="_blank" rel="noopener noreferrer" href="{{ route('client.quality.evidence', ['project' => $project->id, 'result' => $report->id, 'evidence' => $file->id]) }}">View evidence</a></div>
@empty
<div class="q-empty">No supporting evidence is available for this report.</div>
@endforelse
<p class="q-note">Evidence is available for viewing only while this report remains published and your project access is valid. Browser viewing cannot technically prevent saving or copying.</p>
<div style="margin-top:22px"><a href="{{ route('client.quality.index', $project->id) }}" style="color:#174e80;font-weight:800;text-decoration:none">← All quality reports</a></div>
</section>
</div>
@endsection
