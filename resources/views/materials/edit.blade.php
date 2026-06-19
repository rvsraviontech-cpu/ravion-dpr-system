@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Material
</h1>

@if($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST"
      action="{{ route('materials.update', $material) }}"
      class="bg-white p-6 rounded shadow">

    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="block font-semibold mb-1">Material Category</label>
            <select name="material_category_id"
                    class="border p-2 rounded w-full">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ $material->material_category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Material Code</label>
            <input type="text"
                   name="material_code"
                   value="{{ old('material_code', $material->material_code) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Material Name</label>
            <input type="text"
                   name="material_name"
                   value="{{ old('material_name', $material->material_name) }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block font-semibold mb-1">Specification</label>
            <input type="text"
                   name="specification"
                   value="{{ old('specification', $material->specification) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Brand</label>
            <select name="brand_master_id"
        class="border rounded w-full p-3">
    <option value="">Select Brand</option>

    @foreach($brands as $brand)
        <option value="{{ $brand->id }}"
            {{ old('brand_master_id', $material->brand_master_id) == $brand->id ? 'selected' : '' }}>
            {{ $brand->brand_name }}
            @if($brand->brand_code)
                ({{ $brand->brand_code }})
            @endif
        </option>
    @endforeach
</select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Unit</label>
            <input type="text"
                   name="unit"
                   value="{{ old('unit', $material->unit) }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block font-semibold mb-1">Minimum Stock Level</label>
            <input type="number"
                   step="0.01"
                   name="minimum_stock_level"
                   value="{{ old('minimum_stock_level', $material->minimum_stock_level) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Status</label>
            <select name="is_active"
                    class="border p-2 rounded w-full">
                <option value="1" {{ $material->is_active ? 'selected' : '' }}>
                    Active
                </option>
                <option value="0" {{ !$material->is_active ? 'selected' : '' }}>
                    Inactive
                </option>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block font-semibold mb-1">Remarks</label>
            <textarea name="remarks"
                      rows="3"
                      class="border p-2 rounded w-full">{{ old('remarks', $material->remarks) }}</textarea>
        </div>

    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded">
            Update Material
        </button>

        <a href="{{ route('materials.index') }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">
            Back
        </a>
    </div>

</form>

@endsection