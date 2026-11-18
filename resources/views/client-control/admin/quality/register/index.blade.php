@extends('client-control.layout')
@section('title', 'Quality & Testing')
@section('content')
<style>
.rv-quality{max-width:1120px;margin:auto;color:#102b42}.rv-quality .qhero{background:linear-gradient(110deg,#173c67,#087f8c);color:white;padding:28px;border-radius:19px;margin-bottom:20px}.rv-quality .qhero h1{font-size:27px;font-weight:800;margin:8px 0}.rv-quality .qhero a{color:#e3f8fa}.rv-quality .qcard{background:white;border:1px solid #e0eaf4;border-radius:15px;padding:19px;margin-bottom:12px;box-shadow:0 5px 18px #193d6b0d}.rv-quality .qrow{display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap}.rv-quality .qmuted{color:#637c91;font-size:12px}.rv-quality .qtitle{font-size:17px;font-weight:800;color:#102b4e;margin:6px 0}.rv-quality .qtag{display:inline-block;padding:5px 10px;border-radius:20px;background:#e5f5f3;color:#08766e;font-size:12px;font-weight:700}.rv-quality .qbutton{display:inline-block;background:#102b4e;color:#fff;padding:9px 15px;border-radius:9px;text-decoration:none;margin-top:12px}.rv-quality .qbutton:hover{background:#087f8c}
</style>
<div class="rv-quality">
 <header class="qhero"><a href="{{ route('client.projects.show', $project->id) }}">← Project dashboard</a><h1>Quality & Testing</h1><div>{{ $project->project_name }} · {{ $project->project_code }}</div><p style="margin-top:9px;font-size:13px">Only approved, published and currently available quality reports are shown.</p></header>
 <div class="qcard"><strong>{{ $results->total() }} published {{ $results->total() === 1 ? 'report' : 'reports' }}</strong><div class="qmuted">Test records released for client viewing</div></div>
 @forelse($results as $r)
 <article class="qcard"><div class="qrow"><div><div class="qmuted">{{ $r->test_code }} · {{ $r->category }}</div><h2 class="qtitle">{{ $r->test_name }}</h2><div class="qmuted">Test date: {{ $r->tested_on }} · {{ $r->location_label ?: 'Location not specified' }}</div></div><div><span class="qtag">{{ $r->result }}</span></div></div><a class="qbutton" href="{{ route('client.quality.show', [$project->id, $r->id]) }}">View published report</a></article>
 @empty <div class="qcard">No quality reports have been published for client viewing yet.</div> @endforelse
 {{ $results->links() }}
</div>
@endsection
