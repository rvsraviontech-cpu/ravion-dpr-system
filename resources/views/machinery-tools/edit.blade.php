@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Edit Machinery / Equipment Type
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                {{ $machineryTool->code }} — {{ $machineryTool->machine_name }}
            </p>
        </div>

        <a
            href="{{ route('machinery-tools.index') }}"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50"
        >
            Back to Master
        </a>
    </div>

    <form
        method="POST"
        action="{{ route('machinery-tools.update', $machineryTool) }}"
        class="bg-white border border-gray-200 rounded-xl shadow-sm"
    >
        @csrf
        @method('PUT')

        <div class="p-6">
            @include('machinery-tools._form', [
                'machineryTool' => $machineryTool
            ])
        </div>

        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl flex justify-end gap-3">

            <a
                href="{{ route('machinery-tools.index') }}"
                class="px-5 py-2.5 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="px-5 py-2.5 rounded-lg bg-[#0F2A52] text-white text-sm font-semibold hover:opacity-90"
            >
                Update Equipment Type
            </button>

        </div>
    </form>

</div>

@endsection