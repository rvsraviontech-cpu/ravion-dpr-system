@extends('layouts.app')

@section('content')

<div class="mx-auto max-w-full">
    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Create Material Requirement</h1>
            <p class="mt-1 text-sm text-gray-500">
                Request one or more Products for procurement. New entries are saved as Draft.
            </p>
        </div>

        <a href="{{ route('material-requirements.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Back
        </a>
    </div>

    @include('material-requirements._form')
</div>

@endsection
