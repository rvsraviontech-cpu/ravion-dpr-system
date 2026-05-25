@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Add Labour Type
</h1>

<div class="bg-white rounded shadow p-6">

    <form action="/labour-types"
          method="POST">

        @csrf

        <div class="mb-6">

            <label class="block mb-2 font-semibold">
                Labour Type Name
            </label>

            <input type="text"
                   name="labour_type_name"
                   class="w-full border rounded p-2">

        </div>

        <button type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded">

            Save

        </button>

    </form>

</div>

@endsection