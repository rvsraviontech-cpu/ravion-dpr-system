@php
    $roleName = auth()->user()?->role?->name;
    $canViewCommercial = auth()->user()?->hasPermission('projects.manage')
        && in_array($roleName, ['Admin', 'CEO', 'PMO', 'DGM'], true);
    $canViewOdoo = auth()->user()?->hasPermission('projects.manage')
        && in_array($roleName, ['Admin', 'CEO'], true);
@endphp

@if($canViewCommercial)
    <div>
        <h2 class="text-sm font-bold text-gray-800 mb-3">Commercial & ERP Mapping</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="{{ $label }}">Contract Value</label>
                <input type="number" step="0.01" name="contract_value" class="{{ $input }}"
                       value="{{ old('contract_value', optional($project)->contract_value) }}">
            </div>
            @if($canViewOdoo)
                <div>
                    <label class="{{ $label }}">Odoo Analytic Account</label>
                    <input type="text" name="odoo_analytic_account_code" class="{{ $input }}"
                           value="{{ old('odoo_analytic_account_code', optional($project)->odoo_analytic_account_code) }}">
                </div>
            @endif
        </div>
    </div>
@else
    <p class="text-sm text-gray-500">Commercial information is restricted for your role.</p>
@endif
