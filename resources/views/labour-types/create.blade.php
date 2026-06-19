@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="flex justify-between items-center mb-6">

        <div>

            <h1 class="text-3xl font-bold">
                Add Labour Type
            </h1>

            <p class="text-gray-500 mt-1">
                Create a new labour type under a labour category.
            </p>

        </div>

        <a href="{{ route('labour-types.index') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded">

            ← Back

        </a>

    </div>

    @if ($errors->any())

        <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded mb-6">

            <ul class="list-disc pl-5">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <div class="bg-white rounded-xl shadow-lg p-8">

        <form action="{{ route('labour-types.store') }}"
              method="POST">

            @csrf

            <div class="mb-6">

                <label class="block font-semibold mb-2">

                    Labour Category

                </label>

                <select name="labour_category_id"
                        class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500"
                        required>

                    <option value="">
                        Select Labour Category
                    </option>

                    @foreach($labourCategories as $category)

                        <option value="{{ $category->id }}"
                            {{ old('labour_category_id') == $category->id ? 'selected' : '' }}>

                            {{ $category->category_name }}

                        </option>

                    @endforeach

                </select>

            </div>


            <div class="mb-6">

                <label class="block font-semibold mb-2">

                    Labour Type Name

                </label>

                <input type="text"
                       name="labour_type_name"
                       value="{{ old('labour_type_name') }}"
                       placeholder="Example: Mason / Bricklayer"
                       class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500"
                       required>

            </div>

            <div class="flex gap-3">

                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">

                    Save Labour Type

                </button>

                <a href="{{ route('labour-types.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg">

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>

@endsection