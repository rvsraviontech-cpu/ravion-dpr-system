@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Daily Machinery Log
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                Record machinery and equipment usage at site.
            </p>
        </div>

        <a href="{{ route('machinery-daily-usages.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Back to Daily Logs
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
            <div class="font-semibold text-red-800">
                Please correct the following:
            </div>

            <ul class="mt-2 list-disc pl-5 text-sm text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('machinery-daily-usages.store') }}"
          id="machinery-usage-form">
        @csrf

        @include('machinery-daily-usages._form', [
            'usage' => null,
        ])

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('machinery-daily-usages.index') }}"
               class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancel
            </a>

            <button type="submit"
                    name="status"
                    value="draft"
                    class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Save Draft
            </button>

            <button type="submit"
                    name="status"
                    value="submitted"
                    class="inline-flex justify-center rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                Submit Daily Log
            </button>
        </div>
    </form>
</div>
@endsection