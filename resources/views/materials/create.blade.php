@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Add Material
</h1>

@if($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST"
      action="{{ route('materials.store') }}"
      class="bg-white p-6 rounded shadow">

    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="block font-semibold mb-1">
                Material Category
            </label>

            <select name="material_category_id"
                    class="border p-2 rounded w-full">

                <option value="">
                    Select Category
                </option>

                @foreach($categories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->category_name }}
                    </option>
                @endforeach

            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Material Code
            </label>

            <input type="text"
                   name="material_code"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Material Name
            </label>

            <input type="text"
                   name="material_name"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Specification
            </label>

            <input type="text"
                   name="specification"
                   class="border p-2 rounded w-full">
        </div>

        <div>
    <label class="block font-semibold mb-1">
        Brand
    </label>

    <select name="brand_master_id"
            class="border rounded w-full p-3">
        <option value="">Select Brand</option>

        @foreach($brands as $brand)
            <option value="{{ $brand->id }}"
                    data-category="{{ $brand->material_category_id }}">
                {{ $brand->brand_name }}
                @if($brand->brand_code)
                    ({{ $brand->brand_code }})
                @endif
            </option>
        @endforeach
    </select>
</div>

        <div>
            <label class="block font-semibold mb-1">
                Unit
            </label>

            <select name="unit"
        class="border rounded w-full p-3"
        required>
    <option value="">Select Unit</option>

    @foreach($units as $unit)
        <option value="{{ $unit->unit_code ?? $unit->unit_name }}">
            {{ $unit->unit_name }}
            @if($unit->unit_code)
                ({{ $unit->unit_code }})
            @endif
        </option>
    @endforeach
</select>
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Minimum Stock Level
            </label>

            <input type="number"
                   step="0.01"
                   name="minimum_stock_level"
                   value="0"
                   class="border p-2 rounded w-full">
        </div>

        <div class="md:col-span-2">
            <label class="block font-semibold mb-1">
                Remarks
            </label>

            <textarea name="remarks"
                      rows="3"
                      class="border p-2 rounded w-full"></textarea>
        </div>

    </div>

    <div class="mt-6 flex gap-3">

        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded">
            Save Material
        </button>

        <a href="{{ route('materials.index') }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">
            Back
        </a>

    </div>

</form>

<script>
const categorySelect = document.querySelector('[name="material_category_id"]');
const brandSelect = document.querySelector('[name="brand_master_id"]');

if (categorySelect && brandSelect) {
    const brandOptions = Array.from(brandSelect.querySelectorAll('option'))
        .map(option => option.cloneNode(true));

    categorySelect.addEventListener('change', function () {
        const selectedCategory = this.value;

        brandSelect.innerHTML = '';
        brandSelect.add(new Option('Select Brand', ''));

        brandOptions.forEach(function (option) {
            if (
                option.value !== '' &&
                option.dataset.category === selectedCategory
            ) {
                brandSelect.add(option.cloneNode(true));
            }
        });
    });
}
</script>

@endsection