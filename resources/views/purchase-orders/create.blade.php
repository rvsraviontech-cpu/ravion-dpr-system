@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8"
     x-data="purchaseOrderCreate()"
     x-init="init()">

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Create Purchase Order</h1>
            <p class="mt-1 text-sm text-gray-500">
                Build a Draft PO from approved Material Requirements.
            </p>
        </div>
        <a href="{{ route('purchase-orders.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Back to Purchase Orders
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <div class="font-semibold">Please correct the following:</div>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('purchase-orders.store') }}" @submit="beforeSubmit">
        @csrf

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-4 py-3">
                <h2 class="text-sm font-semibold text-gray-900">PO Details</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 p-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700">Project <span class="text-red-500">*</span></label>
                    <select name="project_id" x-model="projectId" @change="projectChanged" required
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">Select Project</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" data-location="{{ $project->location }}">
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700">Vendor <span class="text-red-500">*</span></label>
                    <select name="vendor_id" x-model="vendorId" @change="vendorChanged" required
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">Select Vendor</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}"
                                    data-payment-terms="{{ $vendor->payment_terms }}"
                                    data-credit-days="{{ $vendor->credit_days }}">
                                {{ $vendor->vendor_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700">PO Date <span class="text-red-500">*</span></label>
                    <input type="date" name="po_date" value="{{ old('po_date', now()->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700">Expected Delivery</label>
                    <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-gray-700">Delivery Address</label>
                    <input type="text" name="delivery_address" x-model="deliveryAddress"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700">Vendor Reference / Quotation</label>
                    <input type="text" name="vendor_reference" value="{{ old('vendor_reference') }}"
                           placeholder="Quotation no. / reference"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700">Payment Terms</label>
                    <input type="text" name="payment_terms" x-model="paymentTerms"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                </div>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-3 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Approved Material Requirements</h2>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Review requirement by requirement so no requested item is missed.
                    </p>
                </div>

                <div class="flex w-full gap-2 xl:w-auto">
                    <input type="text" x-model="search" @keydown.enter.prevent="loadRequirements"
                           placeholder="Search Product / Specification..."
                           class="min-w-0 flex-1 rounded-lg border-gray-300 text-sm xl:w-72 focus:border-slate-500 focus:ring-slate-500">
                    <button type="button" @click="loadRequirements"
                            class="rounded-lg bg-[#10212F] px-4 py-2 text-sm font-medium text-white">
                        Search
                    </button>
                </div>
            </div>

            <div x-show="projectId" class="grid grid-cols-2 gap-px border-b border-gray-200 bg-gray-200 md:grid-cols-4">
                <div class="bg-white px-4 py-3">
                    <div class="text-xs text-gray-500">Approved MRs</div>
                    <div class="mt-1 text-lg font-semibold text-gray-900" x-text="mrGroups.length"></div>
                </div>
                <div class="bg-white px-4 py-3">
                    <div class="text-xs text-gray-500">Pending Items</div>
                    <div class="mt-1 text-lg font-semibold text-gray-900" x-text="requirementRows.length"></div>
                </div>
                <div class="bg-white px-4 py-3">
                    <div class="text-xs text-gray-500">Selected Items</div>
                    <div class="mt-1 text-lg font-semibold text-gray-900" x-text="items.length"></div>
                </div>
                <div class="bg-white px-4 py-3">
                    <div class="text-xs text-gray-500">Fully Added MRs</div>
                    <div class="mt-1 text-lg font-semibold text-gray-900" x-text="fullyAddedMrCount()"></div>
                </div>
            </div>

            <div x-show="!projectId" class="p-6 text-center text-sm text-gray-500">
                Select a Project first.
            </div>

            <div x-show="projectId" class="p-3">
                <template x-if="loading">
                    <div class="py-8 text-center text-sm text-gray-500">Loading approved Material Requirements...</div>
                </template>

                <template x-if="!loading && mrGroups.length === 0">
                    <div class="py-8 text-center text-sm text-gray-500">
                        No approved Material Requirements pending to order for this Project.
                    </div>
                </template>

                <div class="space-y-2">
                    <template x-for="group in mrGroups" :key="group.id">
                        <div class="overflow-hidden rounded-lg border border-gray-200">
                            <div class="flex flex-col gap-3 bg-gray-50 px-4 py-3 lg:flex-row lg:items-center lg:justify-between">
                                <button type="button" @click="toggleGroup(group)"
                                        class="flex min-w-0 flex-1 items-center gap-3 text-left">
                                    <span class="w-5 text-gray-400" x-text="group.open ? '▼' : '▶'"></span>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-semibold text-gray-900" x-text="group.requirement_no"></span>
                                            <span class="rounded-full bg-white px-2 py-0.5 text-xs text-gray-600"
                                                  x-text="`${group.rows.length} pending item${group.rows.length === 1 ? '' : 's'}`"></span>
                                            <span x-show="isGroupFullyAdded(group)"
                                                  class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                                                ✓ All pending items added
                                            </span>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Required Date: <span x-text="group.required_date || '-'"></span>
                                            · Added <span x-text="groupSelectedCount(group)"></span>/<span x-text="group.rows.length"></span>
                                        </div>
                                    </div>
                                </button>

                                <div class="flex gap-2">
                                    <button type="button" @click="toggleGroup(group)"
                                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700">
                                        <span x-text="group.open ? 'Hide Items' : 'View Items'"></span>
                                    </button>
                                    <button type="button" @click="addAllPending(group)"
                                            :disabled="isGroupFullyAdded(group)"
                                            class="rounded-md bg-[#10212F] px-3 py-1.5 text-xs font-medium text-white disabled:opacity-40">
                                        + Add All Pending
                                    </button>
                                </div>
                            </div>

                            <div x-show="group.open" x-collapse class="overflow-x-auto">
                                <table class="min-w-[950px] w-full text-sm">
                                    <thead class="bg-white text-xs uppercase tracking-wide text-gray-500">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Product</th>
                                            <th class="px-3 py-2 text-left">Specification / Size</th>
                                            <th class="px-3 py-2 text-right">Required</th>
                                            <th class="px-3 py-2 text-right">Ordered</th>
                                            <th class="px-3 py-2 text-right">Pending</th>
                                            <th class="px-3 py-2 text-left">Unit</th>
                                            <th class="px-3 py-2 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="row in group.rows" :key="row.id">
                                            <tr>
                                                <td class="px-3 py-2">
                                                    <div class="font-medium text-gray-900" x-text="row.product_name"></div>
                                                    <div class="text-xs text-gray-400" x-text="row.catalogue_code || row.product_code || ''"></div>
                                                </td>
                                                <td class="px-3 py-2" x-text="row.specification_text || '-'"></td>
                                                <td class="px-3 py-2 text-right" x-text="qty(row.required_quantity)"></td>
                                                <td class="px-3 py-2 text-right" x-text="qty(row.already_ordered_quantity)"></td>
                                                <td class="px-3 py-2 text-right font-semibold" x-text="qty(row.pending_quantity)"></td>
                                                <td class="px-3 py-2" x-text="row.unit_code || row.unit_name || '-'"></td>
                                                <td class="px-3 py-2 text-right">
                                                    <button type="button" @click="addItem(row)"
                                                            :disabled="isSelected(row.id)"
                                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 disabled:opacity-40">
                                                        <span x-text="isSelected(row.id) ? 'Added' : '+ Add'"></span>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-4 py-3">
                <h2 class="text-sm font-semibold text-gray-900">Purchase Order Items</h2>
                <p class="mt-0.5 text-xs text-gray-500">
                    Only Product-relevant Brands are shown. If no Brand is mapped, leave it as Not Specified.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1450px] w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-3 py-2 text-left">#</th>
                            <th class="px-3 py-2 text-left">MR</th>
                            <th class="px-3 py-2 text-left">Product</th>
                            <th class="px-3 py-2 text-left">Specification / Size</th>
                            <th class="px-3 py-2 text-left">Brand</th>
                            <th class="px-3 py-2 text-right">Qty</th>
                            <th class="px-3 py-2 text-left">Unit</th>
                            <th class="px-3 py-2 text-right">Rate</th>
                            <th class="px-3 py-2 text-right">Disc %</th>
                            <th class="px-3 py-2 text-right">Tax %</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-if="items.length === 0">
                            <tr>
                                <td colspan="12" class="px-4 py-8 text-center text-gray-500">
                                    Add requirement items above.
                                </td>
                            </tr>
                        </template>

                        <template x-for="(item, index) in items" :key="item.material_requirement_item_id">
                            <tr>
                                <td class="px-3 py-2" x-text="index + 1"></td>
                                <td class="px-3 py-2 font-medium text-gray-700" x-text="item.requirement_no"></td>
                                <td class="px-3 py-2">
                                    <input type="hidden"
                                           :name="`items[${index}][material_requirement_item_id]`"
                                           :value="item.material_requirement_item_id">
                                    <div class="font-medium text-gray-900" x-text="item.product_name"></div>
                                </td>
                                <td class="px-3 py-2" x-text="item.specification_text || '-'"></td>
                                <td class="px-3 py-2">
                                    <select :name="`items[${index}][brand_master_id]`"
                                            x-model="item.brand_master_id"
                                            class="w-48 rounded-md border-gray-300 text-xs">
                                        <option value="">No Brand / Not Specified</option>
                                        <template x-for="brand in item.brands" :key="brand.id">
                                            <option :value="String(brand.id)" x-text="brand.name"></option>
                                        </template>
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" min="0.001" step="0.001"
                                           :max="item.pending_quantity"
                                           :name="`items[${index}][ordered_quantity]`"
                                           x-model.number="item.ordered_quantity"
                                           required
                                           class="w-24 rounded-md border-gray-300 text-right text-xs">
                                </td>
                                <td class="px-3 py-2" x-text="item.unit_code || item.unit_name || '-'"></td>
                                <td class="px-3 py-2">
                                    <input type="number" min="0" step="0.01"
                                           :name="`items[${index}][rate]`"
                                           x-model.number="item.rate"
                                           class="w-28 rounded-md border-gray-300 text-right text-xs">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" min="0" max="100" step="0.01"
                                           :name="`items[${index}][discount_percent]`"
                                           x-model.number="item.discount_percent"
                                           class="w-20 rounded-md border-gray-300 text-right text-xs">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" min="0" max="100" step="0.01"
                                           :name="`items[${index}][tax_percent]`"
                                           x-model.number="item.tax_percent"
                                           class="w-20 rounded-md border-gray-300 text-right text-xs">
                                </td>
                                <td class="px-3 py-2 text-right font-semibold">
                                    ₹ <span x-text="money(lineAmount(item))"></span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" @click="removeItem(index)"
                                            class="text-xs font-medium text-red-600">Remove</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 gap-4 border-t border-gray-200 p-4 lg:grid-cols-[1fr_360px]">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Delivery Terms</label>
                        <input type="text" name="delivery_terms" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Freight / Transport</label>
                        <input type="text" name="freight_terms" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Internal Remarks</label>
                        <input type="text" name="internal_remarks" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>₹ <span x-text="money(subtotal())"></span></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Discount</span><span>₹ <span x-text="money(discountTotal())"></span></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Tax</span><span>₹ <span x-text="money(taxTotal())"></span></span></div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-500">Other Charges</span>
                            <input type="number" name="other_charges" min="0" step="0.01"
                                   x-model.number="otherCharges"
                                   class="w-28 rounded-md border-gray-300 text-right text-xs">
                        </div>
                        <div class="flex justify-between border-t border-gray-300 pt-2 text-base font-semibold">
                            <span>Grand Total</span><span>₹ <span x-text="money(grandTotal())"></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex justify-end gap-3">
            <a href="{{ route('purchase-orders.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700">Cancel</a>
            <button type="submit" :disabled="items.length === 0 || submitting"
                    class="rounded-lg bg-[#10212F] px-5 py-2 text-sm font-medium text-white disabled:opacity-40">
                <span x-text="submitting ? 'Saving...' : 'Save Draft Purchase Order'"></span>
            </button>
        </div>
    </form>
</div>

<script>
function purchaseOrderCreate() {
    return {
        projectId: @json((string) old('project_id', $selectedProjectId ?? '')),
        vendorId: @json((string) old('vendor_id', '')),
        deliveryAddress: @json(old('delivery_address', '')),
        paymentTerms: @json(old('payment_terms', '')),
        search: '',
        loading: false,
        submitting: false,
        requirementRows: [],
        items: [],
        otherCharges: Number(@json(old('other_charges', 0))) || 0,

        async init() {
            if (this.projectId) {
                this.setProjectAddress();
                await this.loadRequirements();
            }
        },

        get mrGroups() {
            const map = new Map();

            for (const row of this.requirementRows) {
                if (!map.has(row.material_requirement_id)) {
                    map.set(row.material_requirement_id, {
                        id: row.material_requirement_id,
                        requirement_no: row.requirement_no,
                        required_date: row.required_date,
                        rows: [],
                        open: false,
                    });
                }

                map.get(row.material_requirement_id).rows.push(row);
            }

            return Array.from(map.values());
        },

        projectChanged() {
            this.items = [];
            this.requirementRows = [];
            this.setProjectAddress();

            if (this.projectId) {
                this.loadRequirements();
            }
        },

        setProjectAddress() {
            const select = document.querySelector('select[name="project_id"]');
            const option = select?.options[select.selectedIndex];

            if (option) {
                this.deliveryAddress = option.dataset.location || '';
            }
        },

        vendorChanged() {
            const select = document.querySelector('select[name="vendor_id"]');
            const option = select?.options[select.selectedIndex];

            if (option) {
                const explicit = option.dataset.paymentTerms || '';
                const creditDays = option.dataset.creditDays || '';
                this.paymentTerms = explicit || (creditDays ? `${creditDays} days` : '');
            }
        },

        async loadRequirements() {
            if (!this.projectId) {
                this.requirementRows = [];
                return;
            }

            this.loading = true;

            try {
                const params = new URLSearchParams({
                    project_id: this.projectId,
                    limit: '200',
                });

                if (this.search.trim()) {
                    params.set('search', this.search.trim());
                }

                const response = await fetch(
                    `{{ route('purchase-orders.approved-requirement-items') }}?${params.toString()}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    }
                );

                if (!response.ok) {
                    throw new Error('Unable to load approved Material Requirements.');
                }

                const json = await response.json();
                this.requirementRows = json.data || [];
            } catch (error) {
                console.error(error);
                this.requirementRows = [];
                alert(error.message || 'Unable to load requirement items.');
            } finally {
                this.loading = false;
            }
        },

        toggleGroup(group) {
            group.open = !group.open;
        },

        addAllPending(group) {
            for (const row of group.rows) {
                this.addItem(row);
            }
            group.open = true;
        },

        groupSelectedCount(group) {
            return group.rows.filter(row => this.isSelected(row.id)).length;
        },

        isGroupFullyAdded(group) {
            return group.rows.length > 0 && this.groupSelectedCount(group) === group.rows.length;
        },

        fullyAddedMrCount() {
            return this.mrGroups.filter(group => this.isGroupFullyAdded(group)).length;
        },

        addItem(row) {
            if (this.isSelected(row.id)) {
                return;
            }

            this.items.push({
                material_requirement_item_id: row.id,
                requirement_no: row.requirement_no,
                product_name: row.product_name,
                specification_text: row.specification_text,
                brand_master_id: row.brand_master_id ? String(row.brand_master_id) : '',
                brands: Array.isArray(row.brands) ? row.brands : [],
                pending_quantity: Number(row.pending_quantity) || 0,
                ordered_quantity: Number(row.pending_quantity) || 0,
                unit_code: row.unit_code || '',
                unit_name: row.unit_name || '',
                rate: 0,
                discount_percent: 0,
                tax_percent: 0,
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        isSelected(id) {
            return this.items.some(item =>
                Number(item.material_requirement_item_id) === Number(id)
            );
        },

        qty(value) {
            return Number(value || 0).toFixed(3).replace(/\.?0+$/, '');
        },

        money(value) {
            return Number(value || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        gross(item) {
            return (Number(item.ordered_quantity) || 0) * (Number(item.rate) || 0);
        },

        discount(item) {
            return this.gross(item) * ((Number(item.discount_percent) || 0) / 100);
        },

        taxable(item) {
            return this.gross(item) - this.discount(item);
        },

        tax(item) {
            return this.taxable(item) * ((Number(item.tax_percent) || 0) / 100);
        },

        lineAmount(item) {
            return this.taxable(item) + this.tax(item);
        },

        subtotal() {
            return this.items.reduce((sum, item) => sum + this.gross(item), 0);
        },

        discountTotal() {
            return this.items.reduce((sum, item) => sum + this.discount(item), 0);
        },

        taxTotal() {
            return this.items.reduce((sum, item) => sum + this.tax(item), 0);
        },

        grandTotal() {
            return this.items.reduce((sum, item) => sum + this.lineAmount(item), 0)
                + (Number(this.otherCharges) || 0);
        },

        beforeSubmit(event) {
            if (!this.items.length) {
                event.preventDefault();
                alert('Add at least one approved Material Requirement item.');
                return;
            }

            for (const item of this.items) {
                const qty = Number(item.ordered_quantity) || 0;

                if (qty <= 0 || qty > Number(item.pending_quantity)) {
                    event.preventDefault();
                    alert(`Invalid quantity for ${item.product_name}.`);
                    return;
                }
            }

            this.submitting = true;
        },
    };
}
</script>
@endsection
