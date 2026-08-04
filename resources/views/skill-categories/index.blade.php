@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Skill Categories"
    subtitle="Manage labour skill classifications used in Labour Master and attendance."
>
    <x-slot:actions>
        @if(auth()->user()->hasPermission('labour_master_data.manage'))
            <x-rds.button
                href="{{ route('skill-categories.create') }}"
                variant="primary"
            >
                Add Skill Category
            </x-rds.button>
        @endif
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<x-rds.filter-bar
    method="GET"
    action="{{ route('skill-categories.index') }}"
>
    <x-rds.input
        name="search"
        label="Search"
        placeholder="Search code or skill category"
        :value="request('search')"
    />

    <x-rds.select
        name="status"
        label="Status"
    >
        <option value="">All Statuses</option>

        <option
            value="active"
            @selected(request('status') === 'active')
        >
            Active
        </option>

        <option
            value="inactive"
            @selected(request('status') === 'inactive')
        >
            Inactive
        </option>
    </x-rds.select>

    <x-slot:actions>
        <x-rds.button
            type="submit"
            variant="secondary"
        >
            Filter
        </x-rds.button>

        <x-rds.button
            href="{{ route('skill-categories.index') }}"
            variant="secondary"
        >
            Reset
        </x-rds.button>
    </x-slot:actions>
</x-rds.filter-bar>

<x-rds.card :padding="false">

    <div class="overflow-x-auto">

        <table class="min-w-full divide-y divide-gray-200">

            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        #
                    </th>

                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Code
                    </th>

                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Skill Category
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Record Type
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Sort Order
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Status
                    </th>

                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Actions
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">

                @forelse($skillCategories as $skillCategory)

                    <tr class="hover:bg-gray-50">

                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                            {{ $skillCategories->firstItem() + $loop->index }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-gray-900">
                            {{ $skillCategory->code }}
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">
                                {{ $skillCategory->name }}
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            @if($skillCategory->is_system)
                                <x-rds.badge variant="warning">
                                    System
                                </x-rds.badge>
                            @else
                                <x-rds.badge variant="secondary">
                                    Custom
                                </x-rds.badge>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-gray-700">
                            {{ $skillCategory->sort_order }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            @if($skillCategory->is_active)
                                <x-rds.badge variant="success">
                                    Active
                                </x-rds.badge>
                            @else
                                <x-rds.badge variant="danger">
                                    Inactive
                                </x-rds.badge>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-right">

                            <div class="flex items-center justify-end gap-2">

                                <x-rds.button
                                    href="{{ route('skill-categories.show', $skillCategory) }}"
                                    variant="secondary"
                                    size="sm"
                                >
                                    View
                                </x-rds.button>

                                @if(
                                    auth()->user()->hasPermission('labour_master_data.manage')
                                    && ! $skillCategory->is_system
                                )

                                    <x-rds.button
                                        href="{{ route('skill-categories.edit', $skillCategory) }}"
                                        variant="secondary"
                                        size="sm"
                                    >
                                        Edit
                                    </x-rds.button>

                                    <form
                                        method="POST"
                                        action="{{ route('skill-categories.toggle-status', $skillCategory) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <x-rds.button
                                            type="submit"
                                            variant="{{ $skillCategory->is_active ? 'danger' : 'success' }}"
                                            size="sm"
                                        >
                                            {{ $skillCategory->is_active ? 'Deactivate' : 'Activate' }}
                                        </x-rds.button>
                                    </form>

                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td
                            colspan="7"
                            class="px-4 py-10 text-center text-sm text-gray-500"
                        >
                            No skill categories found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($skillCategories->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $skillCategories->links() }}
        </div>
    @endif

</x-rds.card>

@endsection