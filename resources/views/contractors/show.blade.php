@extends('layouts.app')

@section('content')

<x-rds.resource.show
    title="Contractor Profile"
    description="View registered contractor details, services, location and compliance information."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Labour & Contractors'],
        ['label' => 'Contractors', 'url' => route('contractors.index')],
        ['label' => 'Contractor Profile'],
    ]"
>
    <x-slot name="actions">
        <x-rds.button variant="secondary" href="{{ route('contractors.index') }}">
            Back
        </x-rds.button>

        <x-rds.button href="{{ route('contractors.edit', $contractor) }}">
            Edit Contractor
        </x-rds.button>
    </x-slot>

    <x-rds.description-list>
        <x-rds.description-item label="Contractor Code">
            <x-rds.badge variant="info">
                {{ $contractor->contractor_code ?? 'CONT-' . str_pad($contractor->id, 5, '0', STR_PAD_LEFT) }}
            </x-rds.badge>
        </x-rds.description-item>

        <x-rds.description-item label="Contractor Name">
            {{ $contractor->contractor_name }}
        </x-rds.description-item>

        <x-rds.description-item label="Company Name">
            {{ $contractor->company_name ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Mobile">
            {{ $contractor->mobile ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Alternate Mobile">
            {{ $contractor->alternate_mobile ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Email">
            {{ $contractor->email ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Service Categories">
            <div class="flex flex-wrap gap-2">
                @forelse($contractor->serviceCategories as $service)
                    <x-rds.badge variant="default">
                        {{ $service->name }}
                    </x-rds.badge>
                @empty
                    -
                @endforelse
            </div>
        </x-rds.description-item>

        <x-rds.description-item label="City / Location">
            {{ $contractor->city ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Address">
            {{ $contractor->address ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="GST Number">
            {{ $contractor->gst_number ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="PAN Number">
            {{ $contractor->pan_number ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Aadhaar / ID Proof">
            {{ $contractor->aadhaar_number ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Experience">
            {{ $contractor->experience_years ? $contractor->experience_years . ' years' : '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Rating">
            {{ $contractor->rating ? $contractor->rating . ' / 5' : '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Preferred">
            {{ $contractor->is_preferred ? 'Yes' : 'No' }}
        </x-rds.description-item>

        <x-rds.description-item label="Status">
            @if($contractor->status === 'Active')
                <x-rds.badge variant="success">Active</x-rds.badge>
            @else
                <x-rds.badge variant="danger">Inactive</x-rds.badge>
            @endif
        </x-rds.description-item>

        <x-rds.description-item label="Remarks">
            {{ $contractor->remarks ?: '-' }}
        </x-rds.description-item>
    </x-rds.description-list>
</x-rds.resource.show>

@endsection