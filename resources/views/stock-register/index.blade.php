@extends('layouts.app')
@section('content')
@php $filters = request()->except('export'); @endphp
<div class="mx-auto max-w-full space-y-4">
 <div class="flex flex-wrap items-center justify-between gap-3">
  <div><h1 class="text-2xl font-bold tracking-tight text-slate-900">Stock Register</h1><p class="mt-1 text-xs text-slate-500">Current approved balances · latest transaction first</p></div>
  <div class="flex gap-2">
   <a id="stock-export-pdf" href="{{ route('stock-register.index', array_merge($filters, ['export'=>'pdf'])) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">↓ PDF</a>
   <a id="stock-export-excel" href="{{ route('stock-register.index', array_merge($filters, ['export'=>'xlsx'])) }}" class="rounded-lg bg-emerald-700 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-800">↓ Excel</a>
  </div>
 </div>
 <div id="stock-summary" class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
  <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Available Stock Items</p><p class="mt-1 text-3xl font-bold text-emerald-700">{{ $availableStockCount }}</p></div>
  <div class="text-right text-xs text-slate-500">Positive-balance material identities<br>in the current filtered report</div>
 </div>
 <form id="stock-register-filters" action="{{ route('stock-register.index') }}" method="GET" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
  <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
   <div><label for="project_id" class="mb-1 block text-xs font-semibold text-slate-600">Project</label><select id="project_id" name="project_id" class="w-full rounded-lg border-slate-300 text-sm"><option value="">All Projects</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected((string)request('project_id') === (string)$project->id)>{{ $project->project_name }}</option>@endforeach</select></div>
   <div><label for="material_item" class="mb-1 block text-xs font-semibold text-slate-600">Material Item</label><select id="material_item" name="material_item" class="w-full rounded-lg border-slate-300 text-sm"><option value="">All Stock Items</option>@foreach($itemOptions as $item)<option value="{{ $item['key'] }}" @selected(request('material_item') === $item['key'])>{{ $item['label'] }}</option>@endforeach</select></div>
   <div><label for="date_from" class="mb-1 block text-xs font-semibold text-slate-600">Last Transaction From</label><input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-slate-300 text-sm"></div>
   <div><label for="date_to" class="mb-1 block text-xs font-semibold text-slate-600">Last Transaction To</label><input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-slate-300 text-sm"></div>
   <div><label for="stock_status" class="mb-1 block text-xs font-semibold text-slate-600">Stock Status</label><select id="stock_status" name="stock_status" class="w-full rounded-lg border-slate-300 text-sm">@foreach(['all'=>'All Balances','available'=>'Available Only','zero'=>'Zero Balance','negative'=>'Negative Balance'] as $value=>$label)<option value="{{ $value }}" @selected(request('stock_status','all') === $value)>{{ $label }}</option>@endforeach</select></div>
   <div class="flex items-end gap-2"><button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-xs font-bold text-white">Apply</button><a href="{{ route('stock-register.index') }}" class="rounded-lg border border-slate-300 px-3 py-2.5 text-xs font-semibold text-slate-600">Reset</a></div>
  </div>
  <p class="mt-2 text-[11px] text-slate-500">Filters update the report without reloading the page. Date filters match each stock item's latest approved transaction; balances remain current totals. Exports follow the same filters.</p>
 </form>
 <p id="stock-ajax-error" role="alert" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-xs text-rose-700"></p>
 <section id="stock-table" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
  <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3"><h2 class="text-sm font-bold text-slate-800">Current Stock Position</h2><span class="text-xs text-slate-500">{{ $stockRows->count() }} records · newest first</span></div>
  <div class="overflow-x-auto"><table class="w-full min-w-[850px] table-fixed text-[12px] leading-snug">
   <colgroup><col style="width:4%"><col style="width:11%"><col style="width:12%"><col style="width:15%"><col style="width:20%"><col style="width:10%"><col style="width:7%"><col style="width:7%"><col style="width:7%"><col style="width:7%"></colgroup>
   <thead class="bg-slate-100 text-[10px] font-bold uppercase tracking-wide text-slate-600"><tr><th class="px-2 py-3 text-center">#</th><th class="px-2 py-3 text-left">Date</th><th class="px-2 py-3 text-left">Project</th><th class="px-2 py-3 text-left">Material</th><th class="px-2 py-3 text-left">Specification</th><th class="px-2 py-3 text-left">Unit</th><th class="px-2 py-3 text-right">Received</th><th class="px-2 py-3 text-right">Consumed</th><th class="px-2 py-3 text-right">Wastage</th><th class="px-2 py-3 text-right">Available</th></tr></thead>
   <tbody class="divide-y divide-slate-100">@forelse($stockRows as $index=>$row)
   <tr class="hover:bg-blue-50/50"><td class="px-2 py-3 text-center text-slate-400">{{ $index+1 }}</td><td class="whitespace-nowrap px-2 py-3 text-slate-600">{{ $row['latest_movement_at'] ? date('d M Y', $row['latest_movement_at']) : '—' }}<span class="block text-[10px] text-slate-400">{{ $row['latest_movement_at'] ? date('h:i A', $row['latest_movement_at']) : '' }}</span></td><td class="break-words px-2 py-3 font-medium text-slate-700">{{ $row['project_name'] }}</td><td class="break-words px-2 py-3 font-semibold text-slate-900">{{ $row['material_label'] }}</td><td class="break-words px-2 py-3 text-slate-600">{{ $row['specification_display'] }}</td><td class="break-words px-2 py-3 text-slate-600">{{ $row['unit'] }}</td><td class="px-2 py-3 text-right font-semibold tabular-nums text-emerald-700">{{ formatQuantity($row['received_qty']) }}</td><td class="px-2 py-3 text-right tabular-nums text-blue-700">{{ formatQuantity($row['consumed_qty']) }}</td><td class="px-2 py-3 text-right tabular-nums text-rose-700">{{ formatQuantity($row['wastage_qty']) }}</td><td class="px-2 py-3 text-right font-bold tabular-nums {{ $row['balance_qty'] < 0 ? 'text-rose-700' : ($row['balance_qty'] == 0 ? 'text-amber-700' : 'text-emerald-700') }}">{{ formatQuantity($row['balance_qty']) }}</td></tr>
   @empty<tr><td colspan="10" class="px-4 py-12 text-center text-slate-500">No stock records match these filters.</td></tr>@endforelse</tbody>
  </table></div>
  <div class="border-t border-slate-100 px-4 py-3 text-[11px] text-slate-500">Approved Received − Approved Consumed − Approved Wastage = Available. Negative historical balances are shown without adjustment.</div>
 </section>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('stock-register-filters');
    const project = document.getElementById('project_id');
    const material = document.getElementById('material_item');
    const summary = document.getElementById('stock-summary');
    const table = document.getElementById('stock-table');
    if (!form || !project || !material || !summary || !table) return;

    let pending = null;
    let debounce = null;
    let sequence = 0;

    const buildUrl = () => {
        const url = new URL(form.action, window.location.origin);
        const values = new FormData(form);
        for (const [key, value] of values.entries()) {
            if (String(value).trim() !== '') url.searchParams.set(key, value);
        }
        return url;
    };

    const updateExportLinks = (url) => {
        for (const [id, format] of [['stock-export-pdf', 'pdf'], ['stock-export-excel', 'xlsx']]) {
            const link = document.getElementById(id);
            if (!link) continue;
            const exportUrl = new URL(url);
            exportUrl.searchParams.set('export', format);
            link.href = exportUrl.toString();
        }
    };

    const refresh = async () => {
        if (pending) pending.abort();
        const controller = new AbortController();
        pending = controller;
        const requestNumber = ++sequence;
        const url = buildUrl();
        summary.setAttribute('aria-busy', 'true');
        table.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                credentials: 'same-origin',
                signal: controller.signal
            });
            if (!response.ok) throw new Error('Report request failed (' + response.status + ')');
            const html = await response.text();
            if (requestNumber !== sequence) return;
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const nextSummary = doc.getElementById('stock-summary');
            const nextTable = doc.getElementById('stock-table');
            const nextMaterial = doc.getElementById('material_item');
            if (!nextSummary || !nextTable || !nextMaterial) throw new Error('Unexpected report response');
            summary.innerHTML = nextSummary.innerHTML;
            table.innerHTML = nextTable.innerHTML;
            // Material choices always reflect the selected project's real stock.
            material.innerHTML = nextMaterial.innerHTML;
            updateExportLinks(url);
            window.history.replaceState({}, '', url.toString());
            const error = document.getElementById('stock-ajax-error');
            if (error) error.classList.add('hidden');
        } catch (error) {
            if (error.name !== 'AbortError') {
                const alert = document.getElementById('stock-ajax-error');
                if (alert) {
                    alert.textContent = 'Unable to update the Stock Register. Please click Apply to retry.';
                    alert.classList.remove('hidden');
                }
                console.error(error);
            }
        } finally {
            if (requestNumber === sequence) {
                summary.removeAttribute('aria-busy');
                table.removeAttribute('aria-busy');
                pending = null;
            }
        }
    };

    const queueRefresh = (delay = 0) => {
        clearTimeout(debounce);
        debounce = setTimeout(refresh, delay);
    };

    project.addEventListener('change', () => {
        material.value = '';
        queueRefresh();
    });
    material.addEventListener('change', () => queueRefresh());
    document.getElementById('stock_status').addEventListener('change', () => queueRefresh());
    for (const id of ['date_from', 'date_to']) {
        document.getElementById(id).addEventListener('change', () => {
            const from = document.getElementById('date_from').value;
            const to = document.getElementById('date_to').value;
            // Avoid an invalid date range request while the user chooses dates.
            if (from && to && to < from) return;
            queueRefresh(250);
        });
    }
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        clearTimeout(debounce);
        refresh();
    });
    // Keep the existing Reset link working without a full navigation.
    const reset = form.querySelector('a[href="{{ route('stock-register.index') }}"]');
    if (reset) reset.addEventListener('click', (event) => {
        event.preventDefault();
        form.reset();
        project.value = '';
        material.value = '';
        queueRefresh();
    });
});
</script>
@endsection
