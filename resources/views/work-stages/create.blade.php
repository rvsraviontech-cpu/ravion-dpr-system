@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

<h1 class="text-3xl font-bold mb-6">
Create Work Stage
</h1>

<div class="bg-white rounded-lg shadow p-6">

<form action="{{ route('work-stages.store') }}"
method="POST">

@csrf

<div class="grid grid-cols-2 gap-6">

<div>

<label class="block mb-2 font-semibold">
Code
</label>

<input
type="text"
name="code"
class="w-full border rounded px-4 py-2"
required>

</div>

<div>

<label class="block mb-2 font-semibold">
Sequence
</label>

<input
type="number"
name="sequence"
value="1"
class="w-full border rounded px-4 py-2">

</div>

<div class="col-span-2">

<label class="block mb-2 font-semibold">
Work Stage Name
</label>

<input
type="text"
name="name"
class="w-full border rounded px-4 py-2"
required>

</div>

<div class="col-span-2">

<label class="block mb-2 font-semibold">
Remarks
</label>

<textarea
name="remarks"
rows="3"
class="w-full border rounded px-4 py-2"></textarea>

</div>

<div class="col-span-2">

<label>

<input
type="checkbox"
name="is_active"
checked>

Active

</label>

</div>

</div>

<div class="mt-6">

<button
class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">

Save Work Stage

</button>

</div>

</form>

</div>

</div>

@endsection