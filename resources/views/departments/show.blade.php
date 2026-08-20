@extends('layouts.app')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $department->name }}
                </h1>

                @if($department->is_active)
                    <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">
                        Active
                    </span>
                @else
                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/20">
                        Inactive
                    </span>
                @endif
            </div>

            <div class="mt-1 font-mono text-sm font-semibold text-gray-500">
                {{ $department->code }}
            </div>
        </div>


        <div class="flex flex-wrap gap-2">

            <a href="{{ route('departments.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Back
            </a>

            @if(auth()->user()?->hasPermission('departments.manage'))
                <a href="{{ route('departments.edit', $department->id) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Edit Department
                </a>
            @endif

        </div>

    </div>


    {{-- Alerts --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif


    {{-- Overview --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Department Details
                </h2>
            </div>

            <div class="divide-y divide-gray-100">

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Code</div>
                    <div class="col-span-2 text-sm font-semibold text-gray-900">
                        {{ $department->code }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Name</div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $department->name }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Sort Order</div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $department->sort_order }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</div>
                    <div class="col-span-2 text-sm font-semibold text-gray-900">
                        {{ $department->is_active ? 'Active' : 'Inactive' }}
                    </div>
                </div>

            </div>

        </div>


        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Usage Summary
                </h2>
            </div>

            <div class="divide-y divide-gray-100">

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Designations
                    </div>
                    <div class="col-span-2 text-sm font-semibold text-gray-900">
                        {{ $department->designations->count() }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Users
                    </div>
                    <div class="col-span-2 text-sm font-semibold text-gray-900">
                        {{ $department->users->count() }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Created
                    </div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $department->created_at?->format('d M Y, h:i A') ?? '—' }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Updated
                    </div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $department->updated_at?->format('d M Y, h:i A') ?? '—' }}
                    </div>
                </div>

            </div>

        </div>

    </div>


    {{-- Designations --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="flex flex-col gap-2 border-b border-gray-200 bg-gray-50 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">
                    Employee Designations
                </h2>

                <p class="mt-0.5 text-xs text-gray-500">
                    Designations linked to this department.
                </p>
            </div>

            @if(auth()->user()?->hasPermission('employee_designations.manage'))
                <a href="{{ route('employee-designations.create', ['department_id' => $department->id]) }}"
                   class="text-xs font-semibold text-blue-700 hover:text-blue-800">
                    + Add Designation
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-white">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Designation</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse($department->designations as $designation)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">
                                {{ $designation->code }}
                            </td>

                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                {{ $designation->name }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $designation->is_active ? 'Active' : 'Inactive' }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('employee-designations.show', $designation->id) }}"
                                   class="text-xs font-semibold text-blue-700 hover:text-blue-800">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4"
                                class="px-4 py-8 text-center text-sm text-gray-500">
                                No designations linked to this department.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- Assigned Users --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
            <h2 class="text-sm font-semibold text-gray-900">
                Assigned Users
            </h2>

            <p class="mt-0.5 text-xs text-gray-500">
                Users currently mapped to this department.
            </p>
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-white">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Designation</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse($department->users as $user)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">
                                {{ $user->employee_code ?? '—' }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $user->name }}
                                </div>

                                <div class="text-xs text-gray-500">
                                    {{ $user->email }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $user->employeeDesignation?->name ?? $user->designation ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $user->account_status }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('users.show', $user->id) }}"
                                   class="text-xs font-semibold text-blue-700 hover:text-blue-800">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="px-4 py-8 text-center text-sm text-gray-500">
                                No users assigned to this department.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- Remarks --}}
    @if($department->remarks)
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Remarks
                </h2>
            </div>

            <div class="px-5 py-4 text-sm leading-6 text-gray-700">
                {{ $department->remarks }}
            </div>
        </div>
    @endif


    {{-- Status Action --}}
    @if(auth()->user()?->hasPermission('departments.manage'))
        <div class="flex justify-end">

            @if($department->is_active)
                <form method="POST"
                      action="{{ route('departments.deactivate', $department->id) }}"
                      onsubmit="return confirm('Deactivate this department? Existing user and designation links will be preserved.');">
                    @csrf

                    <button type="submit"
                            class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                        Deactivate Department
                    </button>
                </form>
            @else
                <form method="POST"
                      action="{{ route('departments.activate', $department->id) }}"
                      onsubmit="return confirm('Activate this department?');">
                    @csrf

                    <button type="submit"
                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                        Activate Department
                    </button>
                </form>
            @endif

        </div>
    @endif

</div>

@endsection
