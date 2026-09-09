@extends('layouts.app')

@section('content')

<div class="space-y-4">

    {{-- ============================================================
         PAGE HEADER
    ============================================================ --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

        <div>

            <div class="flex flex-wrap items-center gap-2">

                <a
                    href="{{ route('construction-work-packages.index') }}"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-50 hover:text-gray-900"
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
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                        Add Construction Work Package
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Create a construction group or add a Work Package beneath an existing group.
                    </p>
                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         SELECTED PARENT CONTEXT
    ============================================================ --}}
    @if($parent)

        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                        Creating under Construction Group
                    </p>

                    <div class="mt-1 flex flex-wrap items-center gap-2">

                        <span class="font-mono text-xs font-bold text-blue-700">
                            {{ $parent->code }}
                        </span>

                        <span class="text-sm font-bold text-blue-950">
                            {{ $parent->name }}
                        </span>

                    </div>

                </div>

                <a
                    href="{{ route('construction-work-packages.create') }}"
                    class="text-xs font-semibold text-blue-700 hover:text-blue-900"
                >
                    Create a new top-level group instead
                </a>

            </div>

        </div>

    @endif


    {{-- ============================================================
         FORM
    ============================================================ --}}
    <form
        method="POST"
        action="{{ route('construction-work-packages.store') }}"
    >
        @csrf

        @include('construction-work-packages._form')

    </form>

</div>

@endsection