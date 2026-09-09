@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Head Office Dispatch</h1>
            <p class="mt-1 text-sm text-gray-500">Dispatch materials from Ravion Head Office / central store to a project site.</p>
        </div>
        <a href="{{ route('material-dispatches.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
            Back to Dispatch Register
        </a>
    </div>

    @include('material-dispatches.partials.form')
</div>
@endsection
