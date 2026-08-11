@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';

    $roleName = auth()->user()->role?->name;

    $hasPermission = function (string $permission) use ($roleName): bool {
        if ($roleName === 'Admin') {
            return true;
        }

        return auth()->user()
            ->role
            ?->permissions()
            ->where('name', $permission)
            ->where('is_active', true)
            ->exists() ?? false;
    };

    $canCreate = $hasPermission('material_received.create');
    $canEdit = $hasPermission('material_received.edit');
    $canApprove = $hasPermission('material_received.approve')
        || in_array($roleName, ['PMO', 'DGM'], true);
    $canVerifyBill = $hasPermission('material_received.accountant_verify')
        || $roleName === 'Accountant';
@endphp

<div class="mx-auto max-w-full">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Material Received
            </h1>

            <p class="mt-1 text-gray-500">
                Track inward material entries, receipt approval and supplier bill verification.
            </p>
        </div>

        @if($canCreate)
            <a href="{{ route('material-received.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 font-semibold text-white shadow-sm hover:bg-blue-700">
                + Add Material Received
            </a>
        @endif

    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if($isAccountant && $effectiveAccountantStatusFilter === 'Pending')
        <div class="mb-5 rounded-lg border border-purple-200 bg-purple-50 px-4 py-3 text-purple-800">
            Showing PMO-approved receipts whose supplier bills still require Accounts verification.
        </div>
    @endif

    {{-- Summary Cards --}}
    @if($isAccountant)
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

            <a href="{{ route('material-received.index', [
                    'status' => 'Approved',
                    'accountant_status' => 'Pending',
                ]) }}"
               class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-purple-300 hover:shadow">

                <p class="text-sm text-gray-500">
                    Bills Pending Verification
                </p>

                <p class="mt-2 text-2xl font-bold text-purple-700">
                    {{ $pendingAccountantCount }}
                </p>
            </a>

            <a href="{{ route('material-received.index', [
                    'accountant_status' => 'Bill Verified',
                ]) }}"
               class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-green-300 hover:shadow">

                <p class="text-sm text-gray-500">
                    Bills Verified Today
                </p>

                <p class="mt-2 text-2xl font-bold text-green-700">
                    {{ $billVerifiedTodayCount }}
                </p>
            </a>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">
                    PMO Approved Receipts
                </p>

                <p class="mt-2 text-2xl font-bold text-green-700">
                    {{ $approvedCount }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">
                    Qty Received Today
                </p>

                <p class="mt-2 text-2xl font-bold text-blue-700">
                    {{ formatQuantity($totalReceivedToday) }}
                </p>
            </div>

        </div>
    @else
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">
                    Qty Received Today
                </p>

                <p class="mt-2 text-2xl font-bold text-blue-700">
                    {{ formatQuantity($totalReceivedToday) }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">
                    Draft Entries
                </p>

                <p class="mt-2 text-2xl font-bold text-yellow-700">
                    {{ $draftCount }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">
                    Submitted Entries
                </p>

                <p class="mt-2 text-2xl font-bold text-orange-700">
                    {{ $submittedCount }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">
                    Approved Entries
                </p>

                <p class="mt-2 text-2xl font-bold text-green-700">
                    {{ $approvedCount }}
                </p>
            </div>

        </div>
    @endif

    {{-- Filters --}}
    <form method="GET"
          action="{{ route('material-received.index') }}"
          class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Date
                </label>

                <input type="date"
                       name="received_date"
                       value="{{ request('received_date') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Project
                </label>

                <select name="project_id"
                        class="{{ $inputClass }}">

                    <option value="">All Projects</option>

                    @foreach($projects as $project)
                        <option value="{{ $project->id }}"
                            {{ (string) request('project_id') === (string) $project->id ? 'selected' : '' }}>
                            {{ $project->project_name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Receipt Status
                </label>

                <select name="status"
                        class="{{ $inputClass }}">

                    <option value="">All Receipt Statuses</option>

                    @foreach(['Draft', 'Submitted', 'Approved', 'Rejected'] as $status)
                        <option value="{{ $status }}"
                            {{ $effectiveStatusFilter === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Accounts Status
                </label>

                <select name="accountant_status"
                        class="{{ $inputClass }}">

                    <option value="all"
                        {{ $effectiveAccountantStatusFilter === 'all' ? 'selected' : '' }}>
                        All Accounts Statuses
                    </option>

                    <option value="Pending"
                        {{ $effectiveAccountantStatusFilter === 'Pending' ? 'selected' : '' }}>
                        Pending Verification
                    </option>

                    <option value="Bill Verified"
                        {{ $effectiveAccountantStatusFilter === 'Bill Verified' ? 'selected' : '' }}>
                        Bill Verified
                    </option>

                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Search
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="{{ $inputClass }}"
                       placeholder="Bill, challan, vendor, material">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ $isAccountant
                        ? route('material-received.index', ['accountant_status' => 'all'])
                        : route('material-received.index') }}"
                   class="rounded-lg bg-gray-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">
                    Clear
                </a>
            </div>

        </div>

    </form>

    {{-- Receipt Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-[1180px] w-full text-sm">

                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-3 py-3 text-center">#</th>
                        <th class="px-3 py-3 text-left">Date</th>
                        <th class="px-3 py-3 text-left">Project</th>
                        <th class="px-3 py-3 text-left">Material</th>
                        <th class="px-3 py-3 text-left">Specification</th>
                        <th class="px-3 py-3 text-left">Brand</th>
                        <th class="px-3 py-3 text-right">Qty</th>
                        <th class="px-3 py-3 text-left">Unit</th>
                        <th class="px-3 py-3 text-left">Vendor</th>
                        <th class="px-3 py-3 text-left">Receipt Status</th>
                        <th class="px-3 py-3 text-left">Accounts Status</th>
                        <th class="px-3 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse($materialReceiveds as $index => $materialReceived)

                        @php
                            $hasNewItems = $materialReceived->items->isNotEmpty();

                            $receiptStatusClasses = match($materialReceived->status) {
                                'Approved' => 'bg-green-100 text-green-800',
                                'Submitted' => 'bg-blue-100 text-blue-800',
                                'Rejected' => 'bg-red-100 text-red-800',
                                default => 'bg-yellow-100 text-yellow-800',
                            };

                            $accountsStatus = $materialReceived->accountant_verification_status === 'Bill Verified'
                                ? 'Bill Verified'
                                : 'Pending';

                            $accountsStatusClasses = $accountsStatus === 'Bill Verified'
                                ? 'bg-green-100 text-green-800'
                                : 'bg-purple-100 text-purple-800';
                        @endphp

                        <tr class="align-top hover:bg-gray-50">

                            <td class="px-3 py-3 text-center">
                                {{ $materialReceiveds->firstItem() + $index }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3">
                                {{ $materialReceived->received_date?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td class="px-3 py-3">
                                <div class="font-semibold text-gray-800">
                                    {{ $materialReceived->project?->project_name ?? '-' }}
                                </div>

                                @if($materialReceived->storage_location)
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $materialReceived->storage_location }}
                                    </div>
                                @endif
                            </td>

                            {{-- Material --}}
                            <td class="px-3 py-3">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($materialReceived->items as $item)
                                            <div class="min-h-[42px] py-1">
                                                <div class="font-semibold text-gray-800">
                                                    {{ $item->materialType?->material_type_name ?? '-' }}
                                                </div>

                                                @if($item->activity)
                                                    <div class="text-xs text-gray-500">
                                                        {{ $item->activity->activity_name }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{ $materialReceived->material_name
                                        ?? $materialReceived->material?->material_name
                                        ?? '-' }}
                                @endif
                            </td>

                            {{-- Specification --}}
                            <td class="px-3 py-3">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($materialReceived->items as $item)
                                            <div class="min-h-[42px] py-1">
                                                {{ $item->specification?->specification_name ?? '-' }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{ $materialReceived->specification ?? '-' }}
                                @endif
                            </td>

                            {{-- Brand --}}
                            <td class="px-3 py-3">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($materialReceived->items as $item)
                                            <div class="min-h-[42px] py-1">
                                                {{ $item->brand?->brand_name ?? '-' }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{ $materialReceived->brand ?? '-' }}
                                @endif
                            </td>

                            {{-- Quantity --}}
                            <td class="px-3 py-3 text-right">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($materialReceived->items as $item)
                                            <div class="min-h-[42px] py-1 font-semibold">
                                                {{ formatQuantity($item->quantity_received) }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{ formatQuantity($materialReceived->quantity_received) }}
                                @endif
                            </td>

                            {{-- Unit --}}
                            <td class="px-3 py-3">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($materialReceived->items as $item)
                                            <div class="min-h-[42px] py-1">
                                                {{ $item->unit?->unit_name ?? '-' }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{ $materialReceived->unit ?? '-' }}
                                @endif
                            </td>

                            <td class="px-3 py-3">
                                {{ $materialReceived->vendor?->vendor_name
                                    ?? $materialReceived->vendor_name
                                    ?? '-' }}
                            </td>

                            <td class="px-3 py-3">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $receiptStatusClasses }}">
                                    {{ $materialReceived->status }}
                                </span>
                            </td>

                            <td class="px-3 py-3">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $accountsStatusClasses }}">
                                    {{ $accountsStatus }}
                                </span>

                                @if($accountsStatus === 'Bill Verified')
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $materialReceived->accountantVerifier?->name ?? '-' }}

                                        @if($materialReceived->accountant_verified_at)
                                            <br>
                                            {{ $materialReceived->accountant_verified_at->format('d/m/Y h:i A') }}
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <td class="px-3 py-3">
                                <div class="flex flex-col gap-2">

                                    <a href="{{ route('material-received.show', $materialReceived) }}"
                                       class="rounded bg-slate-700 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-slate-800">
                                        View
                                    </a>

                                    @if(
                                        $materialReceived->status === 'Draft'
                                        && $canEdit
                                    )
                                        <a href="{{ route('material-received.edit', $materialReceived) }}"
                                           class="rounded bg-yellow-500 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-yellow-600">
                                            Edit
                                        </a>
                                    @endif

                                    @if(
                                        $materialReceived->status === 'Submitted'
                                        && $canApprove
                                    )
                                        <form method="POST"
                                              action="{{ route('material-received.approve', $materialReceived) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    onclick="return confirm('Approve this material receipt?')"
                                                    class="w-full rounded bg-green-600 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-green-700">
                                                Approve
                                            </button>
                                        </form>
                                    @endif

                                    @if(
                                        $materialReceived->status === 'Approved'
                                        && $accountsStatus !== 'Bill Verified'
                                        && $canVerifyBill
                                    )
                                        <form method="POST"
                                              action="{{ route('material-received.accountant-verify', $materialReceived) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    onclick="return confirm('Confirm that Accounts has verified this supplier bill?')"
                                                    class="w-full rounded bg-purple-600 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-purple-700">
                                                Verify Bill
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="12"
                                class="px-6 py-12 text-center text-gray-500">
                                No material receipts found for the selected filters.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        @if($materialReceiveds->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $materialReceiveds->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
