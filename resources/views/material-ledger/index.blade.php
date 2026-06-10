@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Material Ledger
        </h1>
        <p class="text-gray-500 mt-1">
            View material-wise receipt, consumption and running balance history.
        </p>
    </div>
</div>

<div class="bg-white p-4 rounded shadow mb-6">
    <form method="GET"
          action="{{ route('material-ledger.index') }}"
          class="grid grid-cols-1 md:grid-cols-6 gap-4">

        <div>
            <label class="block text-sm font-semibold mb-1">Material</label>
            <select name="material_id" class="border p-2 rounded w-full">
                <option value="">All Materials</option>

                @foreach($materials as $material)
                    <option value="{{ $material->id }}"
                        {{ request('material_id') == $material->id ? 'selected' : '' }}>
                        {{ $material->material_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Project</label>
            <select name="project_id" class="border p-2 rounded w-full">
                <option value="">All Projects</option>

                @foreach($projects as $project)
                    <option value="{{ $project->id }}"
                        {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Project Block</label>
            <select name="project_block_id" class="border p-2 rounded w-full">
                <option value="">All Blocks</option>

                @foreach($projectBlocks as $block)
                    <option value="{{ $block->id }}"
                        {{ request('project_block_id') == $block->id ? 'selected' : '' }}>
                        {{ $block->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">From Date</label>
            <input type="date"
                   name="from_date"
                   value="{{ request('from_date') }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">To Date</label>
            <input type="date"
                   name="to_date"
                   value="{{ request('to_date') }}"
                   class="border p-2 rounded w-full">
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Filter
            </button>

            <a href="{{ route('material-ledger.index') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Clear
            </a>
        </div>

    </form>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">

        <table class="min-w-full text-sm">

            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="p-3 text-left">#</th>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Type</th>
                    <th class="p-3 text-left">Project</th>
                    <th class="p-3 text-left">Block</th>
                    <th class="p-3 text-left">Reference</th>
                    <th class="p-3 text-center">Received</th>
                    <th class="p-3 text-center">Consumed</th>
                    <th class="p-3 text-center">Balance</th>
                    <th class="p-3 text-left">Unit</th>
                    <th class="p-3 text-left">Remarks</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">

                @forelse($ledgerRows as $index => $row)

                    <tr class="hover:bg-gray-50">

                        <td class="p-3">
                            {{ $index + 1 }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $row['date'] }}
                        </td>

                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs
                                {{ $row['type'] === 'Received' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $row['type'] }}
                            </span>
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $row['project'] }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $row['block'] }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $row['reference'] }}
                        </td>

                        <td class="p-3 text-center font-bold text-green-700">
                            {{ number_format($row['received_qty'], 2) }}
                        </td>

                        <td class="p-3 text-center font-bold text-red-700">
                            {{ number_format($row['consumed_qty'], 2) }}
                        </td>

                        <td class="p-3 text-center font-bold
                            {{ $row['balance_qty'] < 0 ? 'text-red-700' : 'text-blue-700' }}">
                            {{ number_format($row['balance_qty'], 2) }}
                        </td>

                        <td class="p-3">
                            {{ $row['unit'] }}
                        </td>

                        <td class="p-3">
                            {{ $row['remarks'] }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="11" class="p-6 text-center text-gray-500">
                            No ledger entries found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>
</div>

@endsection