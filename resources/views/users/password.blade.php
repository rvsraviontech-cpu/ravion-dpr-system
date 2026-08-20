@extends('layouts.app')

@section('content')

<div class="space-y-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Password Management</h1>
            <p class="mt-1 text-sm text-gray-500">Reset the password for {{ $user->name }}.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('users.show', $user->id) }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Back to User
            </a>
            <a href="{{ route('users.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Back to Users
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="font-semibold">Please correct the following:</div>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
            <h2 class="text-sm font-semibold text-gray-900">User</h2>
        </div>

        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Employee Code</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $user->employee_code ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Name</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $user->name }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Role</div>
                <div class="mt-1 text-sm text-gray-900">{{ $user->role?->name ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $user->account_status }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Department</div>
                <div class="mt-1 text-sm text-gray-900">{{ $user->departmentMaster?->name ?? $user->department ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Designation</div>
                <div class="mt-1 text-sm text-gray-900">{{ $user->employeeDesignation?->name ?? $user->designation ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Password Last Changed</div>
                <div class="mt-1 text-sm text-gray-900">{{ $user->password_changed_at?->format('d M Y, h:i A') ?? 'Not recorded' }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Change Required</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $user->must_change_password ? 'Yes' : 'No' }}</div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('users.password.update', $user->id) }}" class="space-y-5">
        @csrf

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">Reset Password</h2>
                <p class="mt-0.5 text-xs text-gray-500">Set a new temporary or permanent password for this user.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                           name="password"
                           required
                           autocomplete="new-password"
                           placeholder="Minimum 8 characters"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                           name="password_confirmation"
                           required
                           autocomplete="new-password"
                           placeholder="Re-enter password"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                </div>

                <div class="md:col-span-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <input type="checkbox"
                               name="must_change_password"
                               value="1"
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               {{ old('must_change_password', true) ? 'checked' : '' }}>
                        <div>
                            <div class="text-sm font-semibold text-gray-800">Require password change on next login</div>
                            <div class="mt-0.5 text-xs leading-5 text-gray-500">
                                Recommended for temporary passwords. The user will be forced to create a new password immediately after login.
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4">
            <div class="text-sm font-semibold text-amber-900">Security Note</div>
            <div class="mt-1 text-sm leading-6 text-amber-800">
                Existing passwords cannot be viewed or recovered. Ravion ERP stores password hashes only. Resetting a password replaces the existing password and clears the user's remember-me token.
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end">
            <a href="{{ route('users.show', $user->id) }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Cancel
            </a>

            <button type="submit"
                    onclick="return confirm('Reset the password for this user?');"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Reset Password
            </button>
        </div>
    </form>
</div>

@endsection
