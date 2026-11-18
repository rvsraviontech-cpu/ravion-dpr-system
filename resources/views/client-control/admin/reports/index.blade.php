@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="flex flex-wrap justify-between gap-4 items-center mb-6">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Client Progress Reports</h1>
      <p class="text-slate-500">Internal drafts only · Nothing on this screen is client-visible</p>
    </div>
    <a class="rounded-xl bg-teal-700 hover:bg-teal-800 text-white px-5 py-3 font-semibold" href="{{ route('client-control.admin.reports.create') }}">Create weekly report</a>
  </div>
  @if(session('success'))
    <div role="status" class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900">
      <span aria-hidden="true" class="font-bold">✓</span>
      <div><p class="font-semibold">Report saved successfully</p><p class="text-sm mt-1">{{ session('success') }}</p></div>
    </div>
  @endif
  <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
      <thead class="bg-slate-900 text-white"><tr>
        <th scope="col" class="p-4 text-left">Report</th><th scope="col" class="p-4 text-left">Project</th><th scope="col" class="p-4 text-left">Week</th><th scope="col" class="p-4 text-left">Status</th><th scope="col" class="p-4 text-left">Action</th>
      </tr></thead>
      <tbody>
        @forelse($reports as $report)
          <tr class="border-t border-slate-100">
            <td class="p-4 font-semibold text-slate-900">{{ $report->report_number }}</td>
            <td class="p-4">{{ $report->project_name }}</td>
            <td class="p-4 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($report->period_start)->format('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($report->period_end)->format('d M Y') }}</td>
            <td class="p-4"><span class="inline-block rounded-full bg-amber-50 text-amber-800 px-3 py-1">{{ $report->status }}</span></td>
            <td class="p-4"><a class="inline-flex items-center rounded-lg border border-teal-200 px-3 py-2 text-teal-800 hover:bg-teal-50 font-semibold whitespace-nowrap" href="{{ route('client-control.admin.reports.edit', $report->id) }}">{{ $report->status === 'Draft' ? 'Edit Draft' : 'Open Report' }}</a></td>
          </tr>
        @empty
          <tr><td class="p-8 text-slate-500 text-center" colspan="5">No client progress report drafts yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $reports->links() }}</div>
</div>
@endsection
