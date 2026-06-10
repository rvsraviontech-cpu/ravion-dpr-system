@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Material Category
</h1>

@if($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST"
      action="{{ route('material-categories.update', $materialCategory) }}"
      class="bg-white p-6 rounded shadow">

    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="block font-semibold mb-1">Category Name</label>
            <input type="text"
                   name="category_name"
                   value="{{ old('category_name', $materialCategory->category_name) }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block font-semibold mb-1">Category Code</label>
            <input type="text"
                   name="category_code"
                   value="{{ old('category_code', $materialCategory->category_code) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Status</label>
            <select name="is_active"
                    class="border p-2 rounded w-full">
                <option value="1" {{ $materialCategory->is_active ? 'selected' : '' }}>
                    Active
                </option>
                <option value="0" {{ !$materialCategory->is_active ? 'selected' : '' }}>
                    Inactive
                </option>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block font-semibold mb-1">Remarks</label>
            <textarea name="remarks"
                      class="border p-2 rounded w-full">{{ old('remarks', $materialCategory->remarks) }}</textarea>
        </div>

    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded">
            Update Category
        </button>

        <a href="{{ route('material-categories.index') }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">
            Back
        </a>
    </div>

</form>

@endsection