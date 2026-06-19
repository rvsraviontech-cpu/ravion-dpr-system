@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <div>
        <h1 class="text-3xl font-bold">
            Labour Categories
        </h1>

        <p class="text-gray-500 mt-1">
            Manage labour categories used throughout the DPR system.
        </p>
    </div>

    <button
        onclick="document.getElementById('addModal').classList.remove('hidden')"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded">

        + Add Category

    </button>

</div>

@if(session('success'))
<div class="bg-green-100 text-green-700 p-4 rounded mb-4">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    <div class="bg-white rounded shadow p-5">
        <div class="text-gray-500">
            Total Categories
        </div>

        <div class="text-3xl font-bold mt-2">
            {{ $categories->total() }}
        </div>
    </div>

    <div class="bg-white rounded shadow p-5">

        <div class="text-gray-500">
            Active
        </div>

        <div class="text-3xl font-bold text-green-600 mt-2">
            {{ $categories->where('is_active',1)->count() }}
        </div>

    </div>

    <div class="bg-white rounded shadow p-5">

        <div class="text-gray-500">
            Inactive
        </div>

        <div class="text-3xl font-bold text-red-600 mt-2">
            {{ $categories->where('is_active',0)->count() }}
        </div>

    </div>

</div>

<div class="bg-white rounded shadow overflow-hidden">

<table class="w-full">

<thead class="bg-gray-100">

<tr>

<th class="p-4 text-left">Category</th>

<th class="p-4 text-center">Labour Types</th>

<th class="p-4 text-center">Status</th>

<th class="p-4 text-center">Actions</th>

</tr>

</thead>

<tbody>

@forelse($categories as $category)

<tr class="border-b">

<td class="p-4 font-semibold">

{{ $category->category_name }}

@if($category->remarks)

<div class="text-xs text-gray-500 mt-1">

{{ $category->remarks }}

</div>

@endif

</td>

<td class="text-center">

{{ $category->labour_types_count }}

</td>

<td class="text-center">

@if($category->is_active)

<span class="bg-green-100 text-green-700 px-3 py-1 rounded text-sm">

Active

</span>

@else

<span class="bg-red-100 text-red-700 px-3 py-1 rounded text-sm">

Inactive

</span>

@endif

</td>

<td class="text-center space-x-2">

<a href="{{ route('labour-categories.edit',$category) }}"
class="bg-yellow-500 text-white px-3 py-2 rounded">

Edit

</a>

<form
action="{{ route('labour-categories.toggle-status',$category) }}"
method="POST"
class="inline">

@csrf
@method('PATCH')

<button
class="bg-blue-600 text-white px-3 py-2 rounded">

Toggle

</button>

</form>

</td>

</tr>

@empty

<tr>

<td colspan="4" class="text-center p-8 text-gray-500">

No Labour Categories Found

</td>

</tr>

@endforelse

</tbody>

</table>

<div class="p-4">

{{ $categories->links() }}

</div>

</div>


<div id="addModal"
     class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">

<div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 mx-4">

<h2 class="text-2xl font-bold mb-6">

Add Labour Category

</h2>

<form
method="POST"
action="{{ route('labour-categories.store') }}">

@csrf

<div class="mb-4">

<label class="block font-semibold mb-2">

Category Name

</label>

<input
type="text"
name="category_name"
class="border rounded w-full p-3"
required>

</div>

<div class="mb-6">

<label class="block font-semibold mb-2">

Remarks

</label>

<textarea
name="remarks"
class="border rounded w-full p-3"></textarea>

</div>

<div class="flex justify-end gap-3">

<button
type="button"
onclick="document.getElementById('addModal').classList.add('hidden')"
class="bg-gray-500 text-white px-4 py-2 rounded">

Cancel

</button>

<button
class="bg-blue-600 text-white px-4 py-2 rounded">

Save

</button>

</div>

</form>

</div>

</div>

@endsection