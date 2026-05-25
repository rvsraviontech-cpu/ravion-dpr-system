@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Add Vendor
</h1>

<div class="bg-white rounded shadow p-6">

    <form action="/vendors"
          method="POST">

        @csrf

        <div class="mb-4">

            <label class="block mb-2">
                Vendor Name
            </label>

            <input type="text"
                   name="vendor_name"
                   class="w-full border rounded p-2">

        </div>

        <div class="mb-4">

            <label class="block mb-2">
                Contact Person
            </label>

            <input type="text"
                   name="contact_person"
                   class="w-full border rounded p-2">

        </div>

        <div class="mb-4">

            <label class="block mb-2">
                Mobile
            </label>

            <input type="text"
                   name="mobile"
                   class="w-full border rounded p-2">

        </div>

        <div class="mb-4">

            <label class="block mb-2">
                Email
            </label>

            <input type="email"
                   name="email"
                   class="w-full border rounded p-2">

        </div>

        <div class="mb-6">

            <label class="block mb-2">
                Address
            </label>

            <textarea name="address"
                      class="w-full border rounded p-2"></textarea>

        </div>

        <button type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded">

            Save

        </button>

    </form>

</div>

@endsection