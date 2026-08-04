<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                DPR Report
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                Daily site progress, labour attendance, materials, issues, and planning details.
            </p>
        </div>

        <div class="flex flex-wrap gap-3 print:hidden">

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
            >
                Print DPR
            </button>

            <a
                href="{{ route('dprs.pdf', $dpr->id) }}"
                class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
            >
                Download PDF
            </a>

        </div>

    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Project
            </p>

            <p class="mt-2 font-semibold text-gray-900">
                {{ $dpr->project?->project_name ?? '-' }}
            </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Engineer
            </p>

            <p class="mt-2 font-semibold text-gray-900">
                {{ $dpr->user?->name ?? '-' }}
            </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                DPR Date
            </p>

            <p class="mt-2 font-semibold text-gray-900">
                {{ \Carbon\Carbon::parse($dpr->dpr_date)->format('d M Y') }}
            </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Weather
            </p>

            <p class="mt-2 font-semibold text-gray-900">
                {{ $dpr->weather ?: '-' }}
            </p>
        </div>

    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3">

        <span class="text-sm font-semibold text-gray-700">
            Current Status:
        </span>

        @if($dpr->status === 'Approved')

            <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">
                Approved
            </span>

        @elseif($dpr->status === 'Rejected')

            <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-800">
                Rejected
            </span>

        @else

            <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-sm font-semibold text-yellow-800">
                Pending Review
            </span>

        @endif

    </div>

</div>