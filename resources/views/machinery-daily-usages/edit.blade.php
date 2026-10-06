@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- ============================================================
         PAGE HEADER
    ============================================================ --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">

        <div>
            <div class="flex flex-wrap items-center gap-3">

                <h1 class="text-2xl font-bold text-gray-900">
                    Edit Daily Machinery Log #{{ $machineryDailyUsage->id }}
                </h1>

                @php
                    $statusClasses = match ($machineryDailyUsage->status) {
                        'draft' =>
                            'bg-gray-100 text-gray-700',

                        'submitted' =>
                            'bg-blue-100 text-blue-700',

                        'verified' =>
                            'bg-green-100 text-green-700',

                        'cancelled' =>
                            'bg-red-100 text-red-700',

                        default =>
                            'bg-gray-100 text-gray-700',
                    };
                @endphp

                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                    {{ ucfirst($machineryDailyUsage->status) }}
                </span>

            </div>

            <p class="mt-1 text-sm text-gray-600">
                Update machinery and equipment usage recorded at site.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">

            <a href="{{ route(
                    'machinery-daily-usages.show',
                    $machineryDailyUsage
                ) }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">

                Back to Log
            </a>

            <a href="{{ route(
                    'machinery-daily-usages.index'
                ) }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">

                Daily Logs
            </a>

        </div>
    </div>


    {{-- ============================================================
         VALIDATION ERRORS
    ============================================================ --}}
    @if ($errors->any())

        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">

            <div class="font-semibold text-red-800">
                Please correct the following:
            </div>

            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- ============================================================
         EDIT FORM
    ============================================================ --}}
    <form method="POST"
          action="{{ route(
              'machinery-daily-usages.update',
              $machineryDailyUsage
          ) }}"
          id="machinery-usage-form">

        @csrf
        @method('PUT')


        {{-- ========================================================
             SHARED MACHINERY USAGE FORM

             The same form is used by Create and Edit.

             $usage allows _form.blade.php to:
             - restore project
             - restore equipment
             - restore meter readings
             - restore working condition
             - restore Work Done
             - restore location hierarchy
             - restore operator
             - restore fuel/energy
             - restore remarks
        ======================================================== --}}
        @include(
            'machinery-daily-usages._form',
            [
                'usage' => $machineryDailyUsage,
                'selectedProjectId' =>
                    $machineryDailyUsage->project_id,
            ]
        )


        {{-- ========================================================
             FORM ACTIONS
        ======================================================== --}}
        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div class="text-xs text-gray-500">

                @if ($machineryDailyUsage->updated_at)

                    Last updated:
                    {{ $machineryDailyUsage
                        ->updated_at
                        ->format('d M Y, h:i A') }}

                @endif

            </div>


            <div class="flex flex-col-reverse gap-3 sm:flex-row">

                <a href="{{ route(
                        'machinery-daily-usages.show',
                        $machineryDailyUsage
                    ) }}"
                   class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">

                    Cancel
                </a>


                {{-- =================================================
                     SAVE AS DRAFT
                ================================================= --}}
                <button type="submit"
                        name="status"
                        value="draft"
                        class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">

                    Save as Draft
                </button>


                {{-- =================================================
                     SAVE / SUBMIT
                ================================================= --}}
                <button type="submit"
                        name="status"
                        value="submitted"
                        class="inline-flex justify-center rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">

                    Update & Submit
                </button>

            </div>

        </div>

    </form>

</div>
@endsection