@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Vendor
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
      action="{{ route('vendors.update',$vendor) }}"
      class="bg-white rounded-lg shadow p-6">

    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

        {{-- Vendor Code --}}
        <div>

            <label class="block font-semibold mb-2">
                Vendor Code
            </label>

            <input
                type="text"
                name="vendor_code"
                value="{{ old('vendor_code',$vendor->vendor_code) }}"
                class="border rounded w-full p-3">

        </div>

        {{-- Category --}}
        <div>

            <label class="block font-semibold mb-2">
                Material Category
            </label>

            <select
                name="material_category_id"
                class="border rounded w-full p-3">

                <option value="">Select Category</option>

                @foreach($categories as $category)

                    <option
                        value="{{ $category->id }}"
                        {{ old('material_category_id',$vendor->material_category_id)==$category->id ? 'selected':'' }}>

                        {{ $category->category_name }}

                    </option>

                @endforeach

            </select>

        </div>

        {{-- Vendor Name --}}
        <div>

            <label class="block font-semibold mb-2">
                Vendor Name
            </label>

            <input
                type="text"
                name="vendor_name"
                value="{{ old('vendor_name',$vendor->vendor_name) }}"
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
                value="{{ old('contact_person',$vendor->contact_person) }}"
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
                value="{{ old('mobile',$vendor->mobile) }}"
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
                value="{{ old('alternate_mobile',$vendor->alternate_mobile) }}"
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
                value="{{ old('email',$vendor->email) }}"
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
                value="{{ old('gst_number',$vendor->gst_number) }}"
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
                value="{{ old('pan_number',$vendor->pan_number) }}"
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
                value="{{ old('city',$vendor->city) }}"
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
                value="{{ old('state',$vendor->state) }}"
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
                value="{{ old('pincode',$vendor->pincode) }}"
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
                value="{{ old('payment_terms',$vendor->payment_terms) }}"
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
                value="{{ old('credit_days',$vendor->credit_days) }}"
                class="border rounded w-full p-3">

        </div>

        {{-- Status --}}
        <div>

            <label class="block font-semibold mb-2">
                Status
            </label>

            <select
                name="is_active"
                class="border rounded w-full p-3">

                <option value="1"
                    {{ $vendor->is_active ? 'selected':'' }}>

                    Active

                </option>

                <option value="0"
                    {{ !$vendor->is_active ? 'selected':'' }}>

                    Inactive

                </option>

            </select>

        </div>

    </div>

    <div class="mt-5">

        <label class="block font-semibold mb-2">
            Address
        </label>

        <textarea
            name="address"
            rows="3"
            class="border rounded w-full p-3">{{ old('address',$vendor->address) }}</textarea>

    </div>

    <div class="mt-5">

        <label class="block font-semibold mb-2">
            Remarks
        </label>

        <textarea
            name="remarks"
            rows="3"
            class="border rounded w-full p-3">{{ old('remarks',$vendor->remarks) }}</textarea>

    </div>

    <div class="mt-8 flex gap-3">

        <button
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded">

            Update Vendor

        </button>

        <a
            href="{{ route('vendors.index') }}"
            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded">

            Back

        </a>

    </div>

</form>

@endsection