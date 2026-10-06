@extends('layouts.app')

@section('content')
@php
    $statusClasses = $sitePhoto->status === 'Submitted'
        ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
        : 'bg-amber-100 text-amber-800 border-amber-200';

    $photoCount = $sitePhoto->photos->count();

    $locationPath = $sitePhoto->location_path ?: 'General site / No specific location';
    $workPath = $sitePhoto->work_path ?: 'General site photography / No work activity selected';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
                    Site Photos #{{ $sitePhoto->id }}
                </h1>

                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                    {{ $sitePhoto->status }}
                </span>
            </div>

            <p class="mt-2 text-sm text-slate-600">
                Daily visual site evidence recorded independently and available for DPR reporting.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if(is_null($sitePhoto->dpr_id))
                <a href="{{ route('site-photos.edit', $sitePhoto) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Edit Entry
                </a>
            @endif

            <a href="{{ route('site-photos.index') }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-700">
                Back to Site Photos
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Primary details --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
        <section class="lg:col-span-2 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Site Photo Entry</h2>
                <p class="mt-1 text-sm text-slate-500">Project, date and reporting information.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 p-5">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Project</div>
                    <div class="mt-1 font-semibold text-slate-900">
                        {{ $sitePhoto->project?->project_name ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Photo Date</div>
                    <div class="mt-1 font-semibold text-slate-900">
                        {{ $sitePhoto->photo_date?->format('d M Y') ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Reported By</div>
                    <div class="mt-1 font-semibold text-slate-900">
                        {{ $sitePhoto->reporter?->name ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Category</div>
                    <div class="mt-1 font-semibold text-slate-900">
                        {{ $sitePhoto->category ?: '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Contractor</div>
                    <div class="mt-1 font-semibold text-slate-900">
                        {{ $sitePhoto->contractor?->contractor_name ?? $sitePhoto->contractor?->name ?? 'Not Applicable' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Submitted At</div>
                    <div class="mt-1 font-semibold text-slate-900">
                        {{ $sitePhoto->submitted_at?->format('d M Y, h:i A') ?? '—' }}
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">DPR Integration</h2>
            </div>

            <div class="p-5">
                @if($sitePhoto->dpr_id)
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                        <div class="text-sm font-semibold text-blue-900">Linked to DPR</div>
                        <div class="mt-1 text-sm text-blue-700">DPR #{{ $sitePhoto->dpr_id }}</div>
                        <p class="mt-3 text-xs leading-5 text-blue-700">
                            This entry is protected from normal editing or deletion because it is linked to a DPR.
                        </p>
                    </div>
                @else
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-semibold text-slate-800">Not yet linked</div>
                        <p class="mt-2 text-xs leading-5 text-slate-600">
                            This entry remains independent site evidence and can be included in the DPR later.
                        </p>
                    </div>
                @endif
            </div>
        </section>
    </div>

    {{-- Location and work classification --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Photo Location</h2>
                <p class="mt-1 text-sm text-slate-500">Most specific recorded project location.</p>
            </div>

            <div class="p-5">
                <div class="rounded-lg bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800">
                    {{ $locationPath }}
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Block</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $sitePhoto->block?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Floor</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $sitePhoto->floor?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Unit / Flat</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $sitePhoto->unit?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Room / Space</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $sitePhoto->room?->name ?? '—' }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-slate-500">Sub-space / Element</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $sitePhoto->subspace?->name ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Work Classification</h2>
                <p class="mt-1 text-sm text-slate-500">Canonical Work Execution Catalogue classification.</p>
            </div>

            <div class="p-5">
                <div class="rounded-lg bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800">
                    {{ $workPath }}
                </div>

                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Work Package</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $sitePhoto->workPackage?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Work Section</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $sitePhoto->workSection?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Work Activity</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $sitePhoto->workActivity?->name ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </section>
    </div>

    {{-- Description --}}
    @if($sitePhoto->title || $sitePhoto->remarks)
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm mb-5">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Description & Remarks</h2>
            </div>

            <div class="p-5 space-y-4">
                @if($sitePhoto->title)
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Title</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $sitePhoto->title }}</div>
                    </div>
                @endif

                @if($sitePhoto->remarks)
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Remarks</div>
                        <div class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $sitePhoto->remarks }}</div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Photo gallery --}}
    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-slate-900">Photographic Evidence</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Click any photograph to view it at full size.
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-800">
                {{ $photoCount }} {{ \Illuminate\Support\Str::plural('Photo', $photoCount) }}
            </span>
        </div>

        <div class="p-5">
            @forelse($sitePhoto->photos as $photo)
                @if($loop->first)
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @endif

                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <button type="button"
                            class="group relative block w-full bg-slate-100 text-left"
                            onclick="openSitePhotoModal(
                                @js(asset('storage/' . $photo->file_path)),
                                @js($photo->display_caption),
                                @js($photo->photo_type)
                            )">
                        <img src="{{ asset('storage/' . $photo->file_path) }}"
                             alt="{{ $photo->display_caption }}"
                             loading="lazy"
                             class="h-64 w-full object-cover transition duration-200 group-hover:scale-[1.01]">

                        <span class="absolute right-3 top-3 rounded-full bg-black/70 px-2.5 py-1 text-xs font-semibold text-white">
                            View Full Size
                        </span>
                    </button>

                    <div class="p-4">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                {{ $photo->photo_type }}
                            </span>

                            <span class="text-xs text-slate-500">
                                Photo #{{ $photo->sort_order ?: $loop->iteration }}
                            </span>
                        </div>

                        @if($photo->caption)
                            <p class="mt-3 text-sm font-medium leading-6 text-slate-800">
                                {{ $photo->caption }}
                            </p>
                        @endif

                        <dl class="mt-4 space-y-2 border-t border-slate-100 pt-3 text-xs">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">Captured</dt>
                                <dd class="text-right font-medium text-slate-700">
                                    {{ $photo->captured_at?->format('d M Y, h:i A') ?? 'Not recorded' }}
                                </dd>
                            </div>

                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">Uploaded By</dt>
                                <dd class="text-right font-medium text-slate-700">
                                    {{ $photo->uploader?->name ?? '—' }}
                                </dd>
                            </div>

                            @if($photo->file_size)
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">File Size</dt>
                                    <dd class="text-right font-medium text-slate-700">
                                        {{ number_format($photo->file_size / 1024 / 1024, 2) }} MB
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </article>

                @if($loop->last)
                    </div>
                @endif
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                    <div class="text-sm font-semibold text-slate-700">No photographs found</div>
                    <p class="mt-1 text-sm text-slate-500">This entry currently has no photographic evidence.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>

{{-- Full-size photo modal --}}
<div id="sitePhotoModal"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-3 sm:p-6"
     role="dialog"
     aria-modal="true"
     aria-labelledby="sitePhotoModalTitle"
     onclick="closeSitePhotoModal(event)">

    <div class="relative flex max-h-full w-full max-w-6xl flex-col overflow-hidden rounded-xl bg-slate-950 shadow-2xl"
         onclick="event.stopPropagation()">

        <div class="flex items-center justify-between gap-4 border-b border-white/10 px-4 py-3 text-white">
            <div class="min-w-0">
                <div id="sitePhotoModalType" class="text-xs font-semibold uppercase tracking-wide text-slate-300"></div>
                <div id="sitePhotoModalTitle" class="truncate text-sm font-semibold sm:text-base"></div>
            </div>

            <button type="button"
                    onclick="closeSitePhotoModal()"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 text-xl text-white hover:bg-white/20"
                    aria-label="Close full-size photograph">
                &times;
            </button>
        </div>

        <div class="flex min-h-0 flex-1 items-center justify-center overflow-auto p-2 sm:p-4">
            <img id="sitePhotoModalImage"
                 src=""
                 alt=""
                 class="max-h-[80vh] max-w-full object-contain">
        </div>
    </div>
</div>

<script>
    function openSitePhotoModal(src, caption, type) {
        const modal = document.getElementById('sitePhotoModal');
        const image = document.getElementById('sitePhotoModalImage');
        const title = document.getElementById('sitePhotoModalTitle');
        const typeLabel = document.getElementById('sitePhotoModalType');

        image.src = src;
        image.alt = caption || type || 'Site Photo';
        title.textContent = caption || type || 'Site Photo';
        typeLabel.textContent = type || '';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeSitePhotoModal(event = null) {
        if (event && event.target !== event.currentTarget) {
            return;
        }

        const modal = document.getElementById('sitePhotoModal');
        const image = document.getElementById('sitePhotoModalImage');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        image.src = '';
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeSitePhotoModal();
        }
    });
</script>
@endsection
