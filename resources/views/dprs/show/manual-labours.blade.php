@php
    $manualLabours = $dpr->manualLabours ?? collect();

    $manualNormalHours = $manualLabours->sum(
        fn ($row) => (float) ($row->normal_hours ?? 0)
    );

    $manualOtHours = $manualLabours->sum(
        fn ($row) => (float) ($row->ot_hours ?? 0)
    );
@endphp

<div class="rounded-xl border border-amber-200 bg-white shadow-sm">

    <div class="border-b border-amber-200 bg-amber-50 px-6 py-4">

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <div class="flex flex-wrap items-center gap-2">

                    <h2 class="text-xl font-bold text-gray-900">
                        Manual Labour Exceptions
                    </h2>

                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                        Manual DPR Addition
                    </span>

                </div>

                <p class="mt-1 text-sm text-gray-600">
                    Labour added directly in the DPR because of an attendance exception.
                </p>

            </div>

            @if($manualLabours->isNotEmpty())

                <div class="flex flex-wrap gap-2">

                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-sm font-semibold text-gray-700 ring-1 ring-amber-200">
                        {{ $manualLabours->count() }}
                        {{ $manualLabours->count() === 1 ? 'Exception' : 'Exceptions' }}
                    </span>

                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-sm font-semibold text-gray-700 ring-1 ring-amber-200">
                        {{ number_format($manualNormalHours + $manualOtHours, 2) }}
                        Total Hours
                    </span>

                </div>

            @endif

        </div>

    </div>

    @if($manualLabours->isNotEmpty())

        <div class="grid grid-cols-1 gap-4 border-b border-amber-200 bg-amber-50/50 px-6 py-4 sm:grid-cols-3">

            <div class="rounded-lg border border-amber-200 bg-white p-4">

                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Manual Labour
                </p>

                <p class="mt-2 text-2xl font-bold text-gray-900">
                    {{ $manualLabours->count() }}
                </p>

            </div>

            <div class="rounded-lg border border-amber-200 bg-white p-4">

                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Normal Hours
                </p>

                <p class="mt-2 text-2xl font-bold text-gray-900">
                    {{ number_format($manualNormalHours, 2) }}
                </p>

            </div>

            <div class="rounded-lg border border-amber-200 bg-white p-4">

                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Overtime Hours
                </p>

                <p class="mt-2 text-2xl font-bold text-gray-900">
                    {{ number_format($manualOtHours, 2) }}
                </p>

            </div>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-white">

                    <tr>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            #
                        </th>

                        <th
                            scope="col"
                            class="min-w-52 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Labour
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Labour Code
                        </th>

                        <th
                            scope="col"
                            class="min-w-40 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Category
                        </th>

                        <th
                            scope="col"
                            class="min-w-40 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Trade
                        </th>

                        <th
                            scope="col"
                            class="min-w-40 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Shift
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Attendance Status
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Normal Hours
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            OT Hours
                        </th>

                        <th
                            scope="col"
                            class="min-w-44 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Exception Reason
                        </th>

                        <th
                            scope="col"
                            class="min-w-56 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Remarks
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">

                    @foreach($manualLabours as $manual)

                        @php
                            $reasonLabels = [
                                'missed_attendance' => 'Missed Attendance',
                                'late_joining' => 'Late Joining',
                                'replacement_labour' => 'Replacement Labour',
                                'emergency_labour' => 'Emergency Labour',
                                'attendance_not_created' => 'Attendance Not Created',
                                'attendance_incomplete' => 'Attendance Incomplete',
                                'other' => 'Other',
                            ];

                            $statusCode = strtoupper(
                                (string) (
                                    $manual->attendanceStatus?->code
                                    ?? ''
                                )
                            );

                            $statusName =
                                $manual->attendanceStatus?->status_name
                                ?? $manual->attendanceStatus?->name
                                ?? $manual->attendanceStatus?->code
                                ?? 'Not Specified';
                        @endphp

                        <tr class="transition hover:bg-amber-50/50">

                            <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-500">
                                {{ $loop->iteration }}
                            </td>

                            <td class="px-4 py-4 text-sm">

                                <div class="flex flex-col gap-1">

                                    <p class="font-semibold text-gray-900">
                                        {{
                                            $manual->labour?->full_name
                                            ?? $manual->labour?->name
                                            ?? 'Unknown Labour'
                                        }}
                                    </p>

                                    <span class="inline-flex w-fit rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">
                                        Manual Addition
                                    </span>

                                </div>

                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                {{ $manual->labour?->labour_code ?? '-' }}
                            </td>

                            <td class="px-4 py-4 text-sm text-gray-700">
                                {{
                                    $manual->labour?->labourCategory?->category_name
                                    ?? $manual->labour?->labourCategory?->name
                                    ?? '-'
                                }}
                            </td>

                            <td class="px-4 py-4 text-sm text-gray-700">
                                {{
                                    $manual->labour?->labourType?->labour_type_name
                                    ?? $manual->labour?->labourType?->name
                                    ?? '-'
                                }}
                            </td>

                            <td class="px-4 py-4 text-sm text-gray-700">
                                {{
                                    $manual->shift?->shift_name
                                    ?? $manual->shift?->name
                                    ?? '-'
                                }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm">

                                @if(in_array($statusCode, ['P', 'PRESENT']))

                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                        {{ $statusName }}
                                    </span>

                                @elseif(in_array($statusCode, ['A', 'ABSENT']))

                                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                        {{ $statusName }}
                                    </span>

                                @elseif(in_array($statusCode, ['HD', 'HALF DAY', 'HALF_DAY']))

                                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                        {{ $statusName }}
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        {{ $statusName }}
                                    </span>

                                @endif

                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-900">
                                {{ number_format((float) ($manual->normal_hours ?? 0), 2) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-900">
                                {{ number_format((float) ($manual->ot_hours ?? 0), 2) }}
                            </td>

                            <td class="px-4 py-4 text-sm text-gray-700">
                                {{
                                    $reasonLabels[$manual->reason]
                                    ?? \Illuminate\Support\Str::headline(
                                        (string) $manual->reason
                                    )
                                }}
                            </td>

                            <td class="px-4 py-4 text-sm text-gray-700">
                                <span class="whitespace-pre-line">
                                    {{ $manual->remarks ?: '-' }}
                                </span>
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        <div class="border-t border-amber-200 bg-amber-50 px-6 py-4">

            <p class="text-sm text-amber-900">
                <span class="font-semibold">
                    PMO attention:
                </span>

                These labour records were entered manually in the DPR and were not imported from the linked Labour Attendance sheets.
            </p>

        </div>

    @else

        <div class="px-6 py-12 text-center">

            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-700">

                <svg
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M5 13l4 4L19 7"
                    />
                </svg>

            </div>

            <h3 class="mt-4 text-sm font-semibold text-gray-900">
                No manual labour exceptions
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                All labour information in this DPR was imported from the linked Labour Attendance records.
            </p>

        </div>

    @endif

</div>