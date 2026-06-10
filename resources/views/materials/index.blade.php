@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Materials Master
        </h1>
        <p class="text-gray-500 mt-1">
            Manage standard materials for received, consumed and inventory tracking.
        </p>
    </div>

    <a href="{{ route('materials.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
        + Add Material
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded shadow overflow-hidden">

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="p-3 text-left">#</th>
                    <th class="p-3 text-left">Code</th>
                    <th class="p-3 text-left">Category</th>
                    <th class="p-3 text-left">Material</th>
                    <th class="p-3 text-left">Specification</th>
                    <th class="p-3 text-left">Brand</th>
                    <th class="p-3 text-left">Unit</th>
                    <th class="p-3 text-left">Min Stock</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($materials as $index => $material)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3">
                            {{ $materials->firstItem() + $index }}
                        </td>

                        <td class="p-3">
                            {{ $material->material_code ?? '-' }}
                        </td>

                        <td class="p-3">
                            {{ $material->category?->category_name ?? '-' }}
                        </td>

                        <td class="p-3 font-semibold">
                            {{ $material->material_name }}
                        </td>

                        <td class="p-3">
                            {{ $material->specification ?? '-' }}
                        </td>

                        <td class="p-3">
                            {{ $material->brand ?? '-' }}
                        </td>

                        <td class="p-3">
                            {{ $material->unit }}
                        </td>

                        <td class="p-3">
                            {{ $material->minimum_stock_level }}
                        </td>

                        <td class="p-3">
                            @if($material->is_active)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                    Active
                                </span>
                            @else
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">
                                    Inactive
                                </span>
                            @endif
                        </td>

                        <td class="p-3">
                            <a href="{{ route('materials.edit', $material) }}"
                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                                Edit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10"
                            class="p-6 text-center text-gray-500">
                            No materials found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<div class="mt-4">
    {{ $materials->links() }}
</div>

@endsection