@extends('layouts.app')

@section('content')

<div class="mx-auto max-w-full">
    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Edit Material Requirement #{{ $materialRequirement->id }}
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Update the Draft requirement. Fulfilment remains system-controlled.
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('material-requirements.show', $materialRequirement) }}"
               class="inline-flex items-center justify-center rounded-lg bg-[#10212F] px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                View
            </a>

            <a href="{{ route('material-requirements.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Back
            </a>
        </div>
    </div>

    @include('material-requirements._form')
</div>

@endsection
