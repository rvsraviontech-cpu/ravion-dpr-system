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

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded shadow p-5">
        <div class="text-gray-500">Total Materials</div>
        <div class="text-3xl font-bold mt-2">
            {{ $materials->total() }}
        </div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <div class="text-gray-500">Categories</div>
        <div class="text-3xl font-bold text-blue-600 mt-2">
            {{ $categories->count() }}
        </div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <div class="text-gray-500">Active</div>
        <div class="text-3xl font-bold text-green-600 mt-2">
            {{ $materials->where('is_active',1)->count() }}
        </div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <div class="text-gray-500">Inactive</div>
        <div class="text-3xl font-bold text-red-600 mt-2">
            {{ $materials->where('is_active',0)->count() }}
        </div>
    </div>

</div>

<div class="bg-white rounded shadow p-5 mb-6">

<form method="GET">

<div class="grid grid-cols-1 md:grid-cols-4 gap-4">

<div>
<label class="block font-semibold mb-2">Category</label>

<select name="material_category_id"
class="border rounded w-full p-3">

<option value="">All Categories</option>

@foreach($categories as $category)

<option value="{{ $category->id }}"
{{ request('material_category_id')==$category->id ? 'selected':'' }}>

{{ $category->category_name }}

</option>

@endforeach

</select>

</div>

<div>

<label class="block font-semibold mb-2">

Status

</label>

<select name="status"
class="border rounded w-full p-3">

<option value="">All</option>

<option value="1"
{{ request('status')==='1' ? 'selected':'' }}>

Active

</option>

<option value="0"
{{ request('status')==='0' ? 'selected':'' }}>

Inactive

</option>

</select>

</div>

<div>

<label class="block font-semibold mb-2">

Search

</label>

<input
type="text"
name="search"
value="{{ request('search') }}"
placeholder="Material..."

class="border rounded w-full p-3">

</div>

<div class="flex items-end gap-2">

<button
class="bg-blue-600 text-white px-5 py-3 rounded">

Filter

</button>

<a href="{{ route('materials.index') }}"
class="bg-gray-500 text-white px-5 py-3 rounded">

Clear

</a>

</div>

</div>

</form>

</div>

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
                            {{ $material->brandMaster?->brand_name ?? '-' }}
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

                        <td class="p-3 text-center">

<a href="{{ route('materials.edit',$material) }}"
class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded">

Edit

</a>

<form
method="POST"
action="{{ route('materials.toggle-status',$material) }}"
class="inline">

@csrf
@method('PATCH')

<button
onclick="return confirm('Change material status?')"
class="px-3 py-2 rounded text-white
{{ $material->is_active
? 'bg-red-600 hover:bg-red-700'
: 'bg-green-600 hover:bg-green-700' }}">

{{ $material->is_active ? 'Deactivate' : 'Activate' }}

</button>

</form>

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