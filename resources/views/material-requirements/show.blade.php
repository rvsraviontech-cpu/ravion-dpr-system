@extends('layouts.app')

@section('content')

@php
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

    $canEdit = $hasPermission('material_required.edit');

    $canApprove = $hasPermission('material_required.approve')
        || in_array($roleName, ['PMO', 'DGM'], true);

    $statusClasses = match($materialRequirement->status) {
        'Approved' => 'bg-green-100 text-green-800',
        'Submitted' => 'bg-blue-100 text-blue-800',
        'Rejected' => 'bg-red-100 text-red-800',
        default => 'bg-yellow-100 text-yellow-800',
    };

    $priorityClasses = match($materialRequirement->priority) {
        'Urgent' => 'bg-red-100 text-red-800',
        'High' => 'bg-orange-100 text-orange-800',
        'Normal' => 'bg-blue-100 text-blue-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-bold text-gray-800">
                    Material Requirement MR-{{ str_pad($materialRequirement->id, 4, '0', STR_PAD_LEFT) }}
                </h1>

                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                    {{ $materialRequirement->status }}
                </span>

                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClasses }}">
                    {{ $materialRequirement->priority }}
                </span>
            </div>

            <p class="mt-1 text-sm text-gray-500">
                Material requirement sheet for review, approval and sharing.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($materialRequirement->status === 'Draft' && $canEdit)
                <a href="{{ route('material-requirements.edit', $materialRequirement) }}"
                   class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-600">
                    Edit
                </a>
            @endif

            @if($materialRequirement->status === 'Draft')
                <form method="POST"
                      action="{{ route('material-requirements.submit', $materialRequirement) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            onclick="return confirm('Submit this material requirement for approval?')"
                            class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        Submit
                    </button>
                </form>
            @endif

            @if($materialRequirement->status === 'Submitted' && $canApprove)
                <form method="POST"
                      action="{{ route('material-requirements.approve', $materialRequirement) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            onclick="return confirm('Approve this material requirement?')"
                            class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                        Approve
                    </button>
                </form>
            @endif

            <a href="{{ route('material-requirements.pdf', $materialRequirement) }}"
               class="rounded-lg bg-[#10212F] px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                Download PDF
            </a>

            <a href="{{ route('material-requirements.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Back
            </a>
        </div>
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

    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-2 gap-x-6 gap-y-4 md:grid-cols-4 xl:grid-cols-6">
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Project</div>
                <div class="mt-1 font-semibold text-gray-800">
                    {{ $materialRequirement->project?->project_name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Project Block</div>
                <div class="mt-1 text-gray-800">
                    {{ $materialRequirement->block?->name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Required Date</div>
                <div class="mt-1 text-gray-800">
                    {{ $materialRequirement->required_date?->format('d/m/Y') ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Requested By</div>
                <div class="mt-1 text-gray-800">
                    {{ $materialRequirement->creator?->name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Approved By</div>
                <div class="mt-1 text-gray-800">
                    {{ $materialRequirement->approver?->name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">DPR Ref.</div>
                <div class="mt-1 text-gray-800">
                    {{ $materialRequirement->dpr_id ? '#'.$materialRequirement->dpr_id : '-' }}
                </div>
            </div>
        </div>

        @if($materialRequirement->remarks)
            <div class="mt-4 border-t border-gray-100 pt-4">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Remarks</div>
                <div class="mt-1 whitespace-pre-line text-gray-800">{{ $materialRequirement->remarks }}</div>
            </div>
        @endif
    </div>

    <div class="mb-5 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Requirement Items</h2>
                <p class="mt-1 text-xs text-gray-500">
                    {{ $materialRequirement->items->count() }} item(s) in this requirement.
                </p>
            </div>

            <div class="flex flex-wrap gap-2 text-xs">
                <span class="rounded-full bg-blue-50 px-3 py-1 font-semibold text-blue-700">
                    Required {{ formatQuantity($materialRequirement->total_required_quantity) }}
                </span>

                <span class="rounded-full bg-green-50 px-3 py-1 font-semibold text-green-700">
                    Fulfilled {{ formatQuantity($materialRequirement->total_fulfilled_quantity) }}
                </span>

                <span class="rounded-full bg-orange-50 px-3 py-1 font-semibold text-orange-700">
                    Pending {{ formatQuantity($materialRequirement->total_pending_quantity) }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="w-12 px-3 py-3 text-center">#</th>
                        <th class="min-w-[300px] px-3 py-3 text-left">Product</th>
                        <th class="min-w-[220px] px-3 py-3 text-left">Specification / Size</th>
                        <th class="min-w-[170px] px-3 py-3 text-left">Brand</th>
                        <th class="w-28 px-3 py-3 text-right">Qty</th>
                        <th class="min-w-[220px] px-3 py-3 text-left">Remarks</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @foreach($materialRequirement->items as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-3 text-center">
                                {{ $index + 1 }}
                            </td>

                            <td class="px-3 py-3 font-semibold text-gray-800">
                                {{ $item->materialType?->material_type_name ?? '-' }}
                            </td>

                            <td class="px-3 py-3">
                                {{ $item->specification_text
                                    ?: $item->specification?->specification_name
                                    ?: '-' }}
                            </td>

                            <td class="px-3 py-3">
                                {{ $item->brand?->brand_name ?? '-' }}
                            </td>

                            <td class="px-3 py-3 text-right font-semibold text-blue-700">
                                {{ formatQuantity($item->required_quantity) }}
                            </td>

                            <td class="px-3 py-3">
                                {{ $item->remarks ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-4">
            <div>
                <div class="text-xs text-gray-500">Created At</div>
                <div class="mt-1 font-medium text-gray-800">
                    {{ $materialRequirement->created_at?->format('d/m/Y h:i A') ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">Updated At</div>
                <div class="mt-1 font-medium text-gray-800">
                    {{ $materialRequirement->updated_at?->format('d/m/Y h:i A') ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">Approved At</div>
                <div class="mt-1 font-medium text-gray-800">
                    {{ $materialRequirement->approved_at?->format('d/m/Y h:i A') ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">Requirement ID</div>
                <div class="mt-1 font-medium text-gray-800">
                    MR-{{ str_pad($materialRequirement->id, 4, '0', STR_PAD_LEFT) }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
