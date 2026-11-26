@extends('client-control.project-layout', ['workspaceProject' => $project])
@section('title', 'Project Documents')
@section('project-content')
<style>
.rv-vault{--navy:#173c67;--teal:#087f8c;color:#19344b}.rv-vault .rv-head{background:linear-gradient(105deg,#173c67,#087f8c);color:white;border-radius:20px;padding:29px 32px;margin-bottom:19px}.rv-vault .rv-head a{color:#e1f6fc;font-size:13px;font-weight:700}.rv-vault .rv-head h1{font-size:clamp(24px,3vw,34px);margin:13px 0 5px;letter-spacing:-.6px}.rv-vault .rv-head p{margin:0;color:#e3f4fa;font-size:13px}.rv-vault .rv-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:0 0 16px}.rv-vault .rv-toolbar h2{font-size:18px;margin:0}.rv-vault .rv-count{font-size:12px;color:#59758b;background:#edf5f9;padding:8px 12px;border-radius:99px}.rv-vault .rv-groups{display:grid;gap:16px}.rv-vault .rv-group{border:1px solid #e1eaf2;background:#fff;border-radius:17px;padding:21px;box-shadow:0 7px 20px #19344b0a}.rv-vault .rv-group h3{font-size:15px;margin:0 0 14px;color:#173c67}.rv-vault .rv-file{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:15px 0;border-top:1px solid #edf2f6}.rv-vault .rv-file:first-of-type{border-top:0}.rv-vault .rv-file-name{font-weight:750;font-size:14px;overflow-wrap:anywhere}.rv-vault .rv-meta{font-size:12px;color:#6b8093;line-height:1.7;margin-top:5px}.rv-vault .rv-download{flex:none;display:inline-block;background:#087f8c;color:#fff;padding:10px 16px;border-radius:10px;font-size:12px;font-weight:800}.rv-vault .rv-download:hover{background:#076b76}.rv-vault .rv-empty{border:1px dashed #cddde8;border-radius:16px;padding:32px;background:#fff;text-align:center;color:#587185;line-height:1.7}@media(max-width:600px){.rv-vault .rv-head{padding:23px 19px}.rv-vault .rv-group{padding:16px}.rv-vault .rv-file{align-items:flex-start;flex-direction:column}.rv-vault .rv-download{width:100%;text-align:center}}
 .rv-vault .rv-actions{display:flex;align-items:center;gap:9px;flex:none}
.rv-vault .rv-action{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-width:44px;min-height:42px;padding:9px 12px;border-radius:11px;border:1px solid #d4e4ee;background:#f4f9fc;color:#173c67;text-decoration:none;font-size:12px;font-weight:750;transition:background .15s,border-color .15s}
.rv-vault .rv-action:hover,.rv-vault .rv-action:focus-visible{background:#e2f4f6;border-color:#087f8c;outline-offset:3px}
.rv-vault .rv-action svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.rv-vault .rv-action.download{background:#087f8c;color:#fff;border-color:#087f8c}
.rv-vault .rv-action.download:hover{background:#076b76}
@media(max-width:600px){.rv-vault .rv-actions{width:100%}.rv-vault .rv-action{flex:1}.rv-vault .rv-download{width:auto}}

.rv-vault .rv-project-hero{position:relative;overflow:hidden;height:176px;background:linear-gradient(100deg,#244e7e 0%,#0e567d 76%,#0b7284 100%);padding:17px 42px;border-radius:21px;margin-bottom:0}
.rv-vault .rv-project-hero:after{content:'';position:absolute;right:0;top:0;bottom:0;width:34%;background:url('{{ asset('images/client-control/ravion-hero-illustration.jpg') }}') center/cover;clip-path:polygon(19% 0,100% 0,100% 100%,0 100%)}
.rv-vault .rv-project-hero>a,.rv-vault .rv-project-hero-copy{position:relative;z-index:1}
.rv-vault .rv-project-hero>a{color:#fff;font-size:13px}
.rv-vault .rv-project-hero-copy{margin:8px 0 0 125px}
.rv-vault .rv-project-eyebrow{color:#dceeff;font-size:12px;font-weight:750;letter-spacing:.7px}
.rv-vault .rv-project-hero h1{font-size:38px;line-height:1.1;letter-spacing:-1px;margin:8px 0 10px;color:#fff}
.rv-vault .rv-project-hero p{font-size:15px;color:#f1f7ff}
@media(max-width:1100px){.rv-vault .rv-project-hero-copy{margin-left:45px}}
@media(max-width:800px){.rv-vault .rv-project-hero-copy{margin-left:0}.rv-vault .rv-project-hero:after{width:42%;opacity:.8}.rv-vault .rv-project-hero{padding:15px 22px}.rv-vault .rv-project-hero h1{font-size:30px}}
@media(max-width:580px){.rv-vault .rv-project-hero{height:200px}.rv-vault .rv-project-hero:after{width:53%;opacity:.35}}
</style>
<div class="rv-vault">

  <div class="rv-toolbar"><h2>Published document library</h2><span class="rv-count">{{ $documents->count() }} {{ $documents->count() === 1 ? 'document' : 'documents' }}</span></div>
  @if($documents->isEmpty())
    <div class="rv-empty"><strong>No published documents available yet.</strong><div>Approved project documents will appear here when Ravion publishes them for your access.</div></div>
  @else
    <div class="rv-groups">
      @foreach($documents->groupBy('category_name') as $category => $items)
        <section class="rv-group">
          <h3>{{ $category }} <span style="color:#8394a5;font-size:12px;font-weight:500">({{ $items->count() }})</span></h3>
          @foreach($items as $document)
            <div class="rv-file">
              <div>
                <div class="rv-file-name">{{ $document->title }}</div>
                <div class="rv-meta">{{ $document->document_number }} · Revision {{ $document->revision }} · Published {{ \Illuminate\Support\Carbon::parse($document->published_at)->format('d M Y') }}</div>
                @if($document->description)<div class="rv-meta">{{ $document->description }}</div>@endif
              </div>
              <div class="rv-actions">
                @if(in_array(strtolower((string) ($document->mime_type ?? '')), ['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'image/gif'], true))
                  <a class="rv-action" href="{{ route('client.vault.preview', ['project' => $project['id'], 'document' => $document->id]) }}" target="_blank" rel="noopener noreferrer" title="View published document" aria-label="View {{ $document->title }} in new tab">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><span>View</span>
                  </a>
                @endif
                <a class="rv-action download" href="{{ route('client.vault.download', ['project' => $project['id'], 'document' => $document->id]) }}" title="Download published document" aria-label="Download {{ $document->title }}">
                  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M4 17v3h16v-3"/></svg><span>Download</span>
                </a>
              </div>
            </div>
          @endforeach
        </section>
      @endforeach
    </div>
  @endif
</div>
@endsection
