@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold text-gray-900">
            Product Selector Test
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Test the shared Ravion Product Search component before transaction integration.
        </p>

        <div class="mt-6">
            <x-rds.product-selector
                name="material_type_id"
                label="Product"
                help="Search using Product name, alias, or Product code."
            />
        </div>
    </div>
</div>
@endsection