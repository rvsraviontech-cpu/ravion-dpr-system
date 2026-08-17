@extends('layouts.app')

@section('content')

@php
    $priorityClass = match($siteIssue->priority) {
        'Low' => 'bg-gray-100 text-gray-700',
        'Medium' => 'bg-blue-100 text-blue-800',
        'High' => 'bg-orange-100 text-orange-800',
        'Critical' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-700',
    };

    $statusClass = match($siteIssue->status) {
        'Open' => 'bg-red-100 text-red-800',
        'Assigned' => 'bg-blue-100 text-blue-800',
        'In Progress' => 'bg-orange-100 text-orange-800',
        'Resolved' => 'bg-green-100 text-green-800',
        'Verified' => 'bg-emerald-100 text-emerald-800',
        'Closed' => 'bg-slate-200 text-slate-800',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                Site Issue Details
            </h1>

            <p class="mt-1 text-gray-500">
                Complete issue information, location, escalation, resolution and photographic evidence.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
            <a href="{{ route('site-issues.index') }}"
               class="rounded-lg bg-gray-600 px-5 py-3 text-center font-semibold text-white hover:bg-gray-700 sm:py-2.5">
                Back
            </a>

            <a href="{{ route('site-issues.edit', $siteIssue) }}"
               class="rounded-lg bg-amber-500 px-5 py-3 text-center font-semibold text-white hover:bg-amber-600 sm:py-2.5">
                Edit Issue
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

    {{-- Summary --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-bold text-gray-800 sm:text-2xl">
                        {{ $siteIssue->title }}
                    </h2>

                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClass }}">
                        {{ $siteIssue->priority }}
                    </span>

                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                        {{ $siteIssue->status }}
                    </span>
                </div>

                <div class="mt-2 text-sm text-gray-500">
                    {{ $siteIssue->issue_type }}
                </div>
            </div>

            <div class="text-sm text-gray-500">
                Issue #{{ $siteIssue->id }}
            </div>

        </div>

        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Project
                </div>

                <div class="mt-1 font-semibold text-gray-800">
                    {{ $siteIssue->project?->project_name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Issue Date
                </div>

                <div class="mt-1 font-semibold text-gray-800">
                    {{ $siteIssue->issue_date?->format('d/m/Y') ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Reported By
                </div>

                <div class="mt-1 font-semibold text-gray-800">
                    {{ $siteIssue->creator?->name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    DPR Link
                </div>

                <div class="mt-1">
                    @if($siteIssue->is_dpr_linked)
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                            Linked to DPR #{{ $siteIssue->dpr_id }}
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                            Not Linked
                        </span>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Location --}}
    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="Issue Location"
            subtitle="Exact project location where the issue was observed."
            icon="📍"
        />

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5">

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Block
                </div>

                <div class="mt-1 font-medium text-gray-800">
                    {{ $siteIssue->block?->name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Floor
                </div>

                <div class="mt-1 font-medium text-gray-800">
                    {{ $siteIssue->floor?->name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Unit / Flat
                </div>

                <div class="mt-1 font-medium text-gray-800">
                    {{ $siteIssue->unit?->name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Room / Space
                </div>

                <div class="mt-1 font-medium text-gray-800">
                    {{ $siteIssue->room?->name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Sub-space / Element
                </div>

                <div class="mt-1 font-medium text-gray-800">
                    {{ $siteIssue->subspace?->name ?? '-' }}
                </div>
            </div>

        </div>

        @if($siteIssue->location_path)
            <div class="mt-5 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                {{ $siteIssue->location_path }}
            </div>
        @endif
    </div>

    {{-- Activity + Responsibility --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="Related Activity"
                subtitle="Construction activity associated with this issue."
                icon="⚙️"
            />

            <div class="font-semibold text-gray-800">
                {{ $siteIssue->activity?->activity_name ?? '-' }}
            </div>

            @if($siteIssue->related_activity)
                <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                    {{ $siteIssue->related_activity }}
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="Responsibility & Closure"
                subtitle="Assignment and target closure information."
                icon="👤"
            />

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Responsible
                    </div>

                    <div class="mt-1 font-medium text-gray-800">
                        {{ $siteIssue->responsible_person ?: '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Target Closure
                    </div>

                    <div class="mt-1 font-medium text-gray-800">
                        {{ $siteIssue->target_closure_date?->format('d/m/Y') ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Actual Closure
                    </div>

                    <div class="mt-1 font-medium text-gray-800">
                        {{ $siteIssue->actual_closure_date?->format('d/m/Y') ?? '-' }}
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- Description + Root Cause --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="Description"
                subtitle="Detailed description of the reported issue."
                icon="📝"
            />

            <div class="whitespace-pre-line text-sm leading-6 text-gray-700">
                {{ $siteIssue->description ?: '-' }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="Root Cause"
                subtitle="Identified or suspected root cause."
                icon="🔎"
            />

            <div class="whitespace-pre-line text-sm leading-6 text-gray-700">
                {{ $siteIssue->root_cause ?: '-' }}
            </div>
        </div>

    </div>

    {{-- Escalation --}}
    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="Escalation"
            subtitle="Escalation level currently assigned to this issue."
            icon="📢"
        />

        <div class="flex flex-wrap gap-3">

            @if($siteIssue->escalated_to_management)
                <span class="inline-flex rounded-full bg-red-100 px-4 py-2 text-sm font-semibold text-red-800">
                    Escalated to Management
                </span>
            @endif

            @if($siteIssue->escalated_to_pmo)
                <span class="inline-flex rounded-full bg-orange-100 px-4 py-2 text-sm font-semibold text-orange-800">
                    Escalated to PMO
                </span>
            @endif

            @if(
                !$siteIssue->escalated_to_pmo
                && !$siteIssue->escalated_to_management
            )
                <span class="text-sm text-gray-500">
                    No escalation.
                </span>
            @endif

        </div>
    </div>

    {{-- Resolution --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="Resolution"
                subtitle="Corrective action and resolution details."
                icon="✅"
            />

            <div class="whitespace-pre-line text-sm leading-6 text-gray-700">
                {{ $siteIssue->resolution ?: '-' }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="Remarks"
                subtitle="Additional notes and observations."
                icon="💬"
            />

            <div class="whitespace-pre-line text-sm leading-6 text-gray-700">
                {{ $siteIssue->remarks ?: '-' }}
            </div>
        </div>

    </div>

    {{-- Photos --}}
    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="Site Issue Photos"
            subtitle="Photographic evidence attached to this issue."
            icon="📷"
        >
            <x-slot:actions>
                <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-800">
                    {{ $siteIssue->photos->count() }} Photo{{ $siteIssue->photos->count() === 1 ? '' : 's' }}
                </span>
            </x-slot:actions>
        </x-rds.section-title>

        @if($siteIssue->photos->isNotEmpty())

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                @foreach($siteIssue->photos as $photo)

                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                        <a href="{{ $photo->file_url }}"
                           target="_blank"
                           rel="noopener">
                            <img src="{{ $photo->file_url }}"
                                 alt="{{ $photo->display_caption }}"
                                 class="h-56 w-full bg-gray-100 object-cover">
                        </a>

                        <div class="p-4">

                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                    {{ $photo->photo_type }}
                                </span>

                                @if($photo->file_size)
                                    <span class="text-xs text-gray-500">
                                        {{ $photo->formatted_file_size }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 text-sm font-medium text-gray-800">
                                {{ $photo->display_caption }}
                            </div>

                            @if($photo->uploader)
                                <div class="mt-2 text-xs text-gray-500">
                                    Uploaded by {{ $photo->uploader->name }}
                                </div>
                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="rounded-lg border border-dashed border-gray-300 px-5 py-10 text-center text-sm text-gray-500">
                No photographs were uploaded for this Site Issue.
            </div>

        @endif
    </div>

    {{-- Record Information --}}
    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <x-rds.section-title
            title="Record Information"
            subtitle="System information for this issue record."
            icon="ℹ️"
        />

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Created
                </div>

                <div class="mt-1 text-sm text-gray-800">
                    {{ $siteIssue->created_at?->format('d/m/Y h:i A') ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Last Updated
                </div>

                <div class="mt-1 text-sm text-gray-800">
                    {{ $siteIssue->updated_at?->format('d/m/Y h:i A') ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Record ID
                </div>

                <div class="mt-1 text-sm text-gray-800">
                    #{{ $siteIssue->id }}
                </div>
            </div>

        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-3 sm:flex sm:flex-wrap">

        <a href="{{ route('site-issues.edit', $siteIssue) }}"
           class="rounded-xl bg-amber-500 px-6 py-3 text-center font-semibold text-white hover:bg-amber-600">
            Edit Issue
        </a>

        <a href="{{ route('site-issues.index') }}"
           class="rounded-xl bg-gray-600 px-6 py-3 text-center font-semibold text-white hover:bg-gray-700">
            Back to Register
        </a>

    </div>

</div>

@endsection
