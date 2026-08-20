@extends('layouts.app')

@section('content')

<div class="space-y-5">

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Employee Designations
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Manage controlled employee designations used in the Users Module.
            </p>
        </div>

        @if(auth()->user()?->hasPermission('employee_designations.manage'))
            <a href="{{ route('employee-designations.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                + Create Designation
            </a>
        @endif
    </div>

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

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">

        <form method="GET"
              action="{{ route('employee-designations.index') }}"
              class="grid grid-cols-1 gap-3 md:grid-cols-4">

            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Search
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Code, designation, department..."
                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Department
                </label>

                <select name="department_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                    <option value="">All Departments</option>

                    @foreach($departments as $department)
                        <option value="{{ $department->id }}"
                            {{ (string) request('department_id') === (string) $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Status
                </label>

                <select name="status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex items-end gap-2 md:col-span-4">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Apply
                </button>

                <a href="{{ route('employee-designations.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Reset
                </a>
            </div>

        </form>

    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="whitespace-nowrap px-3 py-3">#</th>
                        <th class="whitespace-nowrap px-3 py-3">Code</th>
                        <th class="whitespace-nowrap px-3 py-3">Designation</th>
                        <th class="whitespace-nowrap px-3 py-3">Department</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center">Users</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center">Sort</th>
                        <th class="whitespace-nowrap px-3 py-3">Status</th>
                        <th class="whitespace-nowrap px-3 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">

                    @forelse($designations as $designation)

                        <tr class="transition hover:bg-gray-50">

                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-500">
                                {{ $designations->firstItem() + $loop->index }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3">
                                <span class="font-mono text-xs font-semibold text-gray-700">
                                    {{ $designation->code }}
                                </span>
                            </td>

                            <td class="min-w-[220px] px-3 py-3">
                                <a href="{{ route('employee-designations.show', $designation->id) }}"
                                   class="text-sm font-semibold text-gray-900 hover:text-blue-600">
                                    {{ $designation->name }}
                                </a>

                                @if($designation->remarks)
                                    <div class="mt-0.5 max-w-md truncate text-xs text-gray-400">
                                        {{ $designation->remarks }}
                                    </div>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-700">
                                {{ $designation->department?->name ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-center text-sm font-semibold text-gray-700">
                                {{ $designation->users_count }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-center text-sm text-gray-600">
                                {{ $designation->sort_order }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3">
                                @if($designation->is_active)
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

                                    <a href="{{ route('employee-designations.show', $designation->id) }}"
                                       class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">
                                        View
                                    </a>

                                    @if(auth()->user()?->hasPermission('employee_designations.manage'))

                                        <a href="{{ route('employee-designations.edit', $designation->id) }}"
                                           class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">
                                            Edit
                                        </a>

                                        @if($designation->is_active)
                                            <form method="POST"
                                                  action="{{ route('employee-designations.deactivate', $designation->id) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Deactivate this designation? Existing user links will be preserved.');">
                                                @csrf

                                                <button type="submit"
                                                        class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50">
                                                    Deactivate
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST"
                                                  action="{{ route('employee-designations.activate', $designation->id) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Activate this designation?');">
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
                                    No employee designations found.
                                </div>

                                <div class="mt-1 text-xs text-gray-400">
                                    Try changing the filters or create a new designation.
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($designations->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-4 py-3">
                {{ $designations->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
