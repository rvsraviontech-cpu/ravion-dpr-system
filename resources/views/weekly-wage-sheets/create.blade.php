@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Create Weekly Wage Sheet"
    subtitle="Create a project-wise weekly wage sheet for a Sunday-to-Saturday period."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('weekly-wage-sheets.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="POST"
    action="{{ route('weekly-wage-sheets.store') }}"
    x-data="weeklyWageSheetForm(
        @js(old('week_start_date', $defaultWeekStart))
    )"
>
    @csrf

    <div class="space-y-6">

        <x-rds.section
            title="Wage Sheet Information"
            description="Select the project and the Sunday on which the wage week begins."
        >
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <x-rds.select
                    name="project_id"
                    label="Project"
                    required
                >
                    <option value="">
                        Select Project
                    </option>

                    @foreach($projects as $project)
                        <option
                            value="{{ $project->id }}"
                            @selected(
                                (string) old('project_id')
                                === (string) $project->id
                            )
                        >
                            {{ $project->project_name }}
                            @if($project->project_code)
                                — {{ $project->project_code }}
                            @endif
                        </option>
                    @endforeach
                </x-rds.select>

                <div>
                    <x-rds.input
                        name="week_start_date"
                        label="Week Starting Sunday"
                        type="date"
                        value="{{ old('week_start_date', $defaultWeekStart) }}"
                        x-model="weekStart"
                        x-on:change="updateWeekEnd"
                        required
                    />

                    <p
                        class="mt-1 text-xs"
                        :class="isSunday
                            ? 'text-gray-500'
                            : 'font-semibold text-red-600'"
                        x-text="weekMessage"
                    ></p>
                </div>

                <div class="md:col-span-2">
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                                    Week Start
                                </div>

                                <div
                                    class="mt-1 text-sm font-bold text-blue-950"
                                    x-text="formattedWeekStart"
                                ></div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                                    Week End
                                </div>

                                <div
                                    class="mt-1 text-sm font-bold text-blue-950"
                                    x-text="formattedWeekEnd"
                                ></div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                                    Wage Period
                                </div>

                                <div class="mt-1 text-sm font-bold text-blue-950">
                                    Sunday to Saturday
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <x-rds.textarea
                        name="remarks"
                        label="Remarks"
                        rows="3"
                        value="{{ old('remarks') }}"
                        placeholder="Optional notes about this weekly wage sheet."
                    />
                </div>

            </div>
        </x-rds.section>

        <x-rds.section
            title="Calculation Method"
            description="The wage sheet will be generated from approved labour attendance for the selected project and week."
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Payable Days
                    </div>

                    <div class="mt-2 text-sm font-semibold text-gray-900">
                        Full Days + Half Days
                    </div>

                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        Attendance Status payable factors decide whether a day counts as 1.0, 0.5, or 0.0.
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Normal Wage
                    </div>

                    <div class="mt-2 text-sm font-semibold text-gray-900">
                        Payable Days × Daily Rate
                    </div>

                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        The labour's current wage rate is copied into the wage sheet as a historical snapshot.
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        OT Wage
                    </div>

                    <div class="mt-2 text-sm font-semibold text-gray-900">
                        OT Hours × OT Hourly Rate
                    </div>

                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        Standard OT rate is Daily Wage ÷ Normal Shift Hours unless another OT method is configured.
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Total Payable
                    </div>

                    <div class="mt-2 text-sm font-semibold text-gray-900">
                        Wages + Charges − Deductions
                    </div>

                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        Labour adjustments and site charges can be added before submission.
                    </p>
                </div>

            </div>
        </x-rds.section>

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div class="text-xs text-gray-500">
                    The wage sheet will first be created as a Draft. Use Calculate on the next page to load approved attendance.
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row">

                    <x-rds.button
                        href="{{ route('weekly-wage-sheets.index') }}"
                        variant="secondary"
                    >
                        Cancel
                    </x-rds.button>

                    <x-rds.button
                        type="submit"
                        variant="primary"
                        x-bind:disabled="! isSunday"
                    >
                        Create Draft Wage Sheet
                    </x-rds.button>

                </div>
            </div>
        </x-rds.card>

    </div>
</form>

<script>
    function weeklyWageSheetForm(initialWeekStart) {
        return {
            weekStart: initialWeekStart || '',
            weekEnd: '',
            isSunday: true,

            init() {
                this.updateWeekEnd();
            },

            updateWeekEnd() {
                if (! this.weekStart) {
                    this.weekEnd = '';
                    this.isSunday = false;

                    return;
                }

                const start = this.parseLocalDate(
                    this.weekStart
                );

                if (! start) {
                    this.weekEnd = '';
                    this.isSunday = false;

                    return;
                }

                this.isSunday = start.getDay() === 0;

                const end = new Date(start);
                end.setDate(end.getDate() + 6);

                this.weekEnd = this.toDateInputValue(
                    end
                );
            },

            parseLocalDate(value) {
                const parts = value.split('-');

                if (parts.length !== 3) {
                    return null;
                }

                const year = Number(parts[0]);
                const month = Number(parts[1]) - 1;
                const day = Number(parts[2]);

                const date = new Date(
                    year,
                    month,
                    day
                );

                return Number.isNaN(date.getTime())
                    ? null
                    : date;
            },

            toDateInputValue(date) {
                const year = date.getFullYear();

                const month = String(
                    date.getMonth() + 1
                ).padStart(2, '0');

                const day = String(
                    date.getDate()
                ).padStart(2, '0');

                return `${year}-${month}-${day}`;
            },

            formatDisplayDate(value) {
                if (! value) {
                    return 'Not selected';
                }

                const date = this.parseLocalDate(
                    value
                );

                if (! date) {
                    return 'Invalid date';
                }

                return new Intl.DateTimeFormat(
                    'en-IN',
                    {
                        weekday: 'long',
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                    }
                ).format(date);
            },

            get formattedWeekStart() {
                return this.formatDisplayDate(
                    this.weekStart
                );
            },

            get formattedWeekEnd() {
                return this.formatDisplayDate(
                    this.weekEnd
                );
            },

            get weekMessage() {
                if (! this.weekStart) {
                    return 'Select the Sunday on which this wage week begins.';
                }

                if (! this.isSunday) {
                    return 'Week Start Date must be a Sunday.';
                }

                return 'The wage period will end automatically on the following Saturday.';
            },
        };
    }
</script>

@endsection
