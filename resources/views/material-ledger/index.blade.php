@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';

    $totalMovements = count($ledgerRows);

    $totalReceived = collect($ledgerRows)
        ->sum(fn ($row) => (float) ($row['received_qty'] ?? 0));

    $totalIssued = collect($ledgerRows)
        ->sum(fn ($row) => (float) ($row['issued_qty'] ?? 0));

    $closingBalance = collect($ledgerRows)
        ->groupBy('variant_key')
        ->sum(function ($rows) {
            return (float) optional($rows->last())['balance_qty'];
        });

    $materialTypeOptionsForJs = $materialTypes
        ->map(fn ($type) => [
            'id' => $type->id,
            'name' => $type->material_type_name,
            'group' => $type->material_group,
        ])
        ->values();
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Material Ledger
            </h1>

            <p class="mt-1 text-gray-500">
                Review approved stock movements with received, issued, wastage and running balance.
            </p>
        </div>

        <button type="button"
                onclick="window.print()"
                class="print:hidden inline-flex items-center justify-center rounded-lg bg-slate-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
            Print
        </button>

    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Ledger Movements</p>

            <p class="mt-2 text-2xl font-bold text-blue-700">
                {{ $totalMovements }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total Received</p>

            <p class="mt-2 text-2xl font-bold text-green-700">
                {{ formatQuantity($totalReceived) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total Issued</p>

            <p class="mt-2 text-2xl font-bold text-red-700">
                {{ formatQuantity($totalIssued) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Combined Closing Balance</p>

            <p class="mt-2 text-2xl font-bold text-purple-700">
                {{ formatQuantity($closingBalance) }}
            </p>
        </div>

    </div>

    <form method="GET"
          action="{{ route('material-ledger.index') }}"
          class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-7">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    From Date
                </label>

                <input type="date"
                       name="from_date"
                       value="{{ request('from_date') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    To Date
                </label>

                <input type="date"
                       name="to_date"
                       value="{{ request('to_date') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Project
                </label>

                <select name="project_id"
                        id="project_id"
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
                    Block
                </label>

                <select name="project_block_id"
                        id="project_block_id"
                        class="{{ $inputClass }}">

                    <option value="">All Blocks</option>

                    @foreach($projectBlocks as $block)
                        <option value="{{ $block->id }}"
                                data-project="{{ $block->project_id }}"
                            {{ (string) request('project_block_id') === (string) $block->id ? 'selected' : '' }}>
                            {{ $block->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Material Group
                </label>

                <select name="material_group"
                        id="material_group"
                        class="{{ $inputClass }}">

                    <option value="">All Material Groups</option>

                    @foreach($materialTypes->pluck('material_group')->filter()->unique()->sort()->values() as $materialGroup)
                        <option value="{{ $materialGroup }}"
                            {{ request('material_group') === $materialGroup ? 'selected' : '' }}>
                            {{ $materialGroup }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Material
                </label>

                <select name="material_type_id"
                        id="material_type_id"
                        class="{{ $inputClass }}">

                    <option value="">All Materials</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('material-ledger.index') }}"
                   class="rounded-lg bg-gray-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">
                    Clear
                </a>
            </div>

        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="text-lg font-bold text-gray-800">
                Stock Movement Ledger
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Latest approved stock movements are shown first.
            </p>
        </div>

        <div class="max-h-[560px] overflow-auto">

            <table class="min-w-[1350px] w-full text-sm">

                <thead class="sticky top-0 z-10 bg-gray-100 text-xs uppercase tracking-wide text-gray-600 shadow-sm">
                    <tr>
                        <th class="px-4 py-3 text-center">#</th>
                        <th class="px-4 py-3 text-left">Project</th>
                        <th class="px-4 py-3 text-left">Material</th>
                        <th class="px-4 py-3 text-left">Brand</th>
                        <th class="px-4 py-3 text-left">Specification</th>
                        <th class="px-4 py-3 text-left">Grade</th>
                        <th class="px-4 py-3 text-left">Transaction</th>
                        <th class="px-4 py-3 text-right">Received</th>
                        <th class="px-4 py-3 text-right">Consumed</th>
                        <th class="px-4 py-3 text-right">Wastage</th>
                        <th class="px-4 py-3 text-right">Balance</th>
                        <th class="px-4 py-3 text-left">Unit</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse(collect($ledgerRows)->sortByDesc(function ($row) {
                        return strtotime(
                            (string) ($row['date'] ?? '')
                            . ' '
                            . (string) ($row['time'] ?? '00:00:00')
                        );
                    })->values() as $index => $row)

                        @php
                            $isReceived = ($row['movement_type'] ?? null) === 'IN';

                            $transactionClasses = $isReceived
                                ? 'bg-green-100 text-green-800'
                                : 'bg-red-100 text-red-800';

                            $balance = (float) ($row['balance_qty'] ?? 0);

                            $balanceClasses = match(true) {
                                $balance < 0 => 'bg-red-100 text-red-800',
                                $balance == 0 => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-green-100 text-green-800',
                            };
                        @endphp

                        <tr class="align-top hover:bg-gray-50">

                            <td class="px-4 py-3 text-center">
                                {{ $index + 1 }}
                            </td>

                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $row['project'] }}
                            </td>

                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $row['material'] }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $row['brand'] }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $row['specification'] }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $row['grade'] }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $transactionClasses }}">
                                    {{ $isReceived ? 'Received' : 'Consumed' }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right font-semibold text-green-700">
                                {{ formatQuantity($row['received_qty']) }}
                            </td>

                            <td class="px-4 py-3 text-right text-blue-700">
                                {{ formatQuantity($row['consumed_qty']) }}
                            </td>

                            <td class="px-4 py-3 text-right text-red-700">
                                {{ formatQuantity($row['wastage_qty']) }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex min-w-20 justify-center rounded-full px-3 py-1 text-xs font-bold {{ $balanceClasses }}">
                                    {{ formatQuantity($row['balance_qty']) }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                {{ $row['unit'] }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="12"
                                class="px-6 py-12 text-center text-gray-500">
                                No approved material ledger movements were found for the selected filters.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 text-sm text-gray-500">
            Running balance is calculated separately for each project and material variant.
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const projectSelect = document.getElementById('project_id');
    const blockSelect = document.getElementById('project_block_id');
    const groupSelect = document.getElementById('material_group');
    const materialSelect = document.getElementById('material_type_id');

    const selectedMaterialId = @json((string) request('material_type_id'));
    const materialTypes = @json($materialTypeOptionsForJs);

    if (projectSelect && blockSelect) {
        const originalBlockOptions = Array.from(blockSelect.options)
            .map(function (option) {
                return option.cloneNode(true);
            });

        function filterBlocks() {
            const selectedProject = projectSelect.value;
            const currentBlock = blockSelect.value;

            blockSelect.innerHTML = '';
            blockSelect.add(new Option('All Blocks', ''));

            originalBlockOptions.forEach(function (option) {
                if (option.value === '') {
                    return;
                }

                if (
                    selectedProject === ''
                    || String(option.dataset.project) === String(selectedProject)
                ) {
                    const cloned = option.cloneNode(true);

                    if (String(cloned.value) === String(currentBlock)) {
                        cloned.selected = true;
                    }

                    blockSelect.add(cloned);
                }
            });
        }

        projectSelect.addEventListener('change', function () {
            blockSelect.value = '';
            filterBlocks();
        });

        filterBlocks();
    }

    function rebuildMaterialOptions(preserveSelection = true) {
        const selectedGroup = groupSelect.value;

        const previousValue = preserveSelection
            ? (materialSelect.value || selectedMaterialId)
            : '';

        materialSelect.innerHTML = '';
        materialSelect.add(new Option('All Materials', ''));

        materialTypes
            .filter(function (materialType) {
                return selectedGroup === ''
                    || materialType.group === selectedGroup;
            })
            .forEach(function (materialType) {
                const option = new Option(
                    materialType.name,
                    String(materialType.id)
                );

                if (String(materialType.id) === String(previousValue)) {
                    option.selected = true;
                }

                materialSelect.add(option);
            });
    }

    groupSelect.addEventListener('change', function () {
        rebuildMaterialOptions(false);
    });

    rebuildMaterialOptions(true);
});
</script>

<style>
    @media print {
        nav,
        aside,
        header,
        .print\:hidden {
            display: none !important;
        }

        body {
            background: #ffffff !important;
        }

        .max-h-\[560px\] {
            max-height: none !important;
            overflow: visible !important;
        }

        .shadow-sm {
            box-shadow: none !important;
        }
    }
</style>

@endsection
