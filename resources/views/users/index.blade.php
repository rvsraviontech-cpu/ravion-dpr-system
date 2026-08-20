@extends('layouts.app')

@section('content')

<div class="space-y-5">

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Users
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Manage ERP users, roles, project access and account status.
            </p>
        </div>

        @if(auth()->user()?->hasPermission('users.manage'))
            <a href="{{ route('users.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800">
                + Create User
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
              action="{{ route('users.index') }}"
              class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">

            {{-- Search --}}
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Search
                </label>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Name, code, email, mobile..."
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200"
                >
            </div>


            {{-- Role --}}
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Role
                </label>

                <select
                    name="role_id"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200"
                >
                    <option value="">All Roles</option>

                    @foreach($roles as $role)
                        <option value="{{ $role->id }}"
                            {{ (string) request('role_id') === (string) $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Department --}}
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Department
                </label>

                <select
                    name="department"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200"
                >
                    <option value="">All Departments</option>

                    @foreach($departments as $department)
                        <option value="{{ $department }}"
                            {{ request('department') === $department ? 'selected' : '' }}>
                            {{ $department }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Project --}}
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Project
                </label>

                <select
                    name="project_id"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200"
                >
                    <option value="">All Projects</option>

                    @foreach($projects as $project)
                        <option value="{{ $project->id }}"
                            {{ (string) request('project_id') === (string) $project->id ? 'selected' : '' }}>
                            {{ $project->project_code }} - {{ $project->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Status --}}
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Status
                </label>

                <select
                    name="account_status"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200"
                >
                    <option value="">All Statuses</option>

                    @foreach(['Active', 'Inactive', 'Suspended', 'Exited'] as $status)
                        <option value="{{ $status }}"
                            {{ request('account_status') === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Actions --}}
            <div class="flex items-end gap-2 xl:col-span-6">

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800"
                >
                    Apply Filters
                </button>

                <a href="{{ route('users.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    Reset
                </a>

            </div>

        </form>

    </div>


    {{-- Users Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">

                        <th class="whitespace-nowrap px-3 py-3">
                            #
                        </th>

                        <th class="whitespace-nowrap px-3 py-3">
                            Code
                        </th>

                        <th class="whitespace-nowrap px-3 py-3">
                            User
                        </th>

                        <th class="whitespace-nowrap px-3 py-3">
                            Mobile
                        </th>

                        <th class="whitespace-nowrap px-3 py-3">
                            Role
                        </th>

                        <th class="whitespace-nowrap px-3 py-3">
                            Department
                        </th>

                        <th class="whitespace-nowrap px-3 py-3">
                            Projects
                        </th>

                        <th class="whitespace-nowrap px-3 py-3">
                            Status
                        </th>

                        <th class="whitespace-nowrap px-3 py-3">
                            Last Login
                        </th>

                        <th class="whitespace-nowrap px-3 py-3 text-right">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-100 bg-white">

                    @forelse($users as $user)

                        <tr class="transition hover:bg-gray-50">

                            {{-- # --}}
                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-500">
                                {{ $users->firstItem() + $loop->index }}
                            </td>


                            {{-- Code --}}
                            <td class="whitespace-nowrap px-3 py-3">

                                <span class="font-mono text-xs font-semibold text-gray-700">
                                    {{ $user->employee_code ?? '—' }}
                                </span>

                            </td>


                            {{-- User --}}
                            <td class="min-w-[220px] px-3 py-3">

                                <div class="flex items-center gap-3">

                                    {{-- Avatar --}}
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-700">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>

                                    <div class="min-w-0">

                                        <a href="{{ route('users.show', $user->id) }}"
                                           class="block truncate text-sm font-semibold text-gray-900 hover:text-blue-600">
                                            {{ $user->name }}
                                        </a>

                                        <div class="mt-0.5 truncate text-xs text-gray-500">
                                            {{ $user->email }}
                                        </div>

                                        @if($user->designation)
                                            <div class="mt-0.5 truncate text-xs text-gray-400">
                                                {{ $user->designation }}
                                            </div>
                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- Mobile --}}
                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-700">
                                {{ $user->mobile ?: '—' }}
                            </td>


                            {{-- Role --}}
                            <td class="whitespace-nowrap px-3 py-3">

                                <span class="inline-flex rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">
                                    {{ $user->role?->name ?? 'No Role' }}
                                </span>

                            </td>


                            {{-- Department --}}
                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-700">
                                {{ $user->department ?: '—' }}
                            </td>


                            {{-- Projects --}}
                            <td class="whitespace-nowrap px-3 py-3">

                                @if($user->project_access_scope === 'all')

                                    <span class="inline-flex rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">
                                        All Projects
                                    </span>

                                @elseif($user->projects_count > 0)

                                    <span class="inline-flex rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700"
                                          title="{{ $user->projects->pluck('project_name')->implode(', ') }}">
                                        {{ $user->projects_count }}
                                        {{ Str::plural('Project', $user->projects_count) }}
                                    </span>

                                @else

                                    <span class="text-xs text-gray-400">
                                        No Projects
                                    </span>

                                @endif

                            </td>


                            {{-- Status --}}
                            <td class="whitespace-nowrap px-3 py-3">

                                @php
                                    $statusClasses = match($user->account_status) {
                                        'Active' => 'bg-green-50 text-green-700 ring-green-600/20',
                                        'Inactive' => 'bg-gray-100 text-gray-600 ring-gray-500/20',
                                        'Suspended' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                        'Exited' => 'bg-red-50 text-red-700 ring-red-600/20',
                                        default => 'bg-gray-100 text-gray-600 ring-gray-500/20',
                                    };
                                @endphp

                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}">
                                    {{ $user->account_status }}
                                </span>

                            </td>


                            {{-- Last Login --}}
                            <td class="whitespace-nowrap px-3 py-3">

                                @if($user->last_login_at)

                                    <div class="text-sm text-gray-700">
                                        {{ $user->last_login_at->format('d M Y') }}
                                    </div>

                                    <div class="text-xs text-gray-400">
                                        {{ $user->last_login_at->format('h:i A') }}
                                    </div>

                                @else

                                    <span class="text-xs text-gray-400">
                                        Never
                                    </span>

                                @endif

                            </td>


                            {{-- Actions --}}
                            <td class="whitespace-nowrap px-3 py-3 text-right">

                                <div class="inline-flex items-center gap-1">

                                    {{-- View --}}
                                    <a href="{{ route('users.show', $user->id) }}"
                                       class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">
                                        View
                                    </a>

                                    @if(auth()->user()?->hasPermission('users.manage'))

                                        {{-- Edit --}}
                                        <a href="{{ route('users.edit', $user->id) }}"
                                           class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">
                                            Edit
                                        </a>

                                        {{-- Password --}}
                                        <a href="{{ route('users.password', $user->id) }}"
                                           class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-purple-700 transition hover:bg-purple-50">
                                            Password
                                        </a>

                                        {{-- Status Actions --}}
                                        @if($user->account_status === 'Active' && auth()->id() !== $user->id)

                                            <form method="POST"
                                                  action="{{ route('users.deactivate', $user->id) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Deactivate this user account?');">

                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50"
                                                >
                                                    Deactivate
                                                </button>

                                            </form>

                                        @elseif($user->account_status !== 'Active')

                                            <form method="POST"
                                                  action="{{ route('users.activate', $user->id) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Activate this user account?');">

                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-green-700 transition hover:bg-green-50"
                                                >
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
                            <td colspan="10"
                                class="px-6 py-12 text-center">

                                <div class="text-sm font-semibold text-gray-700">
                                    No users found.
                                </div>

                                <div class="mt-1 text-xs text-gray-400">
                                    Try changing your search or filter criteria.
                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if($users->hasPages())

            <div class="border-t border-gray-200 bg-gray-50 px-4 py-3">

                {{ $users->links() }}

            </div>

        @endif

    </div>

</div>

@endsection