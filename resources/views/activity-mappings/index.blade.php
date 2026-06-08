@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Activity Mapping Master
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

<a href="{{ route('activity-mappings.create') }}"
   class="bg-blue-600 text-white px-4 py-2 rounded inline-block mb-4">
    Add New Mapping
</a>

<div class="bg-white p-6 rounded shadow mb-6">

    <form method="GET"
          action="{{ route('activity-mappings.index') }}"
          class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search RH code / activity / unit"
               class="border p-2 rounded">

        <select name="division_id"
                class="border p-2 rounded">
            <option value="">All Divisions</option>

            @foreach($divisions as $division)
                <option value="{{ $division->id }}"
                    {{ request('division_id') == $division->id ? 'selected' : '' }}>
                    {{ $division->code }} - {{ $division->name }}
                </option>
            @endforeach
        </select>

        <select name="status"
                class="border p-2 rounded">
            <option value="">All Status</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>
                Active
            </option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>
                Inactive
            </option>
        </select>

        <div class="flex gap-2">

    <button type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded">
        Filter
    </button>

    <a href="{{ route('activity-mappings.index') }}"
       class="bg-gray-500 text-white px-4 py-2 rounded">
        Clear
    </a>

</div>

    </form>

</div>

<div class="bg-white p-6 rounded shadow mb-6">

    <form method="POST"
          action="{{ route('activity-mappings.import') }}"
          enctype="multipart/form-data"
          class="flex flex-col md:flex-row gap-4 items-start md:items-center">

        @csrf

        <input type="file"
               name="file"
               accept=".xlsx,.xls"
               required
               class="border p-2 rounded">

        <button type="submit"
                class="bg-green-600 text-white px-4 py-2 rounded">
            Import Excel
        </button>

    </form>

</div>

<div class="bg-white rounded shadow overflow-x-auto">

    <table class="w-full text-sm">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-3 text-left">#</th>    
                <th class="p-3 text-left">Division</th>
                <th class="p-3 text-left">RH Code</th>
                <th class="p-3 text-left">Activity</th>
                <th class="p-3 text-left">Unit</th>
                <th class="p-3 text-left">Odoo Type</th>
                <th class="p-3 text-left">Status</th>
                <th class="p-3 text-left">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($activityMappings as $index => $mapping)
                <tr class="border-t">
                    <td class="p-3">
                        {{ $activityMappings->firstItem() + $index }}
                    </td>
                    <td class="p-3">
                        {{ $mapping->division?->code }}<br>
                        <span class="text-gray-500">
                            {{ $mapping->division?->name }}
                        </span>
                    </td>

                    <td class="p-3 font-semibold">
                        {{ $mapping->rh_cost_code }}
                    </td>

                    <td class="p-3">
                        {{ $mapping->activity_name }}
                    </td>

                    <td class="p-3">
                        {{ $mapping->unit }}
                    </td>

                    <td class="p-3">
                        {{ $mapping->odoo_type }}
                    </td>

                    <td class="p-3">
                        @if($mapping->is_active)
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
                                Active
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded">
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="p-3">
    <a href="{{ route('activity-mappings.edit', $mapping) }}"
       class="bg-yellow-500 text-white px-3 py-1 rounded">
        Edit
    </a>
</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8"
                        class="p-6 text-center text-gray-500">
                        No activity mappings found.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>

</div>

<div class="mt-4">
    {{ $activityMappings->links() }}
</div>

@endsection