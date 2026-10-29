{{-- PMO Action Desk: Work Done approvals. Uses $workDonePending and $workDoneStats from PmoDashboardController. --}}
@php
    $workDonePending = $workDonePending ?? collect();
    $pendingWorkDoneCount = $workDoneStats['pending'] ?? ($counts['work_done_pending'] ?? 0);
@endphp
<div class="mb-4">
    <div class="mb-2 flex flex-wrap items-center justify-between gap-2 px-2">
        <h2 class="text-sm font-bold text-slate-900">Work Done — Pending Approvals</h2>
        <div class="flex items-center gap-3 text-xs">
            <span class="text-slate-700">{{ $pendingWorkDoneCount }} Pending</span>
            <a href="{{ route('work-done.review.index') }}" class="font-medium text-blue-700 hover:underline">View All</a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] border-collapse text-left text-xs">
            <thead class="bg-slate-800 text-white">
                <tr>
                    <th class="border border-slate-600 px-2 py-1.5">S.No</th>
                    <th class="border border-slate-600 px-2 py-1.5">Work Date</th>
                    <th class="border border-slate-600 px-2 py-1.5">Project</th>
                    <th class="border border-slate-600 px-2 py-1.5">Engineer</th>
                    <th class="border border-slate-600 px-2 py-1.5">Submitted On</th>
                    <th class="border border-slate-600 px-2 py-1.5">Status</th>
                    <th class="border border-slate-600 px-2 py-1.5">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workDonePending as $entry)
                    <tr class="bg-white hover:bg-slate-50">
                        <td class="border border-slate-300 px-2 py-1.5">{{ $loop->iteration }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($entry->work_date)->format('d/m/Y') }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ $entry->project_name }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ $entry->engineer_name ?? '—' }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 whitespace-nowrap">{{ $entry->submitted_at ? \Illuminate\Support\Carbon::parse($entry->submitted_at)->format('d/m/Y h:i A') : '—' }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ $entry->status }}</td>
                        <td class="border border-slate-300 px-2 py-1.5"><a href="{{ route('work-done.show', $entry->id) }}" class="text-blue-700 hover:underline">Review</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="border border-slate-300 px-3 py-3 text-center text-slate-500">No Work Done entries pending PMO approval.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
