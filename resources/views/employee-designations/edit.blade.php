@extends('layouts.app')

@section('content')

<div class="space-y-5">

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Edit Employee Designation
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Update designation details and department mapping.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('employee-designations.show', $designation->id) }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                View Designation
            </a>

            <a href="{{ route('employee-designations.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Back to Designations
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="font-semibold">
                Please correct the following:
            </div>

            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('employee-designations.update', $designation->id) }}"
          class="space-y-5">

        @csrf

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Designation Details
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Designation Code <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           name="code"
                           value="{{ old('code', $designation->code) }}"
                           required
                           maxlength="30"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm uppercase text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Designation Name <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           name="name"
                           value="{{ old('name', $designation->name) }}"
                           required
                           maxlength="150"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Department <span class="text-red-500">*</span>
                    </label>

                    <select name="department_id"
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                        <option value="">Select Department</option>

                        @foreach($departments as $department)
                            <option value="{{ $department->id }}"
                                {{ (string) old('department_id', $designation->department_id) === (string) $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                                {{ !$department->is_active ? ' (Inactive)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Sort Order
                    </label>

                    <input type="number"
                           name="sort_order"
                           value="{{ old('sort_order', $designation->sort_order) }}"
                           min="0"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Status
                    </label>

                    <label class="flex h-[42px] cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               {{ old('is_active', $designation->is_active) ? 'checked' : '' }}>

                        <span class="text-sm font-medium text-gray-800">
                            Active
                        </span>
                    </label>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Remarks
                    </label>

                    <textarea name="remarks"
                              rows="3"
                              placeholder="Optional notes..."
                              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">{{ old('remarks', $designation->remarks) }}</textarea>
                </div>

            </div>

        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end">

            <a href="{{ route('employee-designations.show', $designation->id) }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Cancel
            </a>

            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Update Designation
            </button>

        </div>

    </form>

</div>

@endsection
