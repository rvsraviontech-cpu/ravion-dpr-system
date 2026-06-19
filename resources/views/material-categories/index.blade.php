@extends('layouts.app')

@section('content')

<div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Material Categories
        </h1>

        <p class="text-gray-500 mt-1">
            Manage standard material category masters.
        </p>
    </div>

    <a href="{{ route('material-categories.create') }}"
       class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded shadow">
        + Add Category
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    <div class="bg-white rounded shadow p-5">
        <div class="text-gray-500">Total Categories</div>
        <div class="text-3xl font-bold mt-2">{{ $categories->total() }}</div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <div class="text-gray-500">Active</div>
        <div class="text-3xl font-bold text-green-600 mt-2">
            {{ $categories->where('is_active', 1)->count() }}
        </div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <div class="text-gray-500">Inactive</div>
        <div class="text-3xl font-bold text-red-600 mt-2">
            {{ $categories->where('is_active', 0)->count() }}
        </div>
    </div>

</div>

<div class="bg-white rounded shadow overflow-hidden">

    <table class="min-w-full text-sm">

        <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
            <tr>
                <th class="p-3 text-left">#</th>
                <th class="p-3 text-left">Code</th>
                <th class="p-3 text-left">Category Name</th>
                <th class="p-3 text-center">Status</th>
                <th class="p-3 text-center">Actions</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">

            @forelse($categories as $index => $category)

                <tr class="hover:bg-gray-50">

                    <td class="p-3">
                        {{ $categories->firstItem() + $index }}
                    </td>

                    <td class="p-3">
                        {{ $category->category_code ?? '-' }}
                    </td>

                    <td class="p-3 font-semibold">
                        {{ $category->category_name }}

                        @if($category->remarks)
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $category->remarks }}
                            </div>
                        @endif
                    </td>

                    <td class="p-3 text-center">
                        @if($category->is_active)
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded text-xs">
                                Active
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded text-xs">
                                Inactive
                            </span>
                        @endif
                    </td>

                    <td class="p-3 text-center space-x-1">

                        <a href="{{ route('material-categories.edit', $category) }}"
                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded">
                            Edit
                        </a>

                        <form method="POST"
                              action="{{ route('material-categories.toggle-status', $category) }}"
                              class="inline">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    onclick="return confirm('Change material category status?')"
                                    class="px-3 py-2 rounded text-white
                                    {{ $category->is_active
                                        ? 'bg-red-600 hover:bg-red-700'
                                        : 'bg-green-600 hover:bg-green-700' }}">
                                {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="5"
                        class="p-6 text-center text-gray-500">
                        No material categories found.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

    <div class="p-4">
        {{ $categories->links() }}
    </div>

</div>

@endsection