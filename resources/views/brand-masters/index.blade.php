@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <div>
        <h1 class="text-3xl font-bold">
            Brand Masters
        </h1>

        <p class="text-gray-500 mt-1">
            Manage standard brands used in Material Master.
        </p>
    </div>

    <button
        onclick="document.getElementById('addModal').classList.remove('hidden')"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded">
        + Add Brand
    </button>

</div>

@if(session('success'))
<div class="bg-green-100 text-green-700 p-4 rounded mb-6">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    <div class="bg-white rounded shadow p-5">
        <div class="text-gray-500">Total Brands</div>
        <div class="text-3xl font-bold mt-2">{{ $brands->total() }}</div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <div class="text-gray-500">Active</div>
        <div class="text-3xl font-bold text-green-600 mt-2">
            {{ \App\Models\BrandMaster::where('is_active', 1)->count() }}
        </div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <div class="text-gray-500">Inactive</div>
        <div class="text-3xl font-bold text-red-600 mt-2">
            {{ \App\Models\BrandMaster::where('is_active', 0)->count() }}
        </div>
    </div>

</div>

<div class="bg-white rounded shadow p-5 mb-6">

    <form method="GET">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="font-semibold block mb-2">Search</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Brand Name / Code"
                       class="border rounded p-3 w-full">
            </div>

            <div>
                <label class="font-semibold block mb-2">Status</label>
                <select name="status" class="border rounded p-3 w-full">
                    <option value="">All</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button class="bg-blue-600 text-white px-5 py-3 rounded">
                    Filter
                </button>

                <a href="{{ route('brand-masters.index') }}"
                   class="bg-gray-500 text-white px-5 py-3 rounded">
                    Clear
                </a>
            </div>

        </div>
    </form>

</div>

<div class="bg-white rounded shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 text-left">#</th>
                <th class="p-4 text-left">Category</th>
                <th class="p-4 text-left">Brand Name</th>
                <th class="p-4 text-left">Code</th>
                <th class="p-4 text-center">Status</th>
                <th class="p-4 text-center">Actions</th>
                
            </tr>
        </thead>

        <tbody>

            @forelse($brands as $brand)

                <tr class="border-b">

                    <td class="p-4">
                        {{ $loop->iteration + ($brands->currentPage() - 1) * $brands->perPage() }}
                    </td>
                    <td class="p-4">
    {{ $brand->category?->category_name ?? '-' }}
</td>

                    <td class="p-4 font-semibold">
                        {{ $brand->brand_name }}

                        @if($brand->remarks)
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $brand->remarks }}
                            </div>
                        @endif
                    </td>

                    <td class="p-4">
                        {{ $brand->brand_code ?? '-' }}
                    </td>

                    <td class="text-center">
                        @if($brand->is_active)
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded">
                                Active
                            </span>
                        @else
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded">
                                Inactive
                            </span>
                        @endif
                    </td>

                    <td class="text-center">

                        <a href="{{ route('brand-masters.edit', $brand) }}"
                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                            Edit
                        </a>

                        <form action="{{ route('brand-masters.toggle-status', $brand) }}"
                              method="POST"
                              class="inline">

                            @csrf
                            @method('PATCH')

                            <button onclick="return confirm('Change brand status?')"
                                    class="ml-2 px-4 py-2 rounded text-white
                                    {{ $brand->is_active
                                        ? 'bg-red-600 hover:bg-red-700'
                                        : 'bg-green-600 hover:bg-green-700' }}">
                                {{ $brand->is_active ? 'Deactivate' : 'Activate' }}
                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" class="text-center p-10 text-gray-500">
                        No Brands Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

    <div class="p-4">
        {{ $brands->links() }}
    </div>

</div>

<div id="addModal"
     class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">

    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 mx-4">

        <h2 class="text-2xl font-bold mb-6">
            Add Brand
        </h2>

        <form method="POST" action="{{ route('brand-masters.store') }}">

            @csrf
            <div class="mb-4">
    <label class="block font-semibold mb-2">
        Material Category
    </label>

    <select
        name="material_category_id"
        class="border rounded w-full p-3"
        required>

        <option value="">
            Select Material Category
        </option>

        @foreach($categories as $category)
            <option value="{{ $category->id }}">
                {{ $category->category_name }}
            </option>
        @endforeach

    </select>
</div>

            <div class="mb-4">
                <label class="font-semibold block mb-2">Brand Name</label>
                <input type="text"
                       name="brand_name"
                       class="border rounded w-full p-3"
                       required>
            </div>

            <div class="mb-4">
                <label class="font-semibold block mb-2">Brand Code</label>
                <input type="text"
                       name="brand_code"
                       class="border rounded w-full p-3">
            </div>

            <div class="mb-6">
                <label class="font-semibold block mb-2">Remarks</label>
                <textarea name="remarks"
                          rows="3"
                          class="border rounded w-full p-3"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="bg-gray-500 text-white px-5 py-2 rounded">
                    Cancel
                </button>

                <button class="bg-blue-600 text-white px-5 py-2 rounded">
                    Save
                </button>
            </div>

        </form>

    </div>

</div>

@endsection