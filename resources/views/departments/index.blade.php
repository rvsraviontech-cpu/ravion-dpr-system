@extends('layouts.app')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Departments
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Manage employee departments used across the Users Module.
            </p>
        </div>

        @if(auth()->user()?->hasPermission('departments.manage'))
            <a href="{{ route('departments.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                + Create Department
            </a>
        @endif
    </div>


    {{-- Alerts --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif


    {{-- Filters --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">

        <form method="GET"
              action="{{ route('departments.index') }}"
              class="grid grid-cols-1 gap-3 md:grid-cols-4">

            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Search
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Code, department, remarks..."
                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Status
                </label>

                <select name="status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Apply
                </button>

                <a href="{{ route('departments.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Reset
                </a>
            </div>

        </form>

    </div>


    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="whitespace-nowrap px-3 py-3">#</th>
                        <th class="whitespace-nowrap px-3 py-3">Code</th>
                        <th class="whitespace-nowrap px-3 py-3">Department</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center">Designations</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center">Users</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center">Sort</th>
                        <th class="whitespace-nowrap px-3 py-3">Status</th>
                        <th class="whitespace-nowrap px-3 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">

                    @forelse($departments as $department)

                        <tr class="transition hover:bg-gray-50">

                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-500">
                                {{ $departments->firstItem() + $loop->index }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3">
                                <span class="font-mono text-xs font-semibold text-gray-700">
                                    {{ $department->code }}
                                </span>
                            </td>

                            <td class="min-w-[220px] px-3 py-3">
                                <a href="{{ route('departments.show', $department->id) }}"
                                   class="text-sm font-semibold text-gray-900 hover:text-blue-600">
                                    {{ $department->name }}
                                </a>

                                @if($department->remarks)
                                    <div class="mt-0.5 max-w-md truncate text-xs text-gray-400">
                                        {{ $department->remarks }}
                                    </div>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-center text-sm font-semibold text-gray-700">
                                {{ $department->designations_count }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-center text-sm font-semibold text-gray-700">
                                {{ $department->users_count }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-center text-sm text-gray-600">
                                {{ $department->sort_order }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3">
                                @if($department->is_active)
                                    <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/20">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-right">

                                <div class="inline-flex items-center gap-1">

                                    <a href="{{ route('departments.show', $department->id) }}"
                                       class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">
                                        View
                                    </a>

                                    @if(auth()->user()?->hasPermission('departments.manage'))

                                        <a href="{{ route('departments.edit', $department->id) }}"
                                           class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">
                                            Edit
                                        </a>

                                        @if($department->is_active)
                                            <form method="POST"
                                                  action="{{ route('departments.deactivate', $department->id) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Deactivate this department? Existing user and designation links will be preserved.');">
                                                @csrf

                                                <button type="submit"
                                                        class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50">
                                                    Deactivate
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST"
                                                  action="{{ route('departments.activate', $department->id) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Activate this department?');">
                                                @csrf

                                                <button type="submit"
                                                        class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-green-700 transition hover:bg-green-50">
                                                    Activate
                                                </button>
                                            </form>
                                        @endif

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8"
                                class="px-6 py-12 text-center">
                                <div class="text-sm font-semibold text-gray-700">
                                    No departments found.
                                </div>

                                <div class="mt-1 text-xs text-gray-400">
                                    Try changing the filters or create a new department.
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if($departments->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-4 py-3">
                {{ $departments->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
