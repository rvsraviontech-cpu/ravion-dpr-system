@extends('layouts.app')

@section('content')
<x-rds.page-header title="Labour Groups" subtitle="Manage operational labour groupings used in attendance, registers and wage sheets." />
<x-rds.alert />

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <x-rds.card>
        <form method="POST" action="{{ route('labour-groups.store') }}" class="space-y-4">
            @csrf
            <x-rds.input name="code" label="Code" required value="{{ old('code') }}" placeholder="HINDI-MASON" />
            <x-rds.input name="name" label="Group Name" required value="{{ old('name') }}" placeholder="Hindi Mason" />
            <x-rds.input name="sort_order" label="Sort Order" type="number" min="0" value="{{ old('sort_order', 0) }}" />
            <x-rds.textarea name="remarks" label="Remarks" rows="2" value="{{ old('remarks') }}" />
            <x-rds.button type="submit" variant="primary">Add Labour Group</x-rds.button>
        </form>
    </x-rds.card>

    <div class="xl:col-span-2">
        <x-rds.card :padding="false">
            <div class="max-h-[390px] overflow-auto">
                <table class="w-full min-w-[760px] table-fixed">
                    <thead class="sticky top-0 z-10 bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Code</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Labour Group</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-600">Labours</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-600">Order</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($labourGroups as $group)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $labourGroups->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $group->code }}</td>
                                <td class="px-4 py-3 text-sm font-semibold">{{ $group->name }}</td>
                                <td class="px-4 py-3 text-center text-sm">{{ $group->labours_count }}</td>
                                <td class="px-4 py-3 text-center text-sm">{{ $group->sort_order }}</td>
                                <td class="px-4 py-3 text-center">
                                    <form method="POST" action="{{ route('labour-groups.toggle-status', $group) }}">
                                        @csrf @method('PATCH')
                                        <x-rds.button type="submit" size="sm" variant="{{ $group->is_active ? 'success' : 'secondary' }}">
                                            {{ $group->is_active ? 'Active' : 'Inactive' }}
                                        </x-rds.button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No Labour Groups found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($labourGroups->hasPages())<div class="border-t px-4 py-3">{{ $labourGroups->links() }}</div>@endif
        </x-rds.card>
    </div>
</div>
@endsection
