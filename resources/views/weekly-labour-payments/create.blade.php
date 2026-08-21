@extends('layouts.app')

@section('content')

<div class="mx-auto max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">Create Weekly Labour Payment Register</h1>
        <p class="mt-1 text-sm text-gray-500">
            One register consolidates approved labour attendance across all projects for the selected Sunday–Saturday week.
        </p>
    </div>

    <x-rds.alert />

    <form method="POST" action="{{ route('weekly-labour-payments.store') }}">
        @csrf

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-[#0F2A52] px-5 py-4 text-white">
                <h2 class="font-bold">Register Period</h2>
                <p class="mt-1 text-xs text-blue-100">Choose the Sunday that starts the wage week.</p>
            </div>

            <div class="space-y-5 p-5">
                <div>
                    <label for="week_start_date" class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Week Start Date <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="week_start_date"
                        type="date"
                        name="week_start_date"
                        value="{{ old('week_start_date', $defaultWeekStart) }}"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                    >
                    <p class="mt-1 text-xs text-gray-500">Must be a Sunday. The register will automatically end on Saturday.</p>
                    @error('week_start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="remarks" class="mb-1.5 block text-sm font-semibold text-gray-700">Remarks</label>
                    <textarea
                        id="remarks"
                        name="remarks"
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                        placeholder="Optional register remarks"
                    >{{ old('remarks') }}</textarea>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-gray-200 bg-gray-50 px-5 py-4 sm:flex-row sm:justify-end">
                <a href="{{ route('weekly-labour-payments.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-[#0F2A52] px-5 py-2.5 text-sm font-semibold text-white">
                    Create Register
                </button>
            </div>
        </div>
    </form>
</div>

@endsection
