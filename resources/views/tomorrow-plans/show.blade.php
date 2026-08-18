@extends('layouts.app')

@section('content')

@php
    $priorityClass = match($tomorrowPlan->priority) {
        'Critical' => 'bg-red-100 text-red-800',
        'Urgent' => 'bg-orange-100 text-orange-800',
        default => 'bg-blue-100 text-blue-800',
    };

    $statusClass = match($tomorrowPlan->status) {
        'Approved' => 'bg-green-100 text-green-800',
        'Submitted' => 'bg-orange-100 text-orange-800',
        default => 'bg-yellow-100 text-yellow-800',
    };

    $location = collect([
        $tomorrowPlan->block?->name,
        $tomorrowPlan->floor?->name,
        $tomorrowPlan->projectUnit?->name,
        $tomorrowPlan->room?->name,
        $tomorrowPlan->subspace?->name,
    ])->filter()->implode(' › ');
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                    Tomorrow Plan Details
                </h1>

                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                    {{ $tomorrowPlan->status }}
                </span>

                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClass }}">
                    {{ $tomorrowPlan->priority }}
                </span>
            </div>

            <p class="mt-1 text-gray-500">
                View complete tomorrow planning details.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
            @if($tomorrowPlan->status === 'Draft')
                <a href="{{ route('tomorrow-plans.edit', $tomorrowPlan) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-yellow-500 px-4 py-3 text-sm font-semibold text-white hover:bg-yellow-600 sm:py-2.5">
                    Edit
                </a>
            @endif

            <a href="{{ route('tomorrow-plans.index') }}"
               class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-700 sm:py-2.5">
                Back
            </a>
        </div>
    </div>

    {{-- Primary Summary --}}
    <div class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6 xl:col-span-2">
            <div class="mb-5 rounded-xl bg-[#0F2A52] px-4 py-3 text-white">
                <h2 class="text-lg font-bold sm:text-xl">Plan Information</h2>
                <p class="mt-1 text-xs text-blue-100">Project, location and planned execution details.</p>
            </div>

            <dl class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Project</dt>
                    <dd class="mt-1 font-semibold text-gray-800">{{ $tomorrowPlan->project?->project_name ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Planned Date</dt>
                    <dd class="mt-1 text-gray-800">{{ $tomorrowPlan->planned_date ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Priority</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClass }}">
                            {{ $tomorrowPlan->priority }}
                        </span>
                    </dd>
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Location</dt>
                    <dd class="mt-1 text-gray-800">{{ $location ?: '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Activity</dt>
                    <dd class="mt-1 font-semibold text-gray-800">{{ $tomorrowPlan->activity?->activity_name ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Planned Quantity</dt>
                    <dd class="mt-1 font-bold text-[#0F2A52]">
                        {{ rtrim(rtrim(number_format((float) $tomorrowPlan->planned_quantity, 2, '.', ''), '0'), '.') }}
                        {{ $tomorrowPlan->unit ?? '' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Contractor</dt>
                    <dd class="mt-1 text-gray-800">{{ $tomorrowPlan->contractor?->contractor_name ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Responsible Person</dt>
                    <dd class="mt-1 text-gray-800">{{ $tomorrowPlan->responsible_person ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Created By</dt>
                    <dd class="mt-1 text-gray-800">{{ $tomorrowPlan->creator?->name ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                            {{ $tomorrowPlan->status }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Labour Summary --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="mb-5 rounded-xl bg-[#0F2A52] px-4 py-3 text-white">
                <h2 class="text-lg font-bold sm:text-xl">Labour Requirement</h2>
                <p class="mt-1 text-xs text-blue-100">Tomorrow's planned manpower.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-blue-50 px-3 py-3 text-center">
                    <div class="text-2xl font-bold text-blue-800">{{ $tomorrowPlan->planned_labour ?? 0 }}</div>
                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wide text-blue-700">Total</div>
                </div>

                <div class="rounded-xl bg-green-50 px-3 py-3 text-center">
                    <div class="text-2xl font-bold text-green-800">{{ $tomorrowPlan->required_skilled_labour ?? 0 }}</div>
                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wide text-green-700">Skilled</div>
                </div>

                <div class="rounded-xl bg-amber-50 px-3 py-3 text-center">
                    <div class="text-2xl font-bold text-amber-800">{{ $tomorrowPlan->required_semiskilled_labour ?? 0 }}</div>
                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wide text-amber-700">Semi-Skilled</div>
                </div>

                <div class="rounded-xl bg-slate-50 px-3 py-3 text-center">
                    <div class="text-2xl font-bold text-slate-800">{{ $tomorrowPlan->required_helpers ?? 0 }}</div>
                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wide text-slate-600">Helpers</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Dependencies --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="mb-5 rounded-xl bg-[#0F2A52] px-4 py-3 text-white">
            <h2 class="text-lg font-bold sm:text-xl">Resources & Dependencies</h2>
            <p class="mt-1 text-xs text-blue-100">Materials, machinery, drawings and approvals required.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Materials Required</div>
                <div class="mt-2 whitespace-pre-line text-sm text-gray-800">{{ $tomorrowPlan->materials_required ?? '-' }}</div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Machinery Required</div>
                <div class="mt-2 whitespace-pre-line text-sm text-gray-800">{{ $tomorrowPlan->machinery_required ?? '-' }}</div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Drawing Required?</div>
                <div class="mt-2 text-sm font-semibold text-gray-800">{{ $tomorrowPlan->drawing_required ? 'Yes' : 'No' }}</div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Client Approval Required?</div>
                <div class="mt-2 text-sm font-semibold text-gray-800">{{ $tomorrowPlan->client_approval_required ? 'Yes' : 'No' }}</div>
            </div>
        </div>
    </div>

    {{-- Risks --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="mb-5 rounded-xl bg-[#0F2A52] px-4 py-3 text-white">
            <h2 class="text-lg font-bold sm:text-xl">Risk / Remarks</h2>
            <p class="mt-1 text-xs text-blue-100">Constraints and planning notes.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-orange-200 bg-orange-50 p-4">
                <div class="text-xs font-bold uppercase tracking-wide text-orange-700">Risks / Constraints</div>
                <div class="mt-2 whitespace-pre-line text-sm text-gray-800">{{ $tomorrowPlan->risks_constraints ?? '-' }}</div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Remarks</div>
                <div class="mt-2 whitespace-pre-line text-sm text-gray-800">{{ $tomorrowPlan->remarks ?? '-' }}</div>
            </div>
        </div>
    </div>

</div>

@endsection
