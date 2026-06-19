@extends('layouts.app')

@section('content')

<div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">

    <div>
        <h1 class="text-3xl font-bold">
            Vendor Masters
        </h1>

        <p class="text-gray-500 mt-1">
            Manage vendors and suppliers for materials procurement.
        </p>
    </div>

    <a href="{{ route('vendors.create') }}"
       class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded">

        + Add Vendor

    </a>

</div>

@if(session('success'))
<div class="bg-green-100 text-green-700 p-4 rounded mb-6">
    {{ session('success') }}
</div>
@endif

{{-- Statistics --}}

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    <div class="bg-white rounded shadow p-5">

        <div class="text-gray-500">
            Total Vendors
        </div>

        <div class="text-3xl font-bold mt-2">

            {{ $vendors->total() }}

        </div>

    </div>

    <div class="bg-white rounded shadow p-5">

        <div class="text-gray-500">
            Active Vendors
        </div>

        <div class="text-3xl font-bold text-green-600 mt-2">

            {{ \App\Models\Vendor::where('is_active',1)->count() }}

        </div>

    </div>

    <div class="bg-white rounded shadow p-5">

        <div class="text-gray-500">
            Inactive Vendors
        </div>

        <div class="text-3xl font-bold text-red-600 mt-2">

            {{ \App\Models\Vendor::where('is_active',0)->count() }}

        </div>

    </div>

</div>

{{-- Filters --}}

<div class="bg-white rounded shadow p-5 mb-6">

<form method="GET">

<div class="grid grid-cols-1 md:grid-cols-4 gap-4">

<div>

<label class="font-semibold mb-2 block">

Material Category

</label>

<select
name="material_category_id"
class="border rounded w-full p-3">

<option value="">

All Categories

</option>

@foreach($categories as $category)

<option
value="{{ $category->id }}"
{{ request('material_category_id')==$category->id ? 'selected' : '' }}>

{{ $category->category_name }}

</option>

@endforeach

</select>

</div>

<div>

<label class="font-semibold mb-2 block">

Status

</label>

<select
name="status"
class="border rounded w-full p-3">

<option value="">All</option>

<option value="1"
{{ request('status')==='1' ? 'selected' : '' }}>

Active

</option>

<option value="0"
{{ request('status')==='0' ? 'selected' : '' }}>

Inactive

</option>

</select>

</div>

<div>

<label class="font-semibold mb-2 block">

Search

</label>

<input
type="text"
name="search"
value="{{ request('search') }}"
placeholder="Vendor / Contact / GST..."
class="border rounded w-full p-3">

</div>

<div class="flex items-end gap-2">

<button
class="bg-blue-600 text-white px-5 py-3 rounded">

Filter

</button>

<a
href="{{ route('vendors.index') }}"
class="bg-gray-500 text-white px-5 py-3 rounded">

Clear

</a>

</div>

</div>

</form>

</div>

<div class="bg-white rounded shadow overflow-hidden">

<table class="w-full">

<thead class="bg-gray-100">

<tr>

<th class="p-4 text-left">
Category
</th>

<th class="p-4 text-left">
Vendor
</th>

<th class="p-4 text-left">
Contact Person
</th>

<th class="p-4 text-left">
Mobile
</th>

<th class="p-4 text-left">
GST
</th>

<th class="p-4 text-center">
Status
</th>

<th class="p-4 text-center">
Actions
</th>

</tr>

</thead>

<tbody>

@forelse($vendors as $vendor)

<tr class="border-b">

<td class="p-4">

<span class="bg-blue-100 text-blue-700 px-3 py-1 rounded">

{{ $vendor->category?->category_name ?? '-' }}

</span>

</td>

<td class="p-4">

<div class="font-semibold">

{{ $vendor->vendor_name }}

</div>

<div class="text-gray-500 text-sm">

{{ $vendor->vendor_code }}

</div>

</td>

<td class="p-4">

{{ $vendor->contact_person }}

</td>

<td class="p-4">

{{ $vendor->mobile }}

</td>

<td class="p-4">

{{ $vendor->gst_number }}

</td>

<td class="text-center">

@if($vendor->is_active)

<span class="bg-green-100 text-green-700 px-3 py-1 rounded">

Active

</span>

@else

<span class="bg-red-100 text-red-700 px-3 py-1 rounded">

Inactive

</span>

@endif

</td>

<td class="text-center">

<a
href="{{ route('vendors.edit',$vendor) }}"
class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">

Edit

</a>

<form
action="{{ route('vendors.toggle-status',$vendor) }}"
method="POST"
class="inline">

@csrf
@method('PATCH')

<button
onclick="return confirm('Change vendor status?')"
class="ml-2 px-4 py-2 rounded text-white
{{ $vendor->is_active
? 'bg-red-600 hover:bg-red-700'
: 'bg-green-600 hover:bg-green-700' }}">

{{ $vendor->is_active ? 'Deactivate' : 'Activate' }}

</button>

</form>

</td>

</tr>

@empty

<tr>

<td colspan="7"
class="text-center p-10 text-gray-500">

No Vendors Found

</td>

</tr>

@endforelse

</tbody>

</table>

<div class="p-4">

{{ $vendors->links() }}

</div>

</div>

@endsection