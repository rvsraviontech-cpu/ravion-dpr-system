@extends('layouts.app')

@section('content')

<div class="flex justify-between mb-6">

    <h1 class="text-3xl font-bold">
        Machinery / Tools
    </h1>

    <a href="/machinery-tools/create"
       class="bg-blue-600 text-white px-4 py-2 rounded">

        Add Machinery

    </a>

</div>

<div class="bg-white rounded shadow p-6">

    <table class="w-full">

        <thead class="bg-gray-100">

            <tr>

                <th class="p-4 text-left">
                    Machine
                </th>

                <th class="p-4 text-left">
                    Ownership
                </th>

                <th class="p-4 text-left">
                    Unit
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($machineries as $machine)

            <tr class="border-t">

                <td class="p-4">

                    {{ $machine->machine_name }}

                </td>

                <td class="p-4">

                    {{ $machine->ownership_type }}

                </td>

                <td class="p-4">

                    {{ $machine->unit }}

                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection