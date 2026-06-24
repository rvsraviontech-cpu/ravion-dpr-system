@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">

Edit Activity Division

</h1>

<div class="bg-white rounded-lg shadow p-6">

<form
action="{{ route('activity-divisions.update',$activityDivision) }}"
method="POST">

@csrf
@method('PUT')

<div class="grid grid-cols-2 gap-6">

<div>

<label>Division Code</label>

<input
type="text"
name="code"
value="{{ $activityDivision->code }}"
class="w-full border rounded px-4 py-2">

</div>

<div>

<label>Division Name</label>

<input
type="text"
name="name"
value="{{ $activityDivision->name }}"
class="w-full border rounded px-4 py-2">

</div>

<div>

<label>Sequence</label>

<input
type="number"
name="sequence"
value="{{ $activityDivision->sequence }}"
class="w-full border rounded px-4 py-2">

</div>

<div>

<label>Remarks</label>

<input
type="text"
name="remarks"
value="{{ $activityDivision->remarks }}"
class="w-full border rounded px-4 py-2">

</div>

<div>

<label>

<input
type="checkbox"
name="is_active"
{{ $activityDivision->is_active?'checked':'' }}>

Active

</label>

</div>

</div>

<div class="mt-6">

<button
class="bg-blue-600 text-white px-6 py-2 rounded">

Update Division

</button>

</div>

</form>

</div>

@endsection