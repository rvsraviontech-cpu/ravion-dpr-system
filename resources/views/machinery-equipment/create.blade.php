@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Register Machinery / Equipment
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Register company-owned, rented or contractor-provided equipment for Ravion projects.
            </p>
        </div>

        <a
            href="{{ route('machinery-equipment.index') }}"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50"
        >
            Back to Equipment Register
        </a>

    </div>

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
            <div class="text-sm font-semibold text-red-800">
                Please correct the following:
            </div>

            <ul class="mt-2 list-disc pl-5 text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('machinery-equipment.store') }}"
    >
        @csrf

        @include('machinery-equipment._form', [
            'submitLabel' => 'Register Equipment',
        ])
    </form>

</div>

@endsection