@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

    <div class="flex justify-between items-center mb-6">

        <div>
            <h1 class="text-3xl font-bold">Work Stages</h1>
            <p class="text-gray-500">
                Construction execution stages used by Activities, DPR, Planning and Reports.
            </p>
        </div>

        <a href="{{ route('work-stages.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded">

            + Create Work Stage

        </a>

    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 p-3 rounded mb-5">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow">

        <div class="border-b p-5">

            <form>

                <div class="grid grid-cols-3 gap-4">

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search Work Stage..."
                        class="border rounded px-4 py-2">

                    <select
                        name="status"
                        class="border rounded px-4 py-2">

                        <option value="">All Status</option>

                        <option value="1"
                            {{ request('status')=='1'?'selected':'' }}>
                            Active
                        </option>

                        <option value="0"
                            {{ request('status')=='0'?'selected':'' }}>
                            Inactive
                        </option>

                    </select>

                    <button
                        class="bg-blue-600 text-white rounded">

                        Filter

                    </button>

                </div>

            </form>

        </div>

        <table class="w-full">

            <thead class="bg-gray-100">

                <tr>

                    <th class="p-4 text-left">Sequence</th>

                    <th class="p-4 text-left">Code</th>

                    <th class="p-4 text-left">Work Stage</th>

                    <th class="p-4 text-left">Status</th>

                    <th class="p-4 text-center">Action</th>

                </tr>

            </thead>

            <tbody>

            @forelse($workStages as $stage)

                <tr class="border-t hover:bg-gray-50">

                    <td class="p-4">
                        {{ $stage->sequence }}
                    </td>

                    <td class="p-4 font-mono">
                        {{ $stage->code }}
                    </td>

                    <td class="p-4 font-semibold">
                        {{ $stage->name }}
                    </td>

                    <td class="p-4">

                        @if($stage->is_active)

                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">
                                Active
                            </span>

                        @else

                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">
                                Inactive
                            </span>

                        @endif

                    </td>

                    <td class="p-4 text-center">

                        <a href="{{ route('work-stages.edit',$stage) }}"
                           class="bg-yellow-500 text-white px-3 py-1 rounded">

                            Edit

                        </a>

                        <form
                            action="{{ route('work-stages.destroy',$stage) }}"
                            method="POST"
                            class="inline">

                            @csrf
                            @method('DELETE')

                            <button
                                onclick="return confirm('Change Status?')"
                                class="bg-red-600 text-white px-3 py-1 rounded">

                                {{ $stage->is_active?'Deactivate':'Activate' }}

                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="5"
                        class="text-center text-gray-500 p-10">

                        No Work Stages Found

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection