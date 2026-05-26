@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Activity Mapping Import
</h1>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="bg-white p-6 rounded shadow">

    <form method="POST"
          action="{{ route('activity-mappings.import') }}"
          enctype="multipart/form-data">

        @csrf

        <label class="block font-semibold mb-2">
            Upload RH Cost-Code Excel File
        </label>

        <input type="file"
               name="file"
               accept=".xlsx,.xls"
               required
               class="border p-2 w-full mb-4">

        @error('file')
            <p class="text-red-600 mb-4">
                {{ $message }}
            </p>
        @enderror

        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded">
            Import Activity Mapping
        </button>

    </form>

</div>

@endsection