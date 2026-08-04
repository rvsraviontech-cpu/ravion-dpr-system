<div class="mb-4 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">

    {{-- Total Requests --}}
    <x-rds.card>
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
            Total Requests
        </div>

        <div class="mt-2 text-3xl font-bold text-gray-900">
            {{ $statistics['total'] ?? 0 }}
        </div>
    </x-rds.card>

    {{-- Draft --}}
    <x-rds.card>
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
            Draft
        </div>

        <div class="mt-2 text-3xl font-bold text-gray-600">
            {{ $statistics['draft'] ?? 0 }}
        </div>
    </x-rds.card>

    {{-- Submitted --}}
    <x-rds.card>
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
            Submitted
        </div>

        <div class="mt-2 text-3xl font-bold text-amber-600">
            {{ $statistics['submitted'] ?? 0 }}
        </div>
    </x-rds.card>

    {{-- Approved --}}
    <x-rds.card>
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
            Approved
        </div>

        <div class="mt-2 text-3xl font-bold text-green-600">
            {{ $statistics['approved'] ?? 0 }}
        </div>
    </x-rds.card>

    {{-- Applied --}}
    <x-rds.card>
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
            Applied
        </div>

        <div class="mt-2 text-3xl font-bold text-emerald-600">
            {{ $statistics['applied'] ?? 0 }}
        </div>
    </x-rds.card>

    {{-- Rejected --}}
    <x-rds.card>
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
            Rejected
        </div>

        <div class="mt-2 text-3xl font-bold text-red-600">
            {{ $statistics['rejected'] ?? 0 }}
        </div>
    </x-rds.card>

</div>