@extends('layouts.app')

@section('content')

<div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">

    <div>
        <h1 class="text-3xl font-bold">
            Labour Types
        </h1>

        <p class="text-gray-500 mt-1">
            Manage labour types under each labour category.
        </p>
    </div>

    <a href="{{ route('labour-types.create') }}"
       class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded">

        + Add Labour Type

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
            Total Labour Types
        </div>

        <div class="text-3xl font-bold mt-2">

            {{ $labourTypes->total() }}

        </div>

    </div>

    <div class="bg-white rounded shadow p-5">

        <div class="text-gray-500">
            Categories
        </div>

        <div class="text-3xl font-bold text-blue-600 mt-2">

            {{ $labourCategories->count() }}

        </div>

    </div>

    <div class="bg-white rounded shadow p-5">

        <div class="text-gray-500">
            Showing
        </div>

        <div class="text-3xl font-bold text-green-600 mt-2">

            {{ $labourTypes->count() }}

        </div>

    </div>

</div>


{{-- Filters --}}

<div class="bg-white rounded shadow p-5 mb-6">

<form method="GET">

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">

<div>

<label class="font-semibold mb-2 block">

Labour Category

</label>

<select
name="labour_category_id"
class="border rounded w-full p-3">

<option value="">

All Categories

</option>

@foreach($labourCategories as $category)

<option
value="{{ $category->id }}"
{{ request('labour_category_id')==$category->id ? 'selected' : '' }}>

{{ $category->category_name }}

</option>

@endforeach

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
placeholder="Labour Type..."
class="border rounded w-full p-3">

</div>

<div class="flex items-end gap-2">

<button
class="bg-blue-600 text-white px-5 py-3 rounded">

Filter

</button>

<a
href="{{ route('labour-types.index') }}"
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

Labour Type

</th>

<th class="p-4 text-center">

Actions

</th>
<th class="p-4">Status</th>

</tr>

</thead>

<tbody>

@forelse($labourTypes as $type)

<tr class="border-b">

<td class="p-4">

<span class="bg-blue-100 text-blue-700 px-3 py-1 rounded">

{{ $type->labourCategory?->category_name ?? 'Unmapped' }}

</span>

</td>

<td class="p-4 font-semibold">

{{ $type->labour_type_name }}

</td>

<td class="text-center">

<a
href="{{ route('labour-types.edit',$type) }}"
class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">

Edit

</a>

<form method="POST"
      action="{{ route('labour-types.toggle',$type) }}"
      class="inline">

    @csrf

    <button
        onclick="return confirm('Change labour type status?')"
        class="px-3 py-2 rounded text-white
        {{ $type->status
            ? 'bg-red-600 hover:bg-red-700'
            : 'bg-green-600 hover:bg-green-700' }}">

        {{ $type->status ? 'Deactivate' : 'Activate' }}

    </button>

</form>

</td>

<td class="p-4">
    @if($type->status)
        <span class="bg-green-100 text-green-700 px-3 py-1 rounded">
            Active
        </span>
    @else
        <span class="bg-red-100 text-red-700 px-3 py-1 rounded">
            Inactive
        </span>
    @endif
</td>



</tr>

@empty

<tr>

<td
colspan="4"
class="text-center p-10 text-gray-500">

No Labour Types Found

</td>

</tr>

@endforelse

</tbody>

</table>

<div class="p-4">

{{ $labourTypes->links() }}

</div>

</div>

@endsection