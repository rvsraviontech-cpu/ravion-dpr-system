@extends('layouts.app')

@section('content')

<div class="space-y-4">

    {{-- Page Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

        <div class="flex items-start gap-2">

            <a
                href="{{ route('construction-work-packages.index') }}"
                class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-50 hover:text-gray-900"
                title="Back to Work Packages"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </a>

            <div>

                <div class="flex flex-wrap items-center gap-2">

                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                        Edit Construction Work Package
                    </h1>

                    <span class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 font-mono text-xs font-semibold text-gray-600">
                        {{ $constructionWorkPackage->code }}
                    </span>

                </div>

                <p class="mt-1 text-sm text-gray-500">
                    Update hierarchy, classification and administrative settings.
                </p>

            </div>

        </div>

    </div>


    {{-- Current Hierarchy Context --}}
    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Record Type
                </p>

                <p class="mt-1 text-sm font-bold text-gray-800">
                    {{ $constructionWorkPackage->parent_id ? 'Work Package' : 'Construction Group' }}
                </p>
            </div>

            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Current Parent
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-800">
                    @if($constructionWorkPackage->parent)
                        {{ $constructionWorkPackage->parent->code }}
                        —
                        {{ $constructionWorkPackage->parent->name }}
                    @else
                        Top-level Construction Group
                    @endif
                </p>
            </div>

            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Material Mappings
                </p>

                <p class="mt-1 text-sm font-bold text-gray-800">
                    {{ $constructionWorkPackage->materialTypes()->count() }}
                </p>
            </div>

        </div>

    </div>


    {{-- Form --}}
    <form
        method="POST"
        action="{{ route('construction-work-packages.update', $constructionWorkPackage) }}"
    >
        @csrf
        @method('PUT')

        @include('construction-work-packages._form')

    </form>

</div>

@endsection