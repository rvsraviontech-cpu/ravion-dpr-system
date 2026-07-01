@extends('layouts.app')

@section('content')

<x-rds.resource.show
    title="Service Category Details"
    description="View contractor service category and mapped work division."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Labour & Contractors'],
        ['label' => 'Service Categories', 'url' => route('contractor-service-categories.index')],
        ['label' => 'Service Category Details'],
    ]"
>

    <x-slot name="actions">

        <x-rds.button
            variant="secondary"
            href="{{ route('contractor-service-categories.index') }}">
            Back
        </x-rds.button>

        <x-rds.button
            href="{{ route('contractor-service-categories.edit', $contractorServiceCategory) }}">
            Edit Service Category
        </x-rds.button>

    </x-slot>

    <x-rds.description-list>

        <x-rds.description-item label="Code">
            <x-rds.badge variant="info">
                {{ $contractorServiceCategory->code ?: '-' }}
            </x-rds.badge>
        </x-rds.description-item>

        <x-rds.description-item label="Service Category">
            {{ $contractorServiceCategory->name }}
        </x-rds.description-item>

        <x-rds.description-item label="Work Division">

            @if($contractorServiceCategory->division)

                <x-rds.badge variant="primary">
                    {{ $contractorServiceCategory->division->code }}
                </x-rds.badge>

                <div class="mt-1 text-gray-700">
                    {{ $contractorServiceCategory->division->name }}
                </div>

            @else

                -

            @endif

        </x-rds.description-item>

        <x-rds.description-item label="Status">

            @if($contractorServiceCategory->is_active)

                <x-rds.badge variant="success">
                    Active
                </x-rds.badge>

            @else

                <x-rds.badge variant="danger">
                    Inactive
                </x-rds.badge>

            @endif

        </x-rds.description-item>

        <x-rds.description-item label="Remarks">
            {{ $contractorServiceCategory->remarks ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Created On">
            {{ optional($contractorServiceCategory->created_at)->format('d M Y, h:i A') }}
        </x-rds.description-item>

        <x-rds.description-item label="Last Modified">
            {{ optional($contractorServiceCategory->updated_at)->format('d M Y, h:i A') }}
        </x-rds.description-item>

    </x-rds.description-list>

</x-rds.resource.show>

@endsection