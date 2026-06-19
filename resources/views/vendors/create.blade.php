@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Add Vendor
</h1>

@if($errors->any())
<div class="bg-red-100 text-red-800 p-4 rounded mb-6">
    <ul class="list-disc ml-5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST"
      action="{{ route('vendors.store') }}"
      class="bg-white rounded-lg shadow p-6">

    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

        {{-- Vendor Code --}}
        <div>
            <label class="block font-semibold mb-2">
                Vendor Code
            </label>

            <input
                type="text"
                name="vendor_code"
                value="{{ old('vendor_code') }}"
                class="border rounded w-full p-3">
        </div>

        {{-- Material Category --}}
        <div>
            <label class="block font-semibold mb-2">
                Material Category
            </label>

            <select
                name="material_category_id"
                class="border rounded w-full p-3">

                <option value="">
                    Select Category
                </option>

                @foreach($categories as $category)

                    <option
                        value="{{ $category->id }}"
                        {{ old('material_category_id')==$category->id ? 'selected':'' }}>

                        {{ $category->category_name }}

                    </option>

                @endforeach

            </select>
        </div>

        {{-- Vendor Name --}}
        <div>
            <label class="block font-semibold mb-2">
                Vendor Name *
            </label>

            <input
                type="text"
                name="vendor_name"
                value="{{ old('vendor_name') }}"
                class="border rounded w-full p-3"
                required>
        </div>

        {{-- Contact --}}
        <div>
            <label class="block font-semibold mb-2">
                Contact Person
            </label>

            <input
                type="text"
                name="contact_person"
                value="{{ old('contact_person') }}"
                class="border rounded w-full p-3">
        </div>

        {{-- Mobile --}}
        <div>
            <label class="block font-semibold mb-2">
                Mobile
            </label>

            <input
                type="text"
                name="mobile"
                value="{{ old('mobile') }}"
                class="border rounded w-full p-3">
        </div>

        {{-- Alternate Mobile --}}
        <div>
            <label class="block font-semibold mb-2">
                Alternate Mobile
            </label>

            <input
                type="text"
                name="alternate_mobile"
                value="{{ old('alternate_mobile') }}"
                class="border rounded w-full p-3">
        </div>

        {{-- Email --}}
        <div>
            <label class="block font-semibold mb-2">
                Email
            </label>

            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="border rounded w-full p-3">
        </div>

        {{-- GST --}}
        <div>
            <label class="block font-semibold mb-2">
                GST Number
            </label>

            <input
                type="text"
                name="gst_number"
                value="{{ old('gst_number') }}"
                class="border rounded w-full p-3">
        </div>

        {{-- PAN --}}
        <div>
            <label class="block font-semibold mb-2">
                PAN Number
            </label>

            <input
                type="text"
                name="pan_number"
                value="{{ old('pan_number') }}"
                class="border rounded w-full p-3">
        </div>

        {{-- City --}}
        <div>
            <label class="block font-semibold mb-2">
                City
            </label>

            <input
                type="text"
                name="city"
                value="{{ old('city') }}"
                class="border rounded w-full p-3">
        </div>

        {{-- State --}}
        <div>
            <label class="block font-semibold mb-2">
                State
            </label>

            <input
                type="text"
                name="state"
                value="{{ old('state') }}"
                class="border rounded w-full p-3">
        </div>

        {{-- Pincode --}}
        <div>
            <label class="block font-semibold mb-2">
                Pincode
            </label>

            <input
                type="text"
                name="pincode"
                value="{{ old('pincode') }}"
                class="border rounded w-full p-3">
        </div>

        {{-- Payment Terms --}}
        <div>
            <label class="block font-semibold mb-2">
                Payment Terms
            </label>

            <input
                type="text"
                name="payment_terms"
                value="{{ old('payment_terms') }}"
                placeholder="Advance / 30 Days / 45 Days"
                class="border rounded w-full p-3">
        </div>

        {{-- Credit Days --}}
        <div>
            <label class="block font-semibold mb-2">
                Credit Days
            </label>

            <input
                type="number"
                name="credit_days"
                value="{{ old('credit_days',0) }}"
                class="border rounded w-full p-3">
        </div>

    </div>

    {{-- Address --}}
    <div class="mt-5">

        <label class="block font-semibold mb-2">
            Address
        </label>

        <textarea
            name="address"
            rows="3"
            class="border rounded w-full p-3">{{ old('address') }}</textarea>

    </div>

    {{-- Remarks --}}
    <div class="mt-5">

        <label class="block font-semibold mb-2">
            Remarks
        </label>

        <textarea
            name="remarks"
            rows="3"
            class="border rounded w-full p-3">{{ old('remarks') }}</textarea>

    </div>

    <div class="mt-8 flex gap-3">

        <button
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded">

            Save Vendor

        </button>

        <a
            href="{{ route('vendors.index') }}"
            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded">

            Back

        </a>

    </div>

</form>

@endsection