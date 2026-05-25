@extends('layouts.app')

@section('content')

<div class="flex justify-between mb-6">

    <h1 class="text-3xl font-bold">
        Vendors
    </h1>

    <a href="/vendors/create"
       class="bg-blue-600 text-white px-4 py-2 rounded">

        Add Vendor

    </a>

</div>

<div class="bg-white rounded shadow p-6">

    <table class="w-full">

        <thead class="bg-gray-100">

            <tr>

                <th class="p-4 text-left">
                    Vendor
                </th>

                <th class="p-4 text-left">
                    Contact
                </th>

                <th class="p-4 text-left">
                    Mobile
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($vendors as $vendor)

            <tr class="border-t">

                <td class="p-4">

                    {{ $vendor->vendor_name }}

                </td>

                <td class="p-4">

                    {{ $vendor->contact_person }}

                </td>

                <td class="p-4">

                    {{ $vendor->mobile }}

                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection