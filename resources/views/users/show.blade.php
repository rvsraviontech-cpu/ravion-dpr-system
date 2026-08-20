@extends('layouts.app')

@section('content')

<div class="space-y-5">

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $user->name }}
                </h1>

                @php
                    $statusClasses = match($user->account_status) {
                        'Active' => 'bg-green-50 text-green-700 ring-green-600/20',
                        'Inactive' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
                        'Suspended' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                        'Exited' => 'bg-red-50 text-red-700 ring-red-600/20',
                        default => 'bg-gray-100 text-gray-700 ring-gray-500/20',
                    };
                @endphp

                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}">
                    {{ $user->account_status }}
                </span>
            </div>

            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                <span class="font-mono font-semibold text-gray-700">
                    {{ $user->employee_code ?? '—' }}
                </span>

                @if($user->employeeDesignation?->name || $user->designation)
                    <span>•</span>
                    <span>{{ $user->employeeDesignation?->name ?? $user->designation }}</span>
                @endif

                @if($user->departmentMaster?->name || $user->department)
                    <span>•</span>
                    <span>{{ $user->departmentMaster?->name ?? $user->department }}</span>
                @endif
            </div>
        </div>


        <div class="flex flex-wrap gap-2">

            <a href="{{ route('users.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Back
            </a>

            @if(auth()->user()?->hasPermission('users.manage'))

                <a href="{{ route('users.edit', $user->id) }}"
                   class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                    Edit User
                </a>

                <a href="{{ route('users.password', $user->id) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Manage Password
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

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif


    {{-- Main Details --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

        {{-- Employee Information --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Employee Information
                </h2>
            </div>

            <div class="divide-y divide-gray-100">

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Employee Code</div>
                    <div class="col-span-2 text-sm font-semibold text-gray-900">{{ $user->employee_code ?? '—' }}</div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Full Name</div>
                    <div class="col-span-2 text-sm text-gray-900">{{ $user->name }}</div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Mobile</div>
                    <div class="col-span-2 text-sm text-gray-900">{{ $user->mobile ?: '—' }}</div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Email</div>
                    <div class="col-span-2 text-sm text-gray-900">{{ $user->email }}</div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Department</div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $user->departmentMaster?->name ?? $user->department ?? '—' }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Designation</div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $user->employeeDesignation?->name ?? $user->designation ?? '—' }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Joining Date</div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $user->joining_date?->format('d M Y') ?? '—' }}
                    </div>
                </div>

            </div>

        </div>


        {{-- ERP Access --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    ERP Access
                </h2>
            </div>

            <div class="divide-y divide-gray-100">

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Role</div>
                    <div class="col-span-2">
                        <span class="inline-flex rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">
                            {{ $user->role?->name ?? 'No Role' }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Project Access</div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $user->project_access_scope === 'all' ? 'All Projects' : 'Selected Projects' }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Assigned Projects</div>

                    <div class="col-span-2">
                        @if($user->project_access_scope === 'all')
                            <span class="text-sm font-medium text-blue-700">All projects</span>
                        @elseif($user->projects->isNotEmpty())
                            <div class="space-y-1.5">
                                @foreach($user->projects as $project)
                                    <div class="text-sm text-gray-900">
                                        <span class="font-semibold">{{ $project->project_code }}</span>
                                        <span class="text-gray-400">—</span>
                                        {{ $project->project_name }}
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-sm text-gray-500">No projects assigned</span>
                        @endif
                    </div>
                </div>

            </div>

        </div>


        {{-- Security --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Security
                </h2>
            </div>

            <div class="divide-y divide-gray-100">

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Last Login</div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $user->last_login_at?->format('d M Y, h:i A') ?? 'Never' }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Last Login IP</div>
                    <div class="col-span-2 text-sm text-gray-900">{{ $user->last_login_ip ?: '—' }}</div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Password Changed</div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $user->password_changed_at?->format('d M Y, h:i A') ?? 'Not recorded' }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Change Required</div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $user->must_change_password ? 'Yes' : 'No' }}
                    </div>
                </div>

            </div>

        </div>


        {{-- Account Information --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Account Information
                </h2>
            </div>

            <div class="divide-y divide-gray-100">

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</div>
                    <div class="col-span-2 text-sm font-semibold text-gray-900">{{ $user->account_status }}</div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Created</div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $user->created_at?->format('d M Y, h:i A') ?? '—' }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 px-5 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Updated</div>
                    <div class="col-span-2 text-sm text-gray-900">
                        {{ $user->updated_at?->format('d M Y, h:i A') ?? '—' }}
                    </div>
                </div>

                @if($user->deactivated_at)
                    <div class="grid grid-cols-3 gap-4 px-5 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Disabled At</div>
                        <div class="col-span-2 text-sm text-gray-900">
                            {{ $user->deactivated_at->format('d M Y, h:i A') }}
                        </div>
                    </div>
                @endif

                @if($user->deactivatedBy)
                    <div class="grid grid-cols-3 gap-4 px-5 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Changed By</div>
                        <div class="col-span-2 text-sm text-gray-900">
                            {{ $user->deactivatedBy->name }}
                        </div>
                    </div>
                @endif

                @if($user->exit_date)
                    <div class="grid grid-cols-3 gap-4 px-5 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Exit Date</div>
                        <div class="col-span-2 text-sm text-gray-900">
                            {{ $user->exit_date->format('d M Y') }}
                        </div>
                    </div>
                @endif

            </div>

        </div>

    </div>


    {{-- Remarks --}}
    @if($user->remarks)
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Remarks
                </h2>
            </div>

            <div class="px-5 py-4 text-sm leading-6 text-gray-700">
                {{ $user->remarks }}
            </div>

        </div>
    @endif


    {{-- Account Actions --}}
    @if(auth()->user()?->hasPermission('users.manage') && auth()->id() !== $user->id)

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Account Actions
                </h2>
                <p class="mt-0.5 text-xs text-gray-500">
                    Manage this user's ERP account status. Historical records are preserved.
                </p>
            </div>

            <div class="flex flex-wrap gap-3 p-5">

                @if($user->account_status !== 'Active')
                    <form method="POST" action="{{ route('users.activate', $user->id) }}">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Activate this user account?')"
                                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                            Activate
                        </button>
                    </form>
                @endif

                @if($user->account_status === 'Active')
                    <form method="POST" action="{{ route('users.deactivate', $user->id) }}">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Deactivate this user account?')"
                                class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                            Deactivate
                        </button>
                    </form>

                    <form method="POST" action="{{ route('users.suspend', $user->id) }}">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Suspend this user account?')"
                                class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                            Suspend
                        </button>
                    </form>
                @endif

                @if($user->account_status !== 'Exited')
                    <form method="POST"
                          action="{{ route('users.exit', $user->id) }}"
                          class="flex flex-wrap items-center gap-2">
                        @csrf

                        <input type="date"
                               name="exit_date"
                               required
                               value="{{ now()->format('Y-m-d') }}"
                               class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">

                        <button type="submit"
                                onclick="return confirm('Mark this user as exited? Login access will be disabled once account-status enforcement is enabled.')"
                                class="rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50">
                            Mark Exited
                        </button>
                    </form>
                @endif

            </div>

        </div>

    @endif

</div>

@endsection
