@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Contractor
</h1>

<div class="bg-white rounded shadow p-6">

<form action="/contractors/{{ $contractor->id }}"
      method="POST">

    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-6">

        <div>

            <label class="block mb-2">
                Contractor Name
            </label>

            <input type="text"
                   name="contractor_name"
                   value="{{ $contractor->contractor_name }}"
                   class="w-full border rounded px-4 py-2">

        </div>

        <div>

            <label class="block mb-2">
                Mobile
            </label>

            <input type="text"
                   name="mobile"
                   value="{{ $contractor->mobile }}"
                   class="w-full border rounded px-4 py-2">

        </div>

        <div>

            <label class="block mb-2">
                Work Category
            </label>

            <input type="text"
                   name="work_category"
                   value="{{ $contractor->work_category }}"
                   class="w-full border rounded px-4 py-2">

        </div>

    </div>

    <button type="submit"
            class="mt-6 bg-blue-600 text-white px-6 py-2 rounded">

        Update Contractor

    </button>

</form>

</div>

@endsection