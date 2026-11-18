@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <header>
        <p class="text-xs font-semibold uppercase tracking-wider text-teal-700">Client Control / Administration</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Client Accounts & Project Access</h1>
        <p class="mt-2 text-sm text-slate-500">Create client identities, manage account status and control project-specific access.</p>
    </header>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">Create Client Identity</h2>
        <p class="mt-1 text-sm text-slate-500">Accounts start Pending. Share temporary credentials through an approved secure channel, never by email in plain text.</p>
        <form method="POST" action="{{ route('client-control.admin.store') }}" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            @csrf
            <div>
                <label for="client-name" class="mb-1 block text-xs font-semibold text-slate-600">Full name *</label>
                <input id="client-name" name="name" required maxlength="255" value="{{ old('name') }}" placeholder="Full name" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label for="client-email" class="mb-1 block text-xs font-semibold text-slate-600">Email *</label>
                <input id="client-email" name="email" type="email" required value="{{ old('email') }}" placeholder="Email address" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label for="client-mobile" class="mb-1 block text-xs font-semibold text-slate-600">Mobile</label>
                <input id="client-mobile" name="mobile" maxlength="30" value="{{ old('mobile') }}" placeholder="Mobile number" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label for="client-password" class="mb-1 block text-xs font-semibold text-slate-600">Temporary password *</label>
                <input id="client-password" name="password" type="password" required autocomplete="new-password" placeholder="12+ strong characters" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Create Pending Client</button>
            </div>
        </form>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-slate-900">Client Accounts & Project Grants</h2>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">Manage access securely</span>
        </div>

        <div class="space-y-5">
            @forelse($clients as $client)
                <article class="overflow-hidden rounded-xl border border-slate-200">
                    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 bg-slate-50 p-4 sm:p-5">
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-900">{{ $client->name }}</h3>
                            <p class="break-all text-sm text-slate-600">{{ $client->email }}</p>
                            @if($client->mobile)<p class="text-sm text-slate-500">{{ $client->mobile }}</p>@endif
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $client->status === 'Active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $client->status }}</span>
                            @if($client->status !== 'Active')
                                <form method="POST" action="{{ route('client-control.admin.activate', $client) }}">@csrf
                                    <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Activate</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('client-control.admin.deactivate', $client) }}">@csrf
                                    <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Deactivate</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="border-b border-slate-100 p-4 sm:p-5">
                        <div class="grid gap-3 lg:grid-cols-2">
                            <details class="rounded-xl border border-slate-200 bg-white p-4">
                                <summary class="cursor-pointer text-sm font-semibold text-teal-800">Edit Client Details</summary>
                                <form method="POST" action="{{ route('client-control.admin.update-details', $client) }}" class="mt-4 space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <div><label class="mb-1 block text-xs font-semibold text-slate-600">Full name *</label>
                                        <input name="name" required maxlength="255" value="{{ $client->name }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-xs font-semibold text-slate-600">Email *</label>
                                        <input name="email" type="email" required maxlength="255" value="{{ $client->email }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-xs font-semibold text-slate-600">Mobile</label>
                                        <input name="mobile" maxlength="30" value="{{ $client->mobile }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                                    <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white">Save Client Details</button>
                                </form>
                            </details>
                            <details class="rounded-xl border border-slate-200 bg-white p-4">
                                <summary class="cursor-pointer text-sm font-semibold text-teal-800">Reset Password</summary>
                                <p class="mt-3 text-xs text-slate-500">Use a strong temporary password. The client will be required to change it at next login. Share it securely.</p>
                                <form method="POST" action="{{ route('client-control.admin.reset-password', $client) }}" class="mt-3 space-y-3" onsubmit="return confirm('Reset password for this client?');">
                                    @csrf
                                    <div><label class="mb-1 block text-xs font-semibold text-slate-600">New temporary password *</label>
                                        <input name="password" type="password" required minlength="12" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-xs font-semibold text-slate-600">Confirm temporary password *</label>
                                        <input name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Set Temporary Password</button>
                                </form>
                            </details>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5">
                        <h4 class="text-sm font-bold text-slate-900">Add Project Grant</h4>
                        <form method="POST" action="{{ route('client-control.admin.grant', $client) }}" class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                            @csrf
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600">Project *</label>
                                <select name="project_id" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm">
                                    <option value="">Select project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->project_code }} — {{ $project->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600">Client role *</label>
                                <select name="role" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm">
                                    <option value="">Select role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}">{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600">Expires *</label>
                                <input type="datetime-local" name="expires_at" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">Create Pending Grant</button>
                            </div>
                        </form>

                        <div class="mt-5 space-y-3">
                            @forelse($client->grants as $grant)
                                <div class="rounded-lg border border-slate-200 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $grant->project?->project_name ?? 'Project unavailable' }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ $grant->role }} · Expires: {{ $grant->expires_at?->format('d M Y H:i') ?? 'Not set' }}</p>
                                        </div>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $grant->status === 'Approved' ? 'bg-emerald-100 text-emerald-800' : ($grant->status === 'Pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">{{ $grant->status }}</span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-end gap-3">
                                        @if($grant->status === 'Pending')
                                            <form method="POST" action="{{ route('client-control.admin.approve', $grant) }}">@csrf
                                                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Approve Grant</button>
                                            </form>
                                        @endif
                                        @if(!$grant->revoked_at)
                                            <form method="POST" action="{{ route('client-control.admin.revoke', $grant) }}" class="flex flex-wrap items-center gap-2">
                                                @csrf
                                                <input name="reason" required maxlength="2000" placeholder="Reason for revocation" aria-label="Reason for revocation" class="min-w-0 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">Revoke</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-500">No project grants for this client yet.</p>
                            @endforelse
                        </div>
                    </div>
                </article>
            @empty
                <p class="rounded-xl border border-dashed border-slate-200 px-5 py-10 text-center text-sm text-slate-500">No client accounts yet.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $clients->links() }}</div>
    </section>
</div>
@endsection
