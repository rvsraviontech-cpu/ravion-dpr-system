@extends('layouts.app')

@section('content')
@php
    $tableClass = 'wd-full-table text-sm text-left';
    $thClass = 'wd-th px-3 py-2 font-semibold text-slate-700';
    $tdClass = 'wd-td border-t border-slate-100 px-3 py-2 text-slate-700';
@endphp
<style>
/* Scoped styles: do not modify the Create form or global ERP theme. */
.wd-view { --wd-navy:#10212f; --wd-border:#dfe7f0; color:#233446; }
.wd-view .wd-heading { color:var(--wd-navy); }
.wd-view .wd-summary { border:1px solid #d8e3ed; border-top:4px solid var(--wd-navy); background:linear-gradient(110deg,#fff 65%,#f4f8fc); }
.wd-view .wd-summary .wd-field { padding:4px 15px; border-left:1px solid #e1e8f0; }
.wd-view .wd-summary .wd-field:first-child { border-left:0; }
.wd-view .wd-activity { border:1px solid #d9e3ee; box-shadow:0 3px 14px rgba(16,33,47,.055); }
.wd-view .wd-activity-head { background:#10212f; border-color:#10212f; }
.wd-view .wd-activity-head h2 { color:#fff; }
.wd-view .wd-activity-head p { color:#c7d5e1; }
.wd-view .wd-facts { background:#f5f8fc; border:1px solid #e3eaf2; border-radius:10px; padding:15px; }
.wd-view .wd-facts > div { border-left:2px solid #d9e3ee; padding-left:12px; }
.wd-view .wd-facts > div:first-child { border-left:0; padding-left:0; }
.wd-view .wd-panel { border:1px solid var(--wd-border); border-radius:10px; overflow:hidden; background:#fff; }
.wd-view .wd-panel-head { padding:11px 14px; border-bottom:1px solid var(--wd-border); display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:8px; }
.wd-view .wd-panel-head h3 { margin:0; color:#10212f; font-weight:750; font-size:14px; }
.wd-view .wd-panel--materials { border-left:4px solid #2674c7; }
.wd-view .wd-panel--materials .wd-panel-head { background:#eef5ff; }
.wd-view .wd-panel--labour { border-left:4px solid #0f8c75; }
.wd-view .wd-panel--labour .wd-panel-head { background:#edf9f5; }
.wd-view .wd-panel--machinery { border-left:4px solid #b17a22; }
.wd-view .wd-panel--machinery .wd-panel-head { background:#fff7e9; }
.wd-view .wd-panel--photos { border-left:4px solid #7855b9; }
.wd-view .wd-panel--photos .wd-panel-head { background:#f5f0ff; }
.wd-view .wd-panel--remarks { border-left:4px solid #66788a; background:#f8fafc; padding:12px 15px; }
.wd-view .wd-th { background:#f8fafc; color:#44566a; font-size:12px; border-bottom:1px solid #e1e8ef; }
.wd-view .wd-td { color:#29394a; }
.wd-view tbody tr:nth-child(even) { background:#fafcfe; }
.wd-view tbody tr:hover { background:#f0f6fc; }
/* Full-width, ruled tables. The description receives the flexible column;
   serial and numeric columns stay narrow and easy to scan. */
.wd-view .wd-panel table.wd-full-table { width:100%; table-layout:fixed; border-collapse:collapse; }
.wd-view .wd-full-table th, .wd-view .wd-full-table td { border:1px solid #d6e1ec; padding:10px 12px; vertical-align:middle; overflow-wrap:anywhere; }
.wd-view .wd-full-table th { background:#edf3fa; font-weight:700; color:#263b50; }
.wd-view .wd-full-table td { background:transparent; }
.wd-view .wd-full-table tbody tr:nth-child(even) { background:#f8fbff; }
.wd-view .wd-full-table tbody tr:hover { background:#edf5ff; }
.wd-view .wd-full-table .wd-sno { width:58px; text-align:center; white-space:nowrap; }
.wd-view .wd-full-table .wd-number { text-align:right; white-space:nowrap; }
.wd-view .wd-panel .wd-table-wrap { padding:0 12px 12px; }
@media(max-width:640px) {
  .wd-view .wd-full-table { min-width:560px; }
  .wd-view .wd-full-table th, .wd-view .wd-full-table td { padding:8px 9px; }
}
.wd-view .wd-photo { transition:transform .15s ease, box-shadow .15s ease; }
.wd-view .wd-photo:hover { transform:translateY(-2px); box-shadow:0 5px 16px rgba(16,33,47,.13); }
@media(max-width:640px) { .wd-view .wd-summary .wd-field { border-left:0; padding:4px 0; } .wd-view .wd-facts > div { border-left:0; padding-left:0; } }
@media print { .wd-view .wd-activity-head { print-color-adjust:exact; -webkit-print-color-adjust:exact; } .wd-view .wd-panel { break-inside:avoid; } }
</style>
<div class="wd-view mx-auto max-w-full space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="wd-heading text-2xl font-bold text-slate-800">Daily Work Execution</h1>
            <p class="mt-1 text-sm text-slate-500">Saved work activities and informational resource usage</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('work-done.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Back to Work Done</a>
        </div>
    </div>
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    <div class="wd-summary rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="wd-field"><p class="text-xs font-semibold uppercase text-slate-500">Project</p><p class="font-semibold text-slate-800">{{ $workDone->project?->project_name ?? '—' }}</p></div>
            <div class="wd-field"><p class="text-xs font-semibold uppercase text-slate-500">Work date</p><p class="font-semibold text-slate-800">{{ $workDone->work_date?->format('d/m/Y') ?? '—' }}</p></div>
            <div class="wd-field"><p class="text-xs font-semibold uppercase text-slate-500">Engineer</p><p class="font-semibold text-slate-800">{{ $workDone->engineer?->name ?? '—' }}</p></div>
        </div>
        @if($workDone->remarks)
            <div class="mt-4 border-t border-slate-100 pt-3 text-sm"><span class="font-semibold">Daily remarks:</span> {{ $workDone->remarks }}</div>
        @endif
    </div>
    @forelse($workDone->items as $item)
        @php
            $materials = $reportedMaterials->get($item->id, collect());
            $labours = $reportedLabours->get($item->id, collect());
            $machinery = $reportedMachinery->get($item->id, collect());
        @endphp
        <section class="wd-activity overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="wd-activity-head flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="font-bold text-slate-800">Work Activity {{ $loop->iteration }} — {{ $item->workActivity?->name ?? $item->activity_name ?? 'Activity' }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $item->location_path ?: 'Location not specified' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $item->execution_status }}</span>
                    @if($item->dpr_id)<span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">DPR linked</span>@endif
                </div>
            </div>
            <div class="space-y-5 p-5">
                <div class="wd-facts grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                    <div><p class="text-xs text-slate-500">Completed quantity</p><p class="font-semibold">{{ $item->display_quantity }}</p></div>
                    <div><p class="text-xs text-slate-500">Contractor</p><p class="font-semibold">{{ $item->contractor?->contractor_name ?? '—' }}</p></div>
                    <div><p class="text-xs text-slate-500">Progress</p><p class="font-semibold">{{ $item->progress_percentage === null ? '—' : rtrim(rtrim(number_format((float)$item->progress_percentage, 2), '0'), '.') . '%' }}</p></div>
                    <div><p class="text-xs text-slate-500">DPR reference</p><p class="font-semibold">{{ $item->dpr_id ?: 'Not linked' }}</p></div>
                </div>
                <div class="wd-panel wd-panel--materials">
                    <div class="wd-panel-head"><h3>▣ &nbsp;Materials Used</h3><span class="text-xs text-blue-700">Reported only · no stock deduction</span></div>
                    <div class="wd-table-wrap overflow-x-auto"><table class="{{ $tableClass }}"><thead><tr><th class="{{ $thClass }} wd-sno">S.No</th><th class="{{ $thClass }}">Material / Variant</th><th class="{{ $thClass }} wd-number" style="width:155px">Reported Used</th><th class="{{ $thClass }}" style="width:110px">Unit</th></tr></thead><tbody>
                        @forelse($materials as $m)
                            <tr><td class="{{ $tdClass }} wd-sno">{{ $loop->iteration }}</td><td class="{{ $tdClass }}">{{ collect([$m->material_name, $m->brand_name, $m->specification_name, $m->grade_name])->filter()->implode(' / ') ?: $m->stock_key }}</td><td class="{{ $tdClass }} text-right">{{ rtrim(rtrim(number_format((float)$m->quantity_reported, 3, '.', ''), '0'), '.') }}</td><td class="{{ $tdClass }}">{{ $m->unit_name ?? '—' }}</td></tr>
                        @empty<tr><td colspan="4" class="{{ $tdClass }} text-slate-500">No materials reported.</td></tr>@endforelse
                    </tbody></table></div>
                </div>
                <div class="wd-panel wd-panel--labour">
                    <div class="wd-panel-head"><h3>♙ &nbsp;Labour Used</h3><span class="text-xs text-emerald-700">Activity deployment</span></div>
                    <div class="wd-table-wrap overflow-x-auto"><table class="{{ $tableClass }}"><thead><tr><th class="{{ $thClass }} wd-sno">S.No</th><th class="{{ $thClass }}">Labour Type</th><th class="{{ $thClass }} wd-number" style="width:165px">Deployed</th></tr></thead><tbody>
                        @forelse($labours as $l)
                            <tr><td class="{{ $tdClass }} wd-sno">{{ $loop->iteration }}</td><td class="{{ $tdClass }}">{{ $l->labour_group_name ?? $l->designation_name ?? 'Historical labour record' }}</td><td class="{{ $tdClass }} text-right">{{ $l->quantity }}</td></tr>
                        @empty<tr><td colspan="3" class="{{ $tdClass }} text-slate-500">No labour reported.</td></tr>@endforelse
                    </tbody></table></div>
                </div>
                <div class="wd-panel wd-panel--machinery">
                    <div class="wd-panel-head"><h3>⚙ &nbsp;Machinery &amp; Equipment Used</h3><span class="text-xs text-amber-800">Quantity and operating hours</span></div>
                    <div class="wd-table-wrap overflow-x-auto"><table class="{{ $tableClass }}"><thead><tr><th class="{{ $thClass }} wd-sno">S.No</th><th class="{{ $thClass }}">Equipment</th><th class="{{ $thClass }} wd-number" style="width:135px">Quantity</th><th class="{{ $thClass }} wd-number" style="width:175px">Operating Hours</th></tr></thead><tbody>
                        @forelse($machinery as $machine)
                            <tr><td class="{{ $tdClass }} wd-sno">{{ $loop->iteration }}</td><td class="{{ $tdClass }}">{{ $machine->equipment_name }}</td><td class="{{ $tdClass }} text-right">{{ $machine->quantity }}</td><td class="{{ $tdClass }} text-right">{{ $machine->operating_hours ?? '—' }}</td></tr>
                        @empty<tr><td colspan="4" class="{{ $tdClass }} text-slate-500">No equipment reported.</td></tr>@endforelse
                    </tbody></table></div>
                </div>
                @if($item->remarks)
                    <div class="wd-panel wd-panel--remarks text-sm"><span class="font-semibold">Work remarks:</span> {{ $item->remarks }}</div>
                @endif
                <div class="wd-panel wd-panel--photos">
                    <div class="wd-panel-head"><h3>▧ &nbsp;Work Done Photos</h3><span class="text-xs text-violet-700">Site evidence</span></div>
                    <div class="grid grid-cols-2 gap-3 p-3 md:grid-cols-4">
                        @forelse($item->photos as $photo)
                            <a href="{{ $photo->file_url }}" target="_blank" rel="noopener" class="wd-photo overflow-hidden rounded-lg border border-slate-200">
                                <img src="{{ $photo->file_url }}" alt="{{ $photo->display_caption }}" loading="lazy" class="h-36 w-full object-cover">
                                <p class="p-2 text-xs text-slate-600">{{ $photo->display_caption }}</p>
                            </a>
                        @empty<p class="text-sm text-slate-500">No photos attached.</p>@endforelse
                    </div>
                </div>
            </div>
        </section>
    @empty
        <div class="rounded-xl border border-slate-200 bg-white p-6 text-slate-500">No work activities found for this record.</div>
    @endforelse
</div>
@endsection
