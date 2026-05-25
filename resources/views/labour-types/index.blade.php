@extends('layouts.app')

@section('content')

<div class="flex justify-between mb-6">

    <h1 class="text-3xl font-bold">
        Labour Types
    </h1>

    <a href="/labour-types/create"
       class="bg-blue-600 text-white px-4 py-2 rounded">

        Add Labour Type

    </a>

</div>

<div class="bg-white rounded shadow p-6">

    <table class="w-full">

        <thead class="bg-gray-100">

            <tr>

                <th class="p-4 text-left">
                    Labour Type
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($labourTypes as $type)

            <tr class="border-t">

                <td class="p-4">
                    {{ $type->labour_type_name }}
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection