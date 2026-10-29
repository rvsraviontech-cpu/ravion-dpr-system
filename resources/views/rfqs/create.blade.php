@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8" x-data="rfqForm()" x-init="if (project) loadItems()">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Create Request for Quotation</h1>
            <p class="mt-1 text-sm text-gray-500">Choose approved requirement items and assign vendors to all materials together or individually.</p>
        </div>
        <a href="{{ route('rfqs.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700">← RFQ Register</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="mb-2 font-semibold">Please correct the following:</p>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('rfqs.store') }}" @submit="validateForm($event)">
        @csrf
        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-gray-900">RFQ Details</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <label class="block text-sm font-medium text-gray-700">Project <span class="text-red-600">*</span>
                    <select name="project_id" x-model="project" @change="loadItems()" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Select project</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->project_name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-sm font-medium text-gray-700">Submission Deadline <span class="text-red-600">*</span>
                    <input name="submission_deadline" type="datetime-local" value="{{ old('submission_deadline') }}" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                </label>
                <label class="block text-sm font-medium text-gray-700">Delivery Contact
                    <input name="delivery_contact_name" value="{{ old('delivery_contact_name') }}" maxlength="150" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                </label>
                <label class="block text-sm font-medium text-gray-700">Contact Phone
                    <input name="delivery_contact_phone" value="{{ old('delivery_contact_phone') }}" maxlength="30" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                </label>
                <label class="block text-sm font-medium text-gray-700 md:col-span-2">Delivery Address
                    <input name="delivery_address" value="{{ old('delivery_address') }}" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                </label>
                <label class="block text-sm font-medium text-gray-700 md:col-span-3">Instructions to Vendors
                    <textarea name="vendor_instructions" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">{{ old('vendor_instructions') }}</textarea>
                </label>
            </div>
        </section>

        <section class="my-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Vendor Assignment</h2>
                    <p class="mt-1 text-xs text-gray-500">Apply the same vendors to all selected materials, or assign different vendors to each material.</p>
                </div>
                <div class="inline-flex rounded-lg border border-gray-300 bg-gray-50 p-1 text-xs">
                    <button type="button" @click="vendorMode='common'" :class="vendorMode==='common' ? 'bg-[#10212F] text-white' : 'text-gray-700'" class="rounded-md px-3 py-2">Common vendors</button>
                    <button type="button" @click="vendorMode='individual'" :class="vendorMode==='individual' ? 'bg-[#10212F] text-white' : 'text-gray-700'" class="rounded-md px-3 py-2">Material-wise vendors</button>
                </div>
            </div>
            <div x-show="vendorMode==='common'" class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div>
                    <label for="common-vendor-search" class="mb-1 block text-xs font-semibold text-gray-600">Search and select vendors</label>
                    <input id="common-vendor-search" type="search" x-model="commonVendorSearch" placeholder="Search vendor name..." class="mb-2 w-full rounded-lg border-gray-300 text-sm">
                    <div class="grid max-h-36 grid-cols-1 gap-1 overflow-y-auto rounded-lg border border-gray-200 p-2 sm:grid-cols-2 lg:grid-cols-3">
                        <template x-for="vendor in filteredCommonVendors" :key="vendor.id">
                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-gray-50">
                                <input type="checkbox" :value="String(vendor.id)" x-model="commonVendorIds" class="rounded border-gray-300">
                                <span x-text="vendor.vendor_name"></span>
                            </label>
                        </template>
                        <p x-show="filteredCommonVendors.length===0" class="text-xs text-gray-500">No matching vendors.</p>
                    </div>
                </div>
                <button type="button" @click="applyCommonVendors()" class="rounded-lg bg-[#10212F] px-4 py-2.5 text-sm font-medium text-white">Apply to selected materials</button>
            </div>
            <p x-show="vendorMode==='common'" class="mt-2 text-xs text-gray-500">
                <span x-text="commonVendorIds.length"></span> vendor(s) chosen · <span x-text="selectedItems.length"></span> material(s) selected. Click Apply to assign them. You may then adjust individual rows if necessary.
            </p>
            <p x-show="vendorMode==='individual'" class="mt-3 text-xs text-gray-500">Use the vendor dropdown beside each material. Each vendor can be assigned to more than one material.</p>
        </section>

        <section class="my-5 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 px-4 py-3">
                <h2 class="text-sm font-semibold text-gray-900">Approved Material Requirement Items</h2>
                <div class="flex items-center gap-3"><label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" :checked="allSelected" @change="toggleAll($event.target.checked)" class="rounded border-gray-300"> Select all</label><span class="text-xs text-gray-500" x-text="message"></span></div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-3 py-3 text-center">Select</th>
                            <th class="px-3 py-3 text-left">Material / Variant</th>
                            <th class="px-3 py-3 text-right">Pending to Order</th>
                            <th class="px-3 py-3 text-left">RFQ Qty</th>
                            <th class="px-3 py-3 text-left">Vendors</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="item in items" :key="item.id">
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-3 py-3 text-center"><input type="checkbox" x-model="item.selected" class="rounded border-gray-300"></td>
                                <td class="px-3 py-3">
                                    <div class="font-medium text-gray-900" x-text="item.product_name || 'Material'"></div>
                                    <div class="text-xs text-gray-500" x-text="[item.variant_name, item.specification_text].filter(Boolean).join(' · ')"></div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums" x-text="formatQuantity(item.pending_quantity) + ' ' + (item.unit_code || '')"></td>
                                <td class="px-3 py-3">
                                    <input type="number" min="0.001" step="0.001" x-model="item.quantity" :max="item.pending_quantity" :disabled="!item.selected" class="w-28 rounded-lg border-gray-300 text-sm">
                                </td>
                                <td class="px-3 py-3">
                                    <details class="relative">
                                        <summary class="cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium" x-text="item.vendorIds.length + ' vendor(s) selected'"></summary>
                                        <div class="mt-2 min-w-60 rounded-lg border border-gray-200 bg-white p-2 shadow-sm">
                                            <input type="search" x-model="item.vendorSearch" placeholder="Search vendor..." class="mb-2 w-full rounded-lg border-gray-300 text-sm">
                                            <div class="max-h-44 overflow-y-auto">
                                                <template x-for="vendor in filteredVendors(item)" :key="vendor.id">
                                                    <label class="flex items-center gap-2 rounded px-2 py-1.5 hover:bg-gray-50">
                                                        <input type="checkbox" :value="String(vendor.id)" x-model="item.vendorIds" class="rounded border-gray-300">
                                                        <span x-text="vendor.vendor_name"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="items.length === 0"><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500" x-text="message"></td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <template x-for="(item, index) in selectedItems" :key="item.id">
            <div>
                <input type="hidden" :name="'items[' + index + '][requirement_item_id]'" :value="item.id">
                <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                <template x-for="vendorId in [...new Set(item.vendorIds.map(String))]" :key="vendorId">
                    <input type="hidden" :name="'items[' + index + '][vendor_ids][]'" :value="vendorId">
                </template>
            </div>
        </template>

        <div class="flex justify-end gap-3">
            <a href="{{ route('rfqs.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm">Cancel</a>
            <button type="submit" class="rounded-lg bg-[#10212F] px-5 py-2.5 text-sm font-medium text-white">Save RFQ Draft</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function rfqForm() {
    return {
        project: @json(old('project_id', '')),
        items: [],
        vendorMode: 'common',
        commonVendorIds: [],
        commonVendorSearch: '',
        vendors: @json($vendors),
        message: 'Select a project to load approved material requirements.',
        get selectedItems() { return this.items.filter(item => item.selected); },
        get allSelected() { return this.items.length > 0 && this.items.every(item => item.selected); },
        get filteredCommonVendors() {
            const q = this.commonVendorSearch.trim().toLowerCase();
            return this.vendors.filter(v => String(v.vendor_name || '').toLowerCase().includes(q));
        },
        formatQuantity(value) { return Number(value).toLocaleString('en-IN', {maximumFractionDigits: 3}); },
        toggleAll(checked) { this.items.forEach(item => item.selected = checked); },
        applyCommonVendors() {
            if (!this.selectedItems.length) { alert('Select at least one material first.'); return; }
            if (!this.commonVendorIds.length) { alert('Select at least one vendor.'); return; }
            const ids = [...new Set(this.commonVendorIds.map(String))];
            this.selectedItems.forEach(item => { item.vendorIds = [...ids]; });
        },
        filteredVendors(item) {
            const query = (item.vendorSearch || '').toLowerCase();
            return this.vendors.filter(v => String(v.vendor_name || '').toLowerCase().includes(query));
        },
        async loadItems() {
            this.items = [];
            this.commonVendorIds = [];
            if (!this.project) { this.message = 'Select a project to load approved material requirements.'; return; }
            this.message = 'Loading material requirements...';
            try {
                const url = @json(route('rfqs.requirement-items')) + '?project_id=' + encodeURIComponent(this.project);
                const response = await fetch(url, {headers: {'Accept': 'application/json'}});
                if (!response.ok) throw new Error('Unable to load requirements (HTTP ' + response.status + ').');
                const payload = await response.json();
                const rows = Array.isArray(payload) ? payload : (payload.data || []);
                this.items = rows.map(item => {
                    const pending = Number(item.pending_quantity ?? (Number(item.required_quantity) - Number(item.already_ordered_quantity || 0)));
                    return {...item, pending_quantity: Math.max(0, pending), selected: false,
                        quantity: String(Math.max(0, pending)), vendorIds: [], vendorSearch: ''};
                }).filter(item => item.pending_quantity > 0);
                this.message = this.items.length + ' eligible item(s)';
            } catch (error) { this.message = error.message; }
        },
        validateForm(event) {
            if (!this.selectedItems.length) { event.preventDefault(); alert('Select at least one material.'); return; }
            if (this.selectedItems.some(item => !item.vendorIds.length)) {
                event.preventDefault(); alert('Assign at least one vendor to each selected material.'); return;
            }
            if (this.selectedItems.some(item => !Number.isFinite(Number(item.quantity)) || Number(item.quantity) <= 0 || Number(item.quantity) > item.pending_quantity + 0.0005)) {
                event.preventDefault(); alert('RFQ quantities must be greater than zero and cannot exceed pending-to-order quantities.');
            }
        }
    };
}
</script>
@endpush
