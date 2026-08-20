@extends('layouts.app')

@section('content')

<div class="space-y-5">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $designation->name }}
                </h1>

                @if($designation->is_active)
                    <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">
                        Active
                    </span>
                @else
                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/20">
                        Inactive
                    </span>
                @endif
            </div>

            <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                <span class="font-mono font-semibold">
                    {{ $designation->code }}
                </span>

                @if($designation->department)
                    <span>•</span>
                    <span>{{ $designation->department->name }}</span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap gap-2">

            <a href="{{ route('employee-designations.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Back
            </a>

            @if(auth()->user()?->hasPermission('employee_designations.manage'))
                <a href="{{ route('employee-designations.edit', $designation->id) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Edit Designation
                </a>
            @endif

        </div>

    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Designation Details
                </h2>
            </div>

            <div class="divide-y divide-gray-100">

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Code
                    </div>
                    <div class="col-span-2 text-sm font-semibold text-gray-900">
                        {{ $designation->code }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Designation
                    </div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $designation->name }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Department
                    </div>
                    <div class="col-span-2">
                        @if($designation->department)
                            <a href="{{ route('departments.show', $designation->department->id) }}"
                               class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                                {{ $designation->department->name }}
                            </a>
                        @else
                            <span class="text-sm text-gray-500">—</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Sort Order
                    </div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $designation->sort_order }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Status
                    </div>
                    <div class="col-span-2 text-sm font-semibold text-gray-900">
                        {{ $designation->is_active ? 'Active' : 'Inactive' }}
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
                        Users
                    </div>
                    <div class="col-span-2 text-sm font-semibold text-gray-900">
                        {{ $designation->users->count() }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Created
                    </div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $designation->created_at?->format('d M Y, h:i A') ?? '—' }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Updated
                    </div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $designation->updated_at?->format('d M Y, h:i A') ?? '—' }}
                    </div>
                </div>

            </div>

        </div>

    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
            <h2 class="text-sm font-semibold text-gray-900">
                Assigned Users
            </h2>

            <p class="mt-0.5 text-xs text-gray-500">
                Users currently assigned to this employee designation.
            </p>
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-white">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse($designation->users as $user)

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
                                {{ $user->role?->name ?? '—' }}
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
                                No users assigned to this designation.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    @if($designation->remarks)
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Remarks
                </h2>
            </div>

            <div class="px-5 py-4 text-sm leading-6 text-gray-700">
                {{ $designation->remarks }}
            </div>

        </div>
    @endif

    @if(auth()->user()?->hasPermission('employee_designations.manage'))

        <div class="flex justify-end">

            @if($designation->is_active)
                <form method="POST"
                      action="{{ route('employee-designations.deactivate', $designation->id) }}"
                      onsubmit="return confirm('Deactivate this designation? Existing user links will be preserved.');">
                    @csrf

                    <button type="submit"
                            class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                        Deactivate Designation
                    </button>
                </form>
            @else
                <form method="POST"
                      action="{{ route('employee-designations.activate', $designation->id) }}"
                      onsubmit="return confirm('Activate this designation?');">
                    @csrf

                    <button type="submit"
                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                        Activate Designation
                    </button>
                </form>
            @endif

        </div>

    @endif

</div>

@endsection
