@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-bold text-gray-900">Edit Head Office Dispatch</h1>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Draft</span>
            </div>
            <p class="mt-1 text-sm text-gray-500">{{ $materialDispatch->dispatch_number }}</p>
        </div>
        <a href="{{ route('material-dispatches.show', $materialDispatch) }}"
           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
            Cancel Edit
        </a>
    </div>

    @include('material-dispatches.partials.form')
</div>
@endsection
