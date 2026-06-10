@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Create Material Requirement
        </h1>
        <p class="text-gray-500 mt-1">
            Create material requirement for procurement and planning.
        </p>
    </div>

    <a href="{{ route('material-requirements.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
        Back
    </a>
</div>

@if ($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded shadow p-6">

<form method="POST"
      action="{{ route('material-requirements.store') }}">

    @csrf

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div>
            <label class="block text-sm font-semibold mb-1">
                Project
            </label>

            <select name="project_id"
                    class="border p-2 rounded w-full"
                    required>
                <option value="">Select Project</option>

                @foreach($projects as $project)
                    <option value="{{ $project->id }}">
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">
                Project Block
            </label>

            <select name="project_block_id"
                    class="border p-2 rounded w-full">
                <option value="">Select Block</option>

                @foreach($projectBlocks as $block)
                    <option value="{{ $block->id }}">
                        {{ $block->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">
                Required Date
            </label>

            <input type="date"
                   name="required_date"
                   class="border p-2 rounded w-full"
                   value="{{ old('required_date') }}">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">
                Material Category
            </label>

            <select name="material_category_id"
                    id="material_category_id"
                    class="border p-2 rounded w-full"
                    required>

                <option value="">
                    Select Category
                </option>

                @foreach($materialCategories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->category_name }}
                    </option>
                @endforeach

            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">
                Material
            </label>

            <select name="material_id"
                    id="material_id"
                    class="border p-2 rounded w-full"
                    required>

                <option value="">
                    Select Material
                </option>

                @foreach($materials as $material)
                    <option value="{{ $material->id }}"
                            data-category="{{ $material->material_category_id }}">
                        {{ $material->material_name }}
                    </option>
                @endforeach

            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">
                Unit
            </label>

            <input type="text"
                   name="unit"
                   class="border p-2 rounded w-full"
                   value="{{ old('unit') }}">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">
                Required Quantity
            </label>

            <input type="number"
                   step="0.01"
                   name="required_quantity"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">
                Priority
            </label>

            <select name="priority"
                    class="border p-2 rounded w-full"
                    required>

                <option value="Low">Low</option>
                <option value="Normal" selected>Normal</option>
                <option value="High">High</option>
                <option value="Urgent">Urgent</option>

            </select>
        </div>

    </div>

    <div class="mt-6">
        <label class="block text-sm font-semibold mb-1">
            Remarks
        </label>

        <textarea name="remarks"
                  rows="4"
                  class="border p-2 rounded w-full"></textarea>
    </div>

    <div class="mt-6">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
            Save Requirement
        </button>
    </div>

</form>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const categoryDropdown =
        document.getElementById('material_category_id');

    const materialDropdown =
        document.getElementById('material_id');

    const allOptions =
        Array.from(materialDropdown.options);

    categoryDropdown.addEventListener('change', function () {

        const selectedCategory =
            this.value;

        materialDropdown.innerHTML =
            '<option value="">Select Material</option>';

        allOptions.forEach(option => {

            if (
                option.value === '' ||
                option.dataset.category === selectedCategory
            ) {
                materialDropdown.appendChild(
                    option.cloneNode(true)
                );
            }

        });

    });

});

</script>

@endsection