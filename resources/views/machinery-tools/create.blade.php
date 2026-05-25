@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Add Machinery / Tool
</h1>

<div class="bg-white rounded shadow p-6">

    <form action="/machinery-tools"
          method="POST">

        @csrf

        <div class="mb-4">

            <label class="block mb-2">
                Machine Name
            </label>

            <input type="text"
                   name="machine_name"
                   class="w-full border rounded p-2">

        </div>

        <div class="mb-4">

            <label class="block mb-2">
                Ownership Type
            </label>

            <select name="ownership_type"
                    class="w-full border rounded p-2">

                <option value="Owned">
                    Owned
                </option>

                <option value="Rented">
                    Rented
                </option>

                <option value="Contractor Provided">
                    Contractor Provided
                </option>

            </select>

        </div>

        <div class="mb-6">

            <label class="block mb-2">
                Unit
            </label>

            <input type="text"
                   name="unit"
                   value="Nos"
                   class="w-full border rounded p-2">

        </div>

        <button type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded">

            Save

        </button>

    </form>

</div>

@endsection