@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <div>
        <h1 class="text-3xl font-bold">
            Unit Masters
        </h1>

        <p class="text-gray-500 mt-1">
            Manage standard units used throughout the ERP.
        </p>
    </div>

    <button
        onclick="document.getElementById('addModal').classList.remove('hidden')"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded">

        + Add Unit

    </button>

</div>

@if(session('success'))

<div class="bg-green-100 text-green-700 p-4 rounded mb-6">
    {{ session('success') }}
</div>

@endif


<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

<div class="bg-white rounded shadow p-5">

<div class="text-gray-500">
Total Units
</div>

<div class="text-3xl font-bold mt-2">
{{ $units->total() }}
</div>

</div>

<div class="bg-white rounded shadow p-5">

<div class="text-gray-500">
Active
</div>

<div class="text-3xl font-bold text-green-600 mt-2">

{{ \App\Models\UnitMaster::where('is_active',1)->count() }}

</div>

</div>

<div class="bg-white rounded shadow p-5">

<div class="text-gray-500">
Inactive
</div>

<div class="text-3xl font-bold text-red-600 mt-2">

{{ \App\Models\UnitMaster::where('is_active',0)->count() }}

</div>

</div>

</div>



<div class="bg-white rounded shadow p-5 mb-6">

<form method="GET">

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">

<div>

<label class="font-semibold block mb-2">

Search

</label>

<input
type="text"
name="search"
value="{{ request('search') }}"
placeholder="Unit Name / Code"
class="border rounded p-3 w-full">

</div>

<div>

<label class="font-semibold block mb-2">

Status

</label>

<select
name="status"
class="border rounded p-3 w-full">

<option value="">All</option>

<option value="1"
{{ request('status')==='1' ? 'selected':'' }}>

Active

</option>

<option value="0"
{{ request('status')==='0' ? 'selected':'' }}>

Inactive

</option>

</select>

</div>

<div class="flex items-end gap-2">

<button class="bg-blue-600 text-white px-5 py-3 rounded">

Filter

</button>

<a
href="{{ route('unit-masters.index') }}"
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
#
</th>

<th class="p-4 text-left">
Unit Name
</th>

<th class="p-4 text-left">
Code
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

@forelse($units as $unit)

<tr class="border-b">

<td class="p-4">

{{ $loop->iteration + ($units->currentPage()-1)*$units->perPage() }}

</td>

<td class="p-4 font-semibold">

{{ $unit->unit_name }}

</td>

<td class="p-4">

{{ $unit->unit_code }}

</td>

<td class="text-center">

@if($unit->is_active)

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
href="{{ route('unit-masters.edit',$unit) }}"
class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">

Edit

</a>

<form
action="{{ route('unit-masters.toggle-status',$unit) }}"
method="POST"
class="inline">

@csrf
@method('PATCH')

<button
onclick="return confirm('Change unit status?')"
class="ml-2 px-4 py-2 rounded text-white
{{ $unit->is_active
? 'bg-red-600 hover:bg-red-700'
: 'bg-green-600 hover:bg-green-700' }}">

{{ $unit->is_active ? 'Deactivate' : 'Activate' }}

</button>

</form>

</td>

</tr>

@empty

<tr>

<td
colspan="5"
class="text-center p-10 text-gray-500">

No Units Found

</td>

</tr>

@endforelse

</tbody>

</table>

<div class="p-4">

{{ $units->links() }}

</div>

</div>



<div
id="addModal"
class="fixed inset-0 bg-black bg-opacity-40 hidden flex justify-center items-center z-50">

<div class="bg-white rounded-lg shadow-xl w-full max-w-lg">

<form
method="POST"
action="{{ route('unit-masters.store') }}">

@csrf

<div class="px-6 py-4 border-b">

<h2 class="text-2xl font-bold">

Add Unit

</h2>

</div>

<div class="p-6 space-y-4">

<div>

<label class="font-semibold">

Unit Name

</label>

<input
type="text"
name="unit_name"
class="border rounded w-full p-3"
required>

</div>

<div>

<label class="font-semibold">

Unit Code

</label>

<input
type="text"
name="unit_code"
class="border rounded w-full p-3">

</div>

<div>

<label class="font-semibold">

Remarks

</label>

<textarea
name="remarks"
rows="3"
class="border rounded w-full p-3"></textarea>

</div>

</div>

<div class="border-t px-6 py-4 flex justify-end gap-3">

<button
type="button"
onclick="document.getElementById('addModal').classList.add('hidden')"
class="bg-gray-500 text-white px-5 py-2 rounded">

Cancel

</button>

<button
class="bg-blue-600 text-white px-5 py-2 rounded">

Save

</button>

</div>

</form>

</div>

</div>

@endsection