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

    $canCreate = $hasPermission('material_required.create');
    $canEdit = $hasPermission('material_required.edit');

    $canApprove = $hasPermission('material_required.approve')
        || in_array($roleName, ['PMO', 'DGM'], true);
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Material Requirements</h1>
            <p class="mt-1 text-sm text-gray-500">
                Project-wise requirements, approval and fulfilment tracking.
            </p>
        </div>

        @if($canCreate)
            <a href="{{ route('material-requirements.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                + Add Requirement
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Draft</div>
            <div class="mt-1 text-xl font-bold text-yellow-700">{{ $draftCount }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Submitted</div>
            <div class="mt-1 text-xl font-bold text-orange-700">{{ $submittedCount }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Approved</div>
            <div class="mt-1 text-xl font-bold text-green-700">{{ $approvedCount }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Urgent Open</div>
            <div class="mt-1 text-xl font-bold text-red-700">{{ $urgentCount }}</div>
        </div>
    </div>

    <form method="GET"
          action="{{ route('material-requirements.index') }}"
          class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">

        <div class="grid grid-cols-1 gap-3 md:grid-cols-3 xl:grid-cols-6">
            <input type="date"
                   name="required_date"
                   value="{{ request('required_date') }}"
                   class="{{ $inputClass }}">

            <select name="project_id" class="{{ $inputClass }}">
                <option value="">All Projects</option>

                @foreach($projects as $project)
                    <option value="{{ $project->id }}"
                        {{ (string) request('project_id') === (string) $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>

            <select name="priority" class="{{ $inputClass }}">
                <option value="">All Priorities</option>

                @foreach(['Low', 'Normal', 'High', 'Urgent'] as $priority)
                    <option value="{{ $priority }}"
                        {{ request('priority') === $priority ? 'selected' : '' }}>
                        {{ $priority }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="{{ $inputClass }}">
                <option value="">All Statuses</option>

                @foreach(['Draft', 'Submitted', 'Approved', 'Rejected'] as $status)
                    <option value="{{ $status }}"
                        {{ request('status') === $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="{{ $inputClass }}"
                   placeholder="Project / Product / Spec / Brand">

            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('material-requirements.index') }}"
                   class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Clear
                </a>
            </div>
        </div>
    </form>

    {{-- Mobile --}}
    <div class="space-y-3 lg:hidden">
        @forelse($requirements as $requirement)
            @php
                $statusClasses = match($requirement->status) {
                    'Approved' => 'bg-green-100 text-green-800',
                    'Submitted' => 'bg-blue-100 text-blue-800',
                    'Rejected' => 'bg-red-100 text-red-800',
                    default => 'bg-yellow-100 text-yellow-800',
                };

                $priorityClasses = match($requirement->priority) {
                    'Urgent' => 'bg-red-100 text-red-800',
                    'High' => 'bg-orange-100 text-orange-800',
                    'Normal' => 'bg-blue-100 text-blue-800',
                    default => 'bg-gray-100 text-gray-800',
                };
            @endphp

            <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs text-gray-500">
                            MR-{{ str_pad($requirement->id, 4, '0', STR_PAD_LEFT) }}
                            · {{ $requirement->required_date?->format('d/m/Y') ?? '-' }}
                        </div>

                        <div class="mt-1 font-bold text-gray-800">
                            {{ $requirement->project?->project_name ?? '-' }}
                        </div>
                    </div>

                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                        {{ $requirement->status }}
                    </span>
                </div>

                <div class="mt-3 space-y-1.5">
                    @foreach($requirement->items->take(4) as $item)
                        <div class="text-sm text-gray-700">
                            <span class="font-semibold">
                                {{ $item->materialType?->material_type_name ?? '-' }}
                            </span>

                            @if($item->specification_text ?: $item->specification?->specification_name)
                                · {{ $item->specification_text ?: $item->specification?->specification_name }}
                            @endif

                            @if($item->brand)
                                · {{ $item->brand->brand_name }}
                            @endif

                            <span class="font-semibold text-blue-700">
                                × {{ formatQuantity($item->required_quantity) }}
                            </span>
                        </div>
                    @endforeach

                    @if($requirement->items->count() > 4)
                        <div class="text-xs text-gray-500">
                            +{{ $requirement->items->count() - 4 }} more
                        </div>
                    @endif
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $priorityClasses }}">
                        {{ $requirement->priority }}
                    </span>

                    <div class="text-xs text-gray-500">
                        Req {{ formatQuantity($requirement->total_required_quantity) }}
                        · Ful {{ formatQuantity($requirement->total_fulfilled_quantity) }}
                        · Pen {{ formatQuantity($requirement->total_pending_quantity) }}
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('material-requirements.show', $requirement) }}"
                       class="rounded-lg bg-[#10212F] px-3 py-2 text-center text-sm font-semibold text-white">
                        View
                    </a>

                    @if($requirement->status === 'Draft' && $canEdit)
                        <a href="{{ route('material-requirements.edit', $requirement) }}"
                           class="rounded-lg bg-amber-500 px-3 py-2 text-center text-sm font-semibold text-white">
                            Edit
                        </a>
                    @else
                        <a href="{{ route('material-requirements.pdf', $requirement) }}"
                           class="rounded-lg bg-gray-700 px-3 py-2 text-center text-sm font-semibold text-white">
                            PDF
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-gray-200 bg-white px-5 py-10 text-center text-gray-500">
                No material requirements found.
            </div>
        @endforelse

        @if($requirements->hasPages())
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 shadow-sm">
                {{ $requirements->links() }}
            </div>
        @endif
    </div>

    {{-- Desktop compact register --}}
    <div class="hidden lg:block">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="max-h-[430px] overflow-auto">
                <table class="min-w-[1080px] w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-gray-100 text-[11px] uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="w-12 px-2 py-2 text-center">#</th>
                            <th class="w-24 px-2 py-2 text-left">Date</th>
                            <th class="min-w-[180px] px-2 py-2 text-left">Project</th>
                            <th class="w-20 px-2 py-2 text-left">Priority</th>
                            <th class="min-w-[360px] px-2 py-2 text-left">Products</th>
                            <th class="w-32 px-2 py-2 text-center">Qty</th>
                            <th class="w-24 px-2 py-2 text-left">Status</th>
                            <th class="w-28 px-2 py-2 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse($requirements as $index => $requirement)
                            @php
                                $priorityClasses = match($requirement->priority) {
                                    'Urgent' => 'bg-red-100 text-red-800',
                                    'High' => 'bg-orange-100 text-orange-800',
                                    'Normal' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };

                                $statusClasses = match($requirement->status) {
                                    'Approved' => 'bg-green-100 text-green-800',
                                    'Submitted' => 'bg-blue-100 text-blue-800',
                                    'Rejected' => 'bg-red-100 text-red-800',
                                    default => 'bg-yellow-100 text-yellow-800',
                                };
                            @endphp

                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-2 py-2 text-center text-xs">
                                    {{ $requirements->firstItem() + $index }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2 text-xs">
                                    {{ $requirement->required_date?->format('d/m/Y') ?? '-' }}
                                </td>

                                <td class="px-2 py-2">
                                    <div class="font-semibold text-gray-800">
                                        {{ $requirement->project?->project_name ?? '-' }}
                                    </div>

                                    <div class="mt-0.5 text-[11px] text-gray-400">
                                        MR-{{ str_pad($requirement->id, 4, '0', STR_PAD_LEFT) }}
                                    </div>
                                </td>

                                <td class="px-2 py-2">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $priorityClasses }}">
                                        {{ $requirement->priority }}
                                    </span>
                                </td>

                                <td class="px-2 py-2">
                                    <div class="space-y-1">
                                        @foreach($requirement->items as $item)
                                            <div class="leading-5 text-gray-700">
                                                <span class="font-semibold">
                                                    {{ $item->materialType?->material_type_name ?? '-' }}
                                                </span>

                                                @if($item->specification_text ?: $item->specification?->specification_name)
                                                    <span class="text-gray-500">
                                                        · {{ $item->specification_text ?: $item->specification?->specification_name }}
                                                    </span>
                                                @endif

                                                @if($item->brand)
                                                    <span class="text-gray-500">
                                                        · {{ $item->brand->brand_name }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-2 py-2 text-center">
                                    <div class="inline-grid grid-cols-[auto_auto] gap-x-2 gap-y-0.5 text-xs">
                                        <span class="text-gray-500">Req</span>
                                        <span class="font-semibold text-blue-700">
                                            {{ formatQuantity($requirement->total_required_quantity) }}
                                        </span>

                                        <span class="text-gray-500">Ful</span>
                                        <span class="font-semibold text-green-700">
                                            {{ formatQuantity($requirement->total_fulfilled_quantity) }}
                                        </span>

                                        <span class="text-gray-500">Pen</span>
                                        <span class="font-semibold text-orange-700">
                                            {{ formatQuantity($requirement->total_pending_quantity) }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-2 py-2">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $statusClasses }}">
                                        {{ $requirement->status }}
                                    </span>
                                </td>

                                <td class="px-2 py-2">
                                    <div class="grid grid-cols-2 gap-1">
                                        <a href="{{ route('material-requirements.show', $requirement) }}"
                                           class="rounded bg-slate-700 px-2 py-1.5 text-center text-[11px] font-semibold text-white">
                                            View
                                        </a>

                                        <a href="{{ route('material-requirements.pdf', $requirement) }}"
                                           class="rounded bg-gray-700 px-2 py-1.5 text-center text-[11px] font-semibold text-white">
                                            PDF
                                        </a>

                                        @if($requirement->status === 'Draft' && $canEdit)
                                            <a href="{{ route('material-requirements.edit', $requirement) }}"
                                               class="rounded bg-amber-500 px-2 py-1.5 text-center text-[11px] font-semibold text-white">
                                                Edit
                                            </a>
                                        @endif

                                        @if($requirement->status === 'Draft')
                                            <form method="POST"
                                                  action="{{ route('material-requirements.submit', $requirement) }}">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        onclick="return confirm('Submit this material requirement for approval?')"
                                                        class="w-full rounded bg-blue-600 px-2 py-1.5 text-[11px] font-semibold text-white">
                                                    Submit
                                                </button>
                                            </form>
                                        @elseif($requirement->status === 'Submitted' && $canApprove)
                                            <form method="POST"
                                                  action="{{ route('material-requirements.approve', $requirement) }}">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        onclick="return confirm('Approve this material requirement?')"
                                                        class="w-full rounded bg-green-600 px-2 py-1.5 text-[11px] font-semibold text-white">
                                                    Approve
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                    No material requirements found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($requirements->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">
                    {{ $requirements->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
