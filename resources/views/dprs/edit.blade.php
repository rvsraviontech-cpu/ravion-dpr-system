@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit DPR
</h1>

<div class="bg-white rounded shadow p-6">

<form action="/dprs/{{ $dpr->id }}"
      method="POST">

    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-6 mb-6">

        <div>

            <label class="block mb-2">
                DPR Date
            </label>

            <input type="date"
                   name="dpr_date"
                   value="{{ $dpr->dpr_date }}"
                   class="w-full border rounded px-4 py-2">

        </div>

        <div>

            <label class="block mb-2">
                Weather
            </label>

            <input type="text"
                   name="weather"
                   value="{{ $dpr->weather }}"
                   class="w-full border rounded px-4 py-2">

        </div>

    </div>

    <div class="mb-6">

        <label class="block mb-2">
            Remarks
        </label>

        <textarea name="remarks"
                  class="w-full border rounded px-4 py-2">{{ $dpr->remarks }}</textarea>

    </div>

    <button type="submit"
            class="bg-blue-600 text-white px-6 py-2 rounded">

        Update DPR

    </button>

</form>

</div>

@endsection