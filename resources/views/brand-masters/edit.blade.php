@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">
                Edit Brand
            </h1>

            <p class="text-gray-500 mt-1">
                Update brand name, code and status.
            </p>
        </div>

        <a href="{{ route('brand-masters.index') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-8">

        <form method="POST"
              action="{{ route('brand-masters.update', $brandMaster) }}">

            @csrf
            @method('PUT')

            <div class="mb-5">
    <label class="block font-semibold mb-2">
        Material Category
    </label>

    <select name="material_category_id"
            class="border rounded w-full p-3"
            required>
        <option value="">Select Material Category</option>

        @foreach($categories as $category)
            <option value="{{ $category->id }}"
                {{ old('material_category_id', $brandMaster->material_category_id) == $category->id ? 'selected' : '' }}>
                {{ $category->category_name }}
            </option>
        @endforeach
    </select>
</div>

            <div class="mb-5">
                <label class="block font-semibold mb-2">
                    Brand Name
                </label>

                <input type="text"
                       name="brand_name"
                       value="{{ old('brand_name', $brandMaster->brand_name) }}"
                       class="border rounded w-full p-3"
                       required>
            </div>

            <div class="mb-5">
                <label class="block font-semibold mb-2">
                    Brand Code
                </label>

                <input type="text"
                       name="brand_code"
                       value="{{ old('brand_code', $brandMaster->brand_code) }}"
                       class="border rounded w-full p-3">
            </div>

            <div class="mb-5">
                <label class="block font-semibold mb-2">
                    Status
                </label>

                <select name="is_active"
                        class="border rounded w-full p-3"
                        required>
                    <option value="1" {{ $brandMaster->is_active ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="0" {{ !$brandMaster->is_active ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block font-semibold mb-2">
                    Remarks
                </label>

                <textarea name="remarks"
                          rows="3"
                          class="border rounded w-full p-3">{{ old('remarks', $brandMaster->remarks) }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded">
                    Update Brand
                </button>

                <a href="{{ route('brand-masters.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded">
                    Cancel
                </a>
            </div>

        </form>

    </div>

</div>

@endsection