@php
    $linkedAttendances = $dpr->labourAttendances ?? collect();

    $totalAttendanceLabour = $linkedAttendances->sum(
        fn ($attendance) => $attendance->details?->count() ?? 0
    );

    $totalNormalHours = $linkedAttendances->sum(
        fn ($attendance) => $attendance->details?->sum(
            fn ($detail) => (float) ($detail->normal_hours ?? 0)
        ) ?? 0
    );

    $totalOtHours = $linkedAttendances->sum(
        fn ($attendance) => $attendance->details?->sum(
            fn ($detail) => (float) ($detail->ot_hours ?? 0)
        ) ?? 0
    );
@endphp

<div class="rounded-xl border border-gray-200 bg-white shadow-sm">

    <div class="border-b border-gray-200 px-6 py-4">

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <div class="flex flex-wrap items-center gap-2">

                    <h2 class="text-xl font-bold text-gray-900">
                        Labour Attendance
                    </h2>

                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                        Imported from Attendance
                    </span>

                </div>

                <p class="mt-1 text-sm text-gray-500">
                    Labour records automatically linked using the DPR project and date.
                </p>

            </div>

            @if($linkedAttendances->isNotEmpty())

                <div class="flex flex-wrap gap-2">

                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700">
                        {{ $linkedAttendances->count() }}
                        {{ $linkedAttendances->count() === 1 ? 'Attendance Sheet' : 'Attendance Sheets' }}
                    </span>

                    <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">
                        {{ $totalAttendanceLabour }}
                        {{ $totalAttendanceLabour === 1 ? 'Labour' : 'Labourers' }}
                    </span>

                </div>

            @endif

        </div>

    </div>

    @if($linkedAttendances->isNotEmpty())

        <div class="grid grid-cols-1 gap-4 border-b border-gray-200 bg-gray-50 px-6 py-4 sm:grid-cols-3">

            <div class="rounded-lg border border-gray-200 bg-white p-4">

                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Total Labour
                </p>

                <p class="mt-2 text-2xl font-bold text-gray-900">
                    {{ $totalAttendanceLabour }}
                </p>

            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4">

                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Normal Hours
                </p>

                <p class="mt-2 text-2xl font-bold text-gray-900">
                    {{ number_format($totalNormalHours, 2) }}
                </p>

            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4">

                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Overtime Hours
                </p>

                <p class="mt-2 text-2xl font-bold text-gray-900">
                    {{ number_format($totalOtHours, 2) }}
                </p>

            </div>

        </div>

        <div class="space-y-6 p-6">

            @foreach($linkedAttendances as $attendance)

                @php
                    $attendanceDetails = $attendance->details ?? collect();

                    $attendanceNormalHours = $attendanceDetails->sum(
                        fn ($detail) => (float) ($detail->normal_hours ?? 0)
                    );

                    $attendanceOtHours = $attendanceDetails->sum(
                        fn ($detail) => (float) ($detail->ot_hours ?? 0)
                    );

                    $attendanceStatus = strtolower(
                        (string) ($attendance->status ?? '')
                    );
                @endphp

                <div class="overflow-hidden rounded-xl border border-gray-200">

                    <div class="bg-gray-50 px-5 py-4">

                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                            <div>

                                <div class="flex flex-wrap items-center gap-2">

                                    <h3 class="font-bold text-gray-900">
                                        {{
                                            $attendance->attendance_number
                                            ?? 'Attendance Sheet #' . $attendance->id
                                        }}
                                    </h3>

                                    @if($attendanceStatus === 'approved')

                                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                            Approved
                                        </span>

                                    @elseif($attendanceStatus === 'submitted')

                                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                            Submitted
                                        </span>

                                    @elseif($attendanceStatus === 'reopened')

                                        <span class="inline-flex items-center rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-800">
                                            Reopened
                                        </span>

                                    @else

                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                            {{ ucfirst($attendance->status ?? 'Unknown') }}
                                        </span>

                                    @endif

                                </div>

                                <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600">

                                    <span>
                                        <span class="font-semibold text-gray-700">
                                            Date:
                                        </span>

                                        {{
                                            $attendance->attendance_date
                                                ? \Carbon\Carbon::parse(
                                                    $attendance->attendance_date
                                                )->format('d M Y')
                                                : '-'
                                        }}
                                    </span>

                                    <span>
                                        <span class="font-semibold text-gray-700">
                                            Shift:
                                        </span>

                                        {{
                                            $attendance->shift?->shift_name
                                            ?? $attendance->shift?->name
                                            ?? 'General Shift'
                                        }}
                                    </span>

                                </div>

                            </div>

                            <div class="flex flex-wrap gap-2">

                                <span class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                    <span class="font-semibold">
                                        {{ $attendanceDetails->count() }}
                                    </span>

                                    <span class="ml-1">
                                        Labour
                                    </span>
                                </span>

                                <span class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                    <span class="font-semibold">
                                        {{ number_format($attendanceNormalHours, 2) }}
                                    </span>

                                    <span class="ml-1">
                                        Normal Hrs
                                    </span>
                                </span>

                                <span class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                    <span class="font-semibold">
                                        {{ number_format($attendanceOtHours, 2) }}
                                    </span>

                                    <span class="ml-1">
                                        OT Hrs
                                    </span>
                                </span>

                                @if(Route::has('labour-attendances.show'))

                                    <a
                                        href="{{ route('labour-attendances.show', $attendance) }}"
                                        class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 print:hidden"
                                    >
                                        View Attendance
                                    </a>

                                @endif

                            </div>

                        </div>

                    </div>

                    @if($attendanceDetails->isNotEmpty())

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
                                            Designation
                                        </th>

                                        <th
                                            scope="col"
                                            class="min-w-40 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                                        >
                                            Contractor
                                        </th>

                                        <th
                                            scope="col"
                                            class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                                        >
                                            Status
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
                                            class="min-w-52 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                                        >
                                            Remarks
                                        </th>

                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-gray-200 bg-white">

                                    @foreach($attendanceDetails as $detail)

                                        @php
                                            $statusCode = strtoupper(
                                                (string) (
                                                    $detail->attendanceStatus?->code
                                                    ?? ''
                                                )
                                            );

                                            $statusName =
                                                $detail->attendanceStatus?->status_name
                                                ?? $detail->attendanceStatus?->name
                                                ?? $detail->attendanceStatus?->code
                                                ?? 'Not Specified';
                                        @endphp

                                        <tr class="transition hover:bg-gray-50">

                                            <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-500">
                                                {{ $loop->iteration }}
                                            </td>

                                            <td class="px-4 py-4 text-sm">

                                                <p class="font-semibold text-gray-900">
                                                    {{
                                                        $detail->labour?->full_name
                                                        ?? $detail->labour?->name
                                                        ?? 'Unknown Labour'
                                                    }}
                                                </p>

                                            </td>

                                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                                {{ $detail->labour?->labour_code ?? '-' }}
                                            </td>

                                            <td class="px-4 py-4 text-sm text-gray-700">
                                                {{
                                                    $detail->designationRole?->designation_name
                                                    ?? $detail->designationRole?->role_name
                                                    ?? $detail->designationRole?->name
                                                    ?? '-'
                                                }}
                                            </td>

                                            <td class="px-4 py-4 text-sm text-gray-700">
                                                {{
                                                    $detail->contractor?->contractor_name
                                                    ?? $detail->contractor?->name
                                                    ?? 'Company Workers'
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
                                                {{ number_format((float) ($detail->normal_hours ?? 0), 2) }}
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-900">
                                                {{ number_format((float) ($detail->ot_hours ?? 0), 2) }}
                                            </td>

                                            <td class="px-4 py-4 text-sm text-gray-700">
                                                {{ $detail->remarks ?: '-' }}
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="px-6 py-10 text-center">

                            <p class="text-sm font-medium text-gray-500">
                                No labour detail records are available in this attendance sheet.
                            </p>

                        </div>

                    @endif

                    @if($attendance->remarks)

                        <div class="border-t border-gray-200 bg-gray-50 px-5 py-4">

                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Attendance Remarks
                            </p>

                            <p class="mt-2 whitespace-pre-line text-sm text-gray-700">
                                {{ $attendance->remarks }}
                            </p>

                        </div>

                    @endif

                </div>

            @endforeach

        </div>

    @else

        <div class="px-6 py-12 text-center">

            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-500">

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
                        d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8zm8 10v-2a4 4 0 00-3-3.87m-1-7.13a4 4 0 010 7.75"
                    />
                </svg>

            </div>

            <h3 class="mt-4 text-sm font-semibold text-gray-900">
                No linked Labour Attendance
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                No submitted, approved, or reopened attendance sheet was linked to this DPR.
            </p>

        </div>

    @endif

</div>