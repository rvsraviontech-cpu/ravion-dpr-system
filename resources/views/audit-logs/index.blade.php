@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Audit Trail
</h1>

<form method="GET"
      action="{{ route('audit-logs.index') }}"
      class="bg-white p-4 rounded shadow mb-6">

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

        <div>
            <label class="block text-sm font-medium mb-1">
                From Date
            </label>

            <input type="date"
                   name="from_date"
                   value="{{ request('from_date') }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                To Date
            </label>

            <input type="date"
                   name="to_date"
                   value="{{ request('to_date') }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                User
            </label>

            <select name="user_id"
                    class="w-full border rounded px-3 py-2">

                <option value="">
                    All Users
                </option>

                @foreach($users as $user)

                    <option value="{{ $user->id }}"
                        {{ request('user_id') == $user->id ? 'selected' : '' }}>

                        {{ $user->name }}

                    </option>

                @endforeach

            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                Module
            </label>

            <select name="module"
                    class="w-full border rounded px-3 py-2">

                <option value="">
                    All Modules
                </option>

                @foreach($modules as $module)

                    <option value="{{ $module }}"
                        {{ request('module') == $module ? 'selected' : '' }}>

                        {{ $module }}

                    </option>

                @endforeach

            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                Action
            </label>

            <select name="action"
                    class="w-full border rounded px-3 py-2">

                <option value="">
                    All Actions
                </option>

                @foreach($actions as $action)

                    <option value="{{ $action }}"
                        {{ request('action') == $action ? 'selected' : '' }}>

                        {{ $action }}

                    </option>

                @endforeach

            </select>
        </div>

    </div>

    <div class="mt-4 flex gap-2">

        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded">

            Filter

        </button>

        <a href="{{ route('audit-logs.index') }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">

            Reset

        </a>

    </div>

</form>

<div class="bg-white rounded shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 text-left">Date</th>
                <th class="p-4 text-left">User</th>
                <th class="p-4 text-left">Module</th>
                <th class="p-4 text-left">Action</th>
                <th class="p-4 text-left">Description</th>
                <th class="p-4 text-left">Details</th>
            </tr>
        </thead>

        <tbody>

            @forelse($auditLogs as $log)

                <tr class="border-t">

                    <td class="p-4">
                        {{ $log->created_at->format('d-m-Y H:i') }}
                    </td>

                    <td class="p-4">
                        {{ optional($log->user)->name ?? 'System' }}
                    </td>

                    <td class="p-4">
                        {{ $log->module }}
                    </td>

                    <td class="p-4">
                        {{ $log->action }}
                    </td>

                    <td class="p-4">
                        {{ $log->description }}
                    </td>

                    <td class="p-4">
                        <a href="{{ route('audit-logs.show', $log) }}"
                           class="text-blue-600 hover:underline">
                            View
                        </a>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" class="p-4 text-center">
                        No Audit Logs Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

<div class="mt-4">
    {{ $auditLogs->links() }}
</div>

@endsection