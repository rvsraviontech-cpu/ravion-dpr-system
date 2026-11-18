@extends('client-control.layout')
@section('title', 'QA/QC Project Register')
@section('content')
<style>
.rv-qr{max-width:1180px;margin:0 auto;color:#17324a}.rv-qr *{box-sizing:border-box}.rv-qr .hero{background:linear-gradient(110deg,#173c67,#087f8c);color:#fff;padding:24px;border-radius:16px;margin-bottom:18px}.rv-qr h1{font-size:26px;font-weight:800;margin:0 0 7px}.rv-qr h2{font-size:18px;font-weight:750;margin:0 0 14px}.rv-qr .sub{font-size:13px;opacity:.9}.rv-qr .card{background:#fff;border:1px solid #dde7ef;border-radius:14px;padding:20px;margin-bottom:16px;box-shadow:0 4px 18px #173c6709}.rv-qr .stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.rv-qr .stat{padding:15px;background:#f4f8fb;border-radius:11px}.rv-qr .stat b{font-size:26px;display:block}.rv-qr .stat small{color:#5d7385}.rv-qr .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.rv-qr label{display:block;font-size:13px;font-weight:700;margin-bottom:5px}.rv-qr input,.rv-qr select,.rv-qr textarea{width:100%;border:1px solid #cbd9e5;border-radius:8px;padding:10px;background:#fff;color:#17324a}.rv-qr textarea{min-height:80px}.rv-qr .wide{grid-column:1/-1}.rv-qr .btn{border:0;border-radius:8px;padding:9px 13px;background:#173c67;color:white;cursor:pointer;font-weight:650;text-decoration:none;display:inline-block}.rv-qr .btn.secondary{background:#e6eef5;color:#173c67}.rv-qr .btn.warn{background:#9d4a20}.rv-qr .btn:disabled{opacity:.6}.rv-qr .muted{font-size:12px;color:#61778b}.rv-qr .entry{border:1px solid #e2eaf1;border-radius:11px;padding:16px;margin-top:12px}.rv-qr .entry-head{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}.rv-qr .tag{font-size:12px;padding:5px 9px;border-radius:99px;background:#e8f4f2;color:#086b66;display:inline-block}.rv-qr .actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:13px;align-items:center}.rv-qr .actions form{display:flex;gap:7px;align-items:center;flex-wrap:wrap}.rv-qr .actions input{width:210px}.rv-qr .errors{padding:13px;border-radius:9px;background:#fff1f0;color:#9a2424;margin-bottom:14px}.rv-qr .success{padding:13px;border-radius:9px;background:#ecf8ef;color:#206b39;margin-bottom:14px}@media(max-width:680px){.rv-qr .grid,.rv-qr .stats{grid-template-columns:1fr}.rv-qr .card{padding:15px}.rv-qr .actions input{width:100%}}
</style>
<div class="rv-qr">
  <div class="hero"><h1>QA/QC · Project Test Register</h1><div class="sub">Submit construction quality tests, review evidence and control what is published to clients.</div></div>
  @if(session('success'))<div class="success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="errors"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <div class="card">
    <form method="GET" action="{{ route('client-control.admin.quality.register.index') }}">
      <label for="qr_filter_project">Filter by project</label>
      <div style="display:flex;gap:10px;flex-wrap:wrap"><select id="qr_filter_project" name="project_id" style="flex:1;min-width:200px"><option value="">All projects</option>@foreach($projects as $p)<option value="{{ $p->id }}" @selected((int)$projectId === (int)$p->id)>{{ $p->project_name }} ({{ $p->project_code }})</option>@endforeach</select><button class="btn" type="submit">Apply filter</button><a class="btn secondary" href="{{ route('client-control.admin.quality.register.index') }}">Clear</a></div>
    </form>
  </div>
  <div class="stats" style="margin-bottom:16px"><div class="stat"><b>{{ (int)($counts->total ?? 0) }}</b><small>Total test results</small></div><div class="stat"><b>{{ (int)($counts->pending ?? 0) }}</b><small>Submitted / verified</small></div><div class="stat"><b>{{ (int)($counts->published ?? 0) }}</b><small>Published to clients</small></div></div>
  @if(auth()->user()?->hasPermission('client_control.quality.project.manage') && auth()->user()?->hasPermission('client_control.quality.results.create'))
  <div class="card"><h2>Submit a quality test</h2>
    <form method="POST" action="{{ route('client-control.admin.quality.register.assign') }}" enctype="multipart/form-data">@csrf
      <div class="grid">
        <div><label for="qr_project">Project *</label><select id="qr_project" name="project_id" required><option value="">Select project</option>@foreach($projects as $p)<option value="{{ $p->id }}" @selected((string)old('project_id', $projectId ?: '') === (string)$p->id)>{{ $p->project_name }} ({{ $p->project_code }})</option>@endforeach</select></div>
        <div><label for="qr_test">Standard test *</label><select id="qr_test" name="test_master_id" required><option value="">Select test</option>@foreach($masters as $m)<option value="{{ $m->id }}" @selected((string)old('test_master_id') === (string)$m->id)>{{ $m->code }} · {{ $m->name }} ({{ $m->category }})</option>@endforeach</select></div>
        <div><label for="qr_date">Test date *</label><input id="qr_date" type="date" name="tested_on" value="{{ old('tested_on', date('Y-m-d')) }}" required></div>
        <div><label for="qr_result">Test result *</label><select id="qr_result" name="result" required><option value="">Select result</option>@foreach(['Passed','Failed','Inconclusive'] as $choice)<option value="{{ $choice }}" @selected(old('result') === $choice)>{{ $choice }}</option>@endforeach</select></div>
        <div><label for="qr_location">Location / element</label><input id="qr_location" name="location_label" maxlength="255" value="{{ old('location_label') }}" placeholder="e.g. Block A, Level 2"></div>
        <div><label for="qr_evidence">Evidence files * (1–8)</label><input id="qr_evidence" type="file" name="evidence[]" accept=".pdf,.jpg,.jpeg,.png,.webp" multiple required><div class="muted">PDF or images, up to 10 MB per file.</div></div>
        <div class="wide"><label for="qr_observations">Observations</label><textarea id="qr_observations" name="observations" maxlength="10000">{{ old('observations') }}</textarea></div>
      </div><button class="btn" style="margin-top:14px" type="submit">Submit test for review</button>
    </form>
  </div>
  @endif
  <div class="card"><h2>Test results register</h2>
    @forelse($register as $r)
      @php
        $isPublished = $r->status === 'Approved' && $r->published_at && !$r->withdrawn_at;
        $can = fn($permission) => auth()->user()?->hasPermission($permission);
      @endphp
      <div class="entry"><div class="entry-head"><div><strong>{{ $r->test_code }} · {{ $r->test_name }}</strong><div class="muted">{{ $r->project_name }} · {{ $r->category }} · {{ $r->tested_on }} · {{ $r->location_label ?: 'No location specified' }}</div></div><div><span class="tag">{{ $r->status }}</span> @if($isPublished)<span class="tag">Published</span>@elseif($r->withdrawn_at)<span class="tag">Withdrawn</span>@endif</div></div>
        <p style="margin:9px 0"><strong>Result:</strong> {{ $r->result }} @if($r->observations) · {{ $r->observations }} @endif</p>
        @if(($evidence[$r->id] ?? collect())->isNotEmpty())<div class="muted">Evidence: @foreach($evidence[$r->id] as $file)<a href="{{ route('client-control.admin.quality.register.evidence', ['evidence' => $file->id]) }}" target="_blank" rel="noopener noreferrer">{{ $file->original_name ?: 'File '.$file->id }}</a>@unless($loop->last) · @endunless @endforeach</div>@endif
        <div class="actions">
          @if($r->status === 'Returned' && $can('client_control.quality.results.create'))<form method="POST" action="{{ route('client-control.admin.quality.register.transition', ['result' => $r->id, 'action' => 'submit']) }}">@csrf<button class="btn" type="submit">Resubmit</button></form>@endif
          @if($r->status === 'Submitted' && $can('client_control.quality.results.verify'))<form method="POST" action="{{ route('client-control.admin.quality.register.transition', ['result' => $r->id, 'action' => 'verify']) }}">@csrf<button class="btn" type="submit">Verify</button></form>@endif
          @if($r->status === 'Verified' && $can('client_control.quality.results.approve'))<form method="POST" action="{{ route('client-control.admin.quality.register.transition', ['result' => $r->id, 'action' => 'approve']) }}">@csrf<button class="btn" type="submit">Approve</button></form>@endif
          @if(in_array($r->status, ['Submitted','Verified'], true) && $can('client_control.quality.results.verify'))<form method="POST" action="{{ route('client-control.admin.quality.register.transition', ['result' => $r->id, 'action' => 'return']) }}">@csrf<input name="note" required maxlength="2000" placeholder="Reason for return *"><button class="btn warn" type="submit">Return</button></form>@endif
          @if($r->status === 'Approved' && !$isPublished && $can('client_control.quality.publish'))<form method="POST" action="{{ route('client-control.admin.quality.register.transition', ['result' => $r->id, 'action' => 'publish']) }}">@csrf<button class="btn" type="submit">Publish to client</button></form>@endif
          @if($isPublished && $can('client_control.quality.withdraw'))<form method="POST" action="{{ route('client-control.admin.quality.register.transition', ['result' => $r->id, 'action' => 'withdraw']) }}">@csrf<input name="note" required maxlength="2000" placeholder="Withdrawal reason *"><button class="btn warn" type="submit">Withdraw</button></form>@endif
        </div>
      </div>
    @empty
      <p class="muted">No quality test results found for the selected project.</p>
    @endforelse
    <div style="margin-top:15px">{{ $register->links() }}</div>
  </div>
</div>
@endsection
