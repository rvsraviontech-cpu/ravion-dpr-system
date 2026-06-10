@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Material Shortage Report
        </h1>
        <p class="text-gray-500 mt-1">
            Track open material requirements against available stock.
        </p>
    </div>
</div>

<div class="bg-white p-4 rounded shadow mb-6">
    <form method="GET"
          action="{{ route('material-shortage-report.index') }}"
          class="grid grid-cols-1 md:grid-cols-3 gap-4">

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

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Filter
            </button>

            <a href="{{ route('material-shortage-report.index') }}"
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
                    <th class="p-3 text-left">Material</th>
                    <th class="p-3 text-left">Unit</th>
                    <th class="p-3 text-center">Required Qty</th>
                    <th class="p-3 text-center">Fulfilled Qty</th>
                    <th class="p-3 text-center">Open Requirement</th>
                    <th class="p-3 text-center">Available Stock</th>
                    <th class="p-3 text-center">Shortage Qty</th>
                    <th class="p-3 text-center">Status</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">

            @forelse($reportRows as $index => $row)

                <tr class="hover:bg-gray-50">

                    <td class="p-3">
                        {{ $index + 1 }}
                    </td>

                    <td class="p-3 font-semibold">
                        {{ $row['material']->material_name }}
                    </td>

                    <td class="p-3">
                        {{ $row['unit'] }}
                    </td>

                    <td class="p-3 text-center font-bold">
                        {{ number_format($row['required_qty'], 2) }}
                    </td>

                    <td class="p-3 text-center font-bold text-green-700">
                        {{ number_format($row['fulfilled_qty'], 2) }}
                    </td>

                    <td class="p-3 text-center font-bold text-orange-700">
                        {{ number_format($row['open_requirement'], 2) }}
                    </td>

                    <td class="p-3 text-center font-bold text-blue-700">
                        {{ number_format($row['available_stock'], 2) }}
                    </td>

                    <td class="p-3 text-center font-bold
                        {{ $row['shortage_qty'] > 0 ? 'text-red-700' : 'text-green-700' }}">
                        {{ number_format($row['shortage_qty'], 2) }}
                    </td>

                    <td class="p-3 text-center">
                        @if($row['shortage_qty'] > 0)
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">
                                Shortage
                            </span>
                        @else
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                Available
                            </span>
                        @endif
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="9" class="p-6 text-center text-gray-500">
                        No shortage data found.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>
</div>

@endsection