@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Add Material
</h1>

<div class="bg-white rounded shadow p-6">

    <form action="/materials"
          method="POST">

        @csrf

        <div class="mb-6">

            <label class="block mb-2 font-semibold">
                Material Name
            </label>

            <input type="text"
                   name="material_name"
                   class="w-full border rounded p-2">

        </div>

        <div class="mb-6">

            <label class="block mb-2 font-semibold">
                Unit
            </label>

            <input type="text"
                   name="unit"
                   placeholder="Kg / Bags / Loads"
                   class="w-full border rounded p-2">

        </div>

        <button type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded">

            Save

        </button>

    </form>

</div>

@endsection