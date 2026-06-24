@php
    $roleName = auth()->user()->role->name ?? '';
    $canViewCommercial = in_array($roleName, ['Admin', 'CEO', 'PMO', 'DGM']);
    $canViewOdoo = in_array($roleName, ['Admin', 'CEO']);
@endphp

@if($canViewCommercial)
    <div>
        <h2 class="text-sm font-bold text-gray-800 mb-3">Commercial & ERP Mapping</h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="{{ $label }}">Contract Value</label>
                <input type="number"
                       step="0.01"
                       name="contract_value"
                       class="{{ $input }}"
                       value="{{ old('contract_value', optional($project)->contract_value) }}">
            </div>

            @if($canViewOdoo)
                <div>
                    <label class="{{ $label }}">Odoo Analytic Account</label>
                    <input type="text"
                           name="odoo_analytic_account_code"
                           class="{{ $input }}"
                           value="{{ old('odoo_analytic_account_code', optional($project)->odoo_analytic_account_code) }}">
                </div>
            @else
                <input type="hidden"
                       name="odoo_analytic_account_code"
                       value="{{ old('odoo_analytic_account_code', optional($project)->odoo_analytic_account_code) }}">
            @endif
        </div>

        <p class="text-xs text-gray-500 mt-3">
            Commercial fields are restricted and must not be visible to Site Engineers.
        </p>
    </div>
@else
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md p-4 text-sm">
        Commercial information is restricted for your role.
    </div>

    <input type="hidden" name="contract_value" value="{{ old('contract_value', optional($project)->contract_value) }}">
    <input type="hidden" name="odoo_analytic_account_code" value="{{ old('odoo_analytic_account_code', optional($project)->odoo_analytic_account_code) }}">
@endif