@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Activity Mapping
</h1>

@if($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="bg-white p-6 rounded shadow">

    <form method="POST"
          action="{{ route('activity-mappings.update', $activityMapping) }}">

        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="block font-semibold mb-1">Division</label>
                <select name="activity_division_id" class="border p-2 rounded w-full">
                    <option value="">Select Division</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}"
                            {{ $activityMapping->activity_division_id == $division->id ? 'selected' : '' }}>
                            {{ $division->code }} - {{ $division->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">Activity</label>
                <select name="activity_id" class="border p-2 rounded w-full">
                    <option value="">Select Activity</option>
                    @foreach($activities as $activity)
                        <option value="{{ $activity->id }}"
                            {{ $activityMapping->activity_id == $activity->id ? 'selected' : '' }}>
                            {{ $activity->activity_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">RH Cost Code</label>
                <input type="text" name="rh_cost_code"
                       value="{{ old('rh_cost_code', $activityMapping->rh_cost_code) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block font-semibold mb-1">Activity Name</label>
                <input type="text" name="activity_name"
                       value="{{ old('activity_name', $activityMapping->activity_name) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block font-semibold mb-1">Unit</label>
                <input type="text" name="unit"
                       value="{{ old('unit', $activityMapping->unit) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block font-semibold mb-1">Odoo Type Code</label>
                <input type="text" name="odoo_type_code"
                       value="{{ old('odoo_type_code', $activityMapping->odoo_type_code) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block font-semibold mb-1">Odoo Type</label>
                <input type="text" name="odoo_type"
                       value="{{ old('odoo_type', $activityMapping->odoo_type) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block font-semibold mb-1">Material Group</label>
                <input type="text" name="material_group"
                       value="{{ old('material_group', $activityMapping->material_group) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block font-semibold mb-1">Contractor Type</label>
                <input type="text" name="contractor_type"
                       value="{{ old('contractor_type', $activityMapping->contractor_type) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block font-semibold mb-1">Inventory / Expense Bucket</label>
                <input type="text" name="inventory_expense_bucket"
                       value="{{ old('inventory_expense_bucket', $activityMapping->inventory_expense_bucket) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block font-semibold mb-1">Procurement Mode</label>
                <input type="text" name="procurement_mode"
                       value="{{ old('procurement_mode', $activityMapping->procurement_mode) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block font-semibold mb-1">Status</label>
                <select name="is_active" class="border p-2 rounded w-full">
                    <option value="1" {{ $activityMapping->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$activityMapping->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block font-semibold mb-1">Remarks</label>
                <textarea name="remarks"
                          class="border p-2 rounded w-full">{{ old('remarks', $activityMapping->remarks) }}</textarea>
            </div>

        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded">
                Update Mapping
            </button>

            <a href="{{ route('activity-mappings.index') }}"
               class="bg-gray-500 text-white px-4 py-2 rounded">
                Back
            </a>
        </div>

    </form>

</div>

@endsection