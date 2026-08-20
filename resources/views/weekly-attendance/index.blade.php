@extends('layouts.app')

@section('content')
<x-rds.page-header
    title="Weekly Labour Attendance"
    subtitle="Admin/PMO bulk attendance recovery for Sunday to Saturday. Present defaults to a full normal shift."
/>

<x-rds.alert />

@if(session('success'))
    <div
        class="mb-5 rounded-xl border border-green-300 bg-green-50 px-4 py-4 text-green-900 shadow-sm"
        role="status"
        aria-live="polite"
    >
        <div class="flex items-start gap-3">
            <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-600 text-sm font-bold text-white">
                ✓
            </div>

            <div>
                <p class="font-semibold">
                    Weekly attendance saved successfully.
                </p>

                <p class="mt-1 text-sm text-green-800">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    </div>
@endif

<x-rds.card>
    <form method="GET" action="{{ route('weekly-attendance.index') }}">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-rds.select name="project_id" label="Project" required>
                <option value="">Select Project</option>
                @foreach($projects as $item)
                    <option value="{{ $item->id }}" @selected((string) request('project_id') === (string) $item->id)>
                        {{ $item->project_name }}
                    </option>
                @endforeach
            </x-rds.select>

            <x-rds.input
                type="date"
                name="week_start"
                label="Week Starting"
                value="{{ request('week_start', $weekStart->toDateString()) }}"
            />

            <div class="flex items-end">
                <x-rds.button type="submit" variant="primary">Load Week</x-rds.button>
            </div>
        </div>
    </form>
</x-rds.card>

@if($project)
    @php
        $statusOptions = $statuses->mapWithKeys(function ($status) {
            $code = strtoupper(trim((string) ($status->short_name ?: $status->code ?: $status->name)));
            $label = match ($code) {
                'PRESENT' => 'P',
                'ABSENT' => 'A',
                'HALF DAY', 'HALF-DAY', 'HALF_DAY', 'HALFDAY' => 'HD',
                'LEAVE' => 'L',
                'WEEKLY OFF', 'WEEKLY-OFF', 'WEEKLY_OFF' => 'WO',
                'HOLIDAY' => 'H',
                default => $code,
            };
            return [$status->id => $label];
        });

        $groupedLabours = $labours->groupBy(fn ($labour) => $labour->labourGroup?->name ?? 'Un-grouped Labour');
    @endphp

    <form method="POST" action="{{ route('weekly-attendance.store') }}" class="mt-6">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">

        <x-rds.card :padding="false">
            <div class="border-b border-gray-200 px-4 py-4">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="font-semibold text-gray-900">{{ $project->project_name }}</h2>
                        <p class="text-sm text-gray-500">
                            {{ $weekStart->format('d M Y') }} - {{ $weekStart->copy()->addDays(6)->format('d M Y') }}
                        </p>
                    </div>
                    <div class="text-xs text-gray-500">
                        — Not Marked · P Present · A Absent · HD Half Day · L Leave · WO Weekly Off · H Holiday
                    </div>
                </div>
            </div>

            <div class="max-h-[620px] overflow-auto">
                <table class="w-full min-w-[1050px] table-fixed border-collapse">
                    <colgroup>
                        <col class="w-[55px]">
                        <col class="w-[240px]">
                        @foreach($days as $day)<col class="w-[105px]">@endforeach
                    </colgroup>
                    <thead class="sticky top-0 z-20 bg-gray-50 shadow-sm">
                        <tr>
                            <th class="border-b px-3 py-3 text-left text-xs font-semibold uppercase text-gray-600">#</th>
                            <th class="sticky left-0 z-30 border-b bg-gray-50 px-3 py-3 text-left text-xs font-semibold uppercase text-gray-600">Labour Name</th>
                            @foreach($days as $day)
                                @php $dateKey = $day->toDateString(); @endphp
                                <th class="border-b px-2 py-3 text-center text-xs font-semibold uppercase text-gray-600">
                                    <div>{{ $day->format('j D') }}</div>

                                    @if(isset($lockedDates[$dateKey]))
                                        <div class="mt-1 text-[10px] font-bold text-amber-700">
                                            {{ $lockedDates[$dateKey] }} 🔒
                                        </div>
                                    @elseif($day->isFuture())
                                        <div class="mt-1 text-[10px] font-bold text-gray-400">
                                            Future
                                        </div>
                                    @else
                                        <div class="mt-2 flex items-center justify-center gap-1 normal-case">
                                            <button
                                                type="button"
                                                class="weekly-bulk-status rounded border border-emerald-300 bg-emerald-50 px-1.5 py-1 text-[10px] font-bold text-emerald-700 hover:bg-emerald-100"
                                                data-date="{{ $dateKey }}"
                                                data-status-label="P"
                                            >
                                                P All
                                            </button>

                                            <button
                                                type="button"
                                                class="weekly-bulk-status rounded border border-red-300 bg-red-50 px-1.5 py-1 text-[10px] font-bold text-red-700 hover:bg-red-100"
                                                data-date="{{ $dateKey }}"
                                                data-status-label="A"
                                            >
                                                A All
                                            </button>
                                        </div>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="bg-white">
                        @php $serial = 0; @endphp
                        @forelse($groupedLabours as $groupName => $groupLabours)
                            <tr class="bg-[#0F2A52]">
                                <td colspan="9" class="border-y border-[#0F2A52] bg-[#0F2A52] px-3 py-2 text-xs font-bold uppercase tracking-wide text-white">
                                    {{ $groupName }}
                                    <span class="ml-2 font-medium normal-case tracking-normal text-blue-100">
                                        {{ $groupLabours->count() }}
                                        labour{{ $groupLabours->count() === 1 ? '' : 's' }}
                                    </span>
                                </td>
                            </tr>

                            @foreach($groupLabours as $labour)
                                @php $serial++; @endphp
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $serial }}</td>
                                    <td class="sticky left-0 z-10 bg-white px-3 py-2">
                                        <div class="text-sm font-semibold text-gray-900">{{ $labour->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $labour->designationRole?->name ?? '—' }}</div>
                                    </td>

                                    @foreach($days as $day)
                                        @php
                                            $dateKey = $day->toDateString();
                                            $existingDetail = $existingByDate->get($dateKey)?->get($labour->id);
                                            $selected = old("attendance.{$dateKey}.{$labour->id}", $existingDetail?->attendance_status_id);
                                            $locked = isset($lockedDates[$dateKey]) || $day->isFuture();
                                        @endphp
                                        <td class="px-2 py-2 text-center">
                                            @if($locked)
                                                <div class="flex items-center justify-center">
                                                    <span class="inline-flex min-w-[46px] items-center justify-center rounded-md border border-gray-200 bg-gray-100 px-2 py-2 text-xs font-semibold text-gray-500">
                                                        {{ $selected ? ($statusOptions[$selected] ?? '—') : '—' }}
                                                    </span>
                                                </div>
                                            @else
                                                @php
                                                    $presentStatusId = $statusOptions->search('P');
                                                    $absentStatusId = $statusOptions->search('A');

                                                    $selectedLabel = $selected
                                                        ? ($statusOptions[$selected] ?? null)
                                                        : null;

                                                    $exceptionSelected =
                                                        $selectedLabel
                                                        && ! in_array(
                                                            $selectedLabel,
                                                            ['P', 'A'],
                                                            true
                                                        );
                                                @endphp

                                                <div
                                                    class="weekly-status-cell flex items-center justify-center gap-1"
                                                    data-date="{{ $dateKey }}"
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="attendance[{{ $dateKey }}][{{ $labour->id }}]"
                                                        value="{{ $selected }}"
                                                        class="weekly-status-input"
                                                    >

                                                    <button
                                                        type="button"
                                                        class="weekly-status-btn rounded-md border px-2 py-2 text-xs font-bold transition
                                                            {{ $selectedLabel === 'P'
                                                                ? 'border-emerald-500 bg-emerald-500 text-white'
                                                                : 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}"
                                                        data-status-id="{{ $presentStatusId }}"
                                                        data-status-label="P"
                                                        title="Present - Full Normal Shift"
                                                    >
                                                        P
                                                    </button>

                                                    <button
                                                        type="button"
                                                        class="weekly-status-btn rounded-md border px-2 py-2 text-xs font-bold transition
                                                            {{ $selectedLabel === 'A'
                                                                ? 'border-red-500 bg-red-500 text-white'
                                                                : 'border-red-300 bg-red-50 text-red-700 hover:bg-red-100' }}"
                                                        data-status-id="{{ $absentStatusId }}"
                                                        data-status-label="A"
                                                        title="Absent"
                                                    >
                                                        A
                                                    </button>

                                                    <select
                                                        class="weekly-exception-select min-w-[58px] rounded-md border border-gray-300 bg-white px-1 py-2 text-xs text-gray-700"
                                                        aria-label="Other Attendance Status"
                                                        title="Other Status"
                                                    >
                                                        <option value="">Status</option>

                                                        @foreach($statusOptions as $statusId => $label)
                                                            @continue(in_array($label, ['P', 'A'], true))

                                                            <option
                                                                value="{{ $statusId }}"
                                                                @selected(
                                                                    $exceptionSelected
                                                                    && (string) $selected
                                                                        === (string) $statusId
                                                                )
                                                            >
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="9" class="px-6 py-12 text-center text-sm text-gray-500">No active assigned labour found for this project/week.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end border-t border-gray-200 px-4 py-4">
                <x-rds.button type="submit" variant="primary">Save Weekly Attendance</x-rds.button>
            </div>
        </x-rds.card>
    </form>
@endif
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cells = Array.from(
        document.querySelectorAll('.weekly-status-cell')
    );

    function setCellStatus(cell, statusId, statusLabel) {
        if (!cell) {
            return;
        }

        const input = cell.querySelector('.weekly-status-input');
        const exceptionSelect = cell.querySelector('.weekly-exception-select');
        const buttons = cell.querySelectorAll('.weekly-status-btn');

        if (!input) {
            return;
        }

        input.value = statusId || '';

        buttons.forEach(function (button) {
            const label = button.dataset.statusLabel;
            const isActive = label === statusLabel;

            if (label === 'P') {
                button.className = isActive
                    ? 'weekly-status-btn rounded-md border border-emerald-500 bg-emerald-500 px-2 py-2 text-xs font-bold text-white transition'
                    : 'weekly-status-btn rounded-md border border-emerald-300 bg-emerald-50 px-2 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100';
            }

            if (label === 'A') {
                button.className = isActive
                    ? 'weekly-status-btn rounded-md border border-red-500 bg-red-500 px-2 py-2 text-xs font-bold text-white transition'
                    : 'weekly-status-btn rounded-md border border-red-300 bg-red-50 px-2 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100';
            }
        });

        if (exceptionSelect) {
            if (
                statusId
                && statusLabel !== 'P'
                && statusLabel !== 'A'
            ) {
                exceptionSelect.value = statusId;
            } else {
                exceptionSelect.value = '';
            }
        }
    }

    document.querySelectorAll('.weekly-status-btn')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                const cell = button.closest('.weekly-status-cell');

                if (!cell) {
                    return;
                }

                const input = cell.querySelector('.weekly-status-input');
                const clickedStatusId = String(button.dataset.statusId || '');
                const currentStatusId = String(input?.value || '');

                if (
                    clickedStatusId
                    && currentStatusId === clickedStatusId
                ) {
                    setCellStatus(cell, '', '');
                    return;
                }

                setCellStatus(
                    cell,
                    button.dataset.statusId,
                    button.dataset.statusLabel
                );
            });
        });

    document.querySelectorAll('.weekly-exception-select')
        .forEach(function (select) {
            select.addEventListener('change', function () {
                const cell = select.closest('.weekly-status-cell');

                if (!select.value) {
                    setCellStatus(cell, '', '');
                    return;
                }

                const selectedOption =
                    select.options[select.selectedIndex];

                setCellStatus(
                    cell,
                    select.value,
                    selectedOption.text.trim()
                );
            });
        });

    document.querySelectorAll('.weekly-bulk-status')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                const date = button.dataset.date;
                const label = button.dataset.statusLabel;

                cells
                    .filter(function (cell) {
                        return cell.dataset.date === date;
                    })
                    .forEach(function (cell) {
                        const statusButton = cell.querySelector(
                            '.weekly-status-btn[data-status-label="'
                            + label
                            + '"]'
                        );

                        if (!statusButton) {
                            return;
                        }

                        setCellStatus(
                            cell,
                            statusButton.dataset.statusId,
                            statusButton.dataset.statusLabel
                        );
                    });
            });
        });
});
</script>
@endpush

@endsection
