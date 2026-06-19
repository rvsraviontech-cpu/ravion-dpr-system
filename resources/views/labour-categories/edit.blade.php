@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Labour Category
</h1>

<div class="bg-white rounded shadow p-6 max-w-2xl">

    <form method="POST"
          action="{{ route('labour-categories.update', $labourCategory) }}">

        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block font-semibold mb-2">
                Category Name
            </label>

            <input type="text"
                   name="category_name"
                   value="{{ old('category_name', $labourCategory->category_name) }}"
                   class="border rounded w-full p-3"
                   required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">
                Status
            </label>

            <select name="is_active"
                    class="border rounded w-full p-3"
                    required>
                <option value="1" {{ $labourCategory->is_active ? 'selected' : '' }}>
                    Active
                </option>

                <option value="0" {{ !$labourCategory->is_active ? 'selected' : '' }}>
                    Inactive
                </option>
            </select>
        </div>

        <div class="mb-6">
            <label class="block font-semibold mb-2">
                Remarks
            </label>

            <textarea name="remarks"
                      class="border rounded w-full p-3">{{ old('remarks', $labourCategory->remarks) }}</textarea>
        </div>

        <div class="flex gap-3">

            <button type="submit"
                    class="bg-blue-600 text-white px-5 py-3 rounded">
                Update Category
            </button>

            <a href="{{ route('labour-categories.index') }}"
               class="bg-gray-500 text-white px-5 py-3 rounded">
                Back
            </a>

        </div>

    </form>

</div>

@endsection