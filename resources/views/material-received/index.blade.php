@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Material Received
        </h1>
        <p class="text-gray-500 mt-1">
            Track inward material entries by project, vendor, challan and verification status.
        </p>
    </div>

    <a href="{{ route('material-received.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
        + Add Material Received
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Qty Received Today</p>
        <p class="text-2xl font-bold text-blue-700">
            {{ $totalReceivedToday }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Draft Entries</p>
        <p class="text-2xl font-bold text-yellow-700">
            {{ $draftCount }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Submitted Entries</p>
        <p class="text-2xl font-bold text-orange-700">
            {{ $submittedCount }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Approved Entries</p>
        <p class="text-2xl font-bold text-green-700">
            {{ $approvedCount }}
        </p>
    </div>

</div>

<div class="bg-white p-4 rounded shadow mb-6">

    <form method="GET"
          action="{{ route('material-received.index') }}"
          class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            <label class="block text-sm font-semibold mb-1">Date</label>
            <input type="date"
                   name="received_date"
                   value="{{ request('received_date') }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Project</label>
            <select name="project_id"
                    class="border p-2 rounded w-full">
                <option value="">All Projects</option>

                @foreach($projects as $project)
                    <option value="{{ $project->id }}"
                        {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Status</label>
            <select name="status"
                    class="border p-2 rounded w-full">
                <option value="">All Status</option>
                <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                <option value="Submitted" {{ request('status') == 'Submitted' ? 'selected' : '' }}>Submitted</option>
                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Filter
            </button>

            <a href="{{ route('material-received.index') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Clear
            </a>
        </div>

    </form>

</div>

<div class="bg-white rounded shadow overflow-hidden">

    <div class="overflow-x-auto">

        <table class="min-w-full text-sm">

            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="p-3 text-left">#</th>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Project</th>
                    <th class="p-3 text-left">Material</th>
                    <th class="p-3 text-left">Specification</th>
                    <th class="p-3 text-left">Brand</th>
                    <th class="p-3 text-center">Qty</th>
                    <th class="p-3 text-left">Unit</th>
                    <th class="p-3 text-left">Vendor</th>
                    <th class="p-3 text-left">Challan</th>
                    <th class="p-3 text-left">Condition</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">

                @forelse($materialReceiveds as $index => $entry)

                    <tr class="hover:bg-gray-50">

                        <td class="p-3">
                            {{ $materialReceiveds->firstItem() + $index }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $entry->received_date }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $entry->project?->project_name ?? '-' }}
                        </td>

                        <td class="p-3 min-w-[180px]">
                            {{ $entry->material_name }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $entry->specification ?? '-' }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $entry->brand ?? '-' }}
                        </td>

                        <td class="p-3 text-center font-bold">
                            {{ $entry->quantity_received }}
                        </td>

                        <td class="p-3">
                            {{ $entry->unit ?? '-' }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $entry->vendor_name ?? '-' }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $entry->challan_number ?? '-' }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $entry->material_condition }}
                        </td>

                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs
                                {{ $entry->status === 'Draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $entry->status === 'Submitted' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $entry->status === 'Approved' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ $entry->status }}
                            </span>
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            <div class="flex gap-2">
                                <a href="{{ route('material-received.show', $entry) }}"
                                    class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded">
                                        View
                                </a>
                                <a href="{{ route('material-received.edit', $entry) }}"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                                    Edit
                                </a>

                                @if($entry->status === 'Draft')

                                    <form method="POST"
                                          action="{{ route('material-received.submit', $entry) }}">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">
                                            Submit
                                        </button>
                                    </form>

                                @elseif($entry->status === 'Submitted')

                                    @if(in_array(auth()->user()->role->name, ['Admin', 'PMO', 'DGM']))

                                        <form method="POST"
                                              action="{{ route('material-received.approve', $entry) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                                Approve
                                            </button>
                                        </form>

                                    @else

                                        <span class="text-orange-600 font-semibold">
                                            Pending Approval
                                        </span>

                                    @endif

                                @elseif($entry->status === 'Approved')

                                    <span class="text-green-700 font-semibold">
                                        Approved
                                    </span>

                                @endif

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="13"
                            class="p-6 text-center text-gray-500">
                            No material received entries found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

<div class="mt-4">
    {{ $materialReceiveds->links() }}
</div>

@endsection