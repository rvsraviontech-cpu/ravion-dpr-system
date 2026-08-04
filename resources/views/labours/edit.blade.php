@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Edit Labour"
    subtitle="Update labour profile details, classification, employment information, and permitted financial settings."
>
    <x-slot:actions>

        <x-rds.button
            href="{{ route('labours.show', $labour) }}"
            variant="secondary"
        >
            View Labour
        </x-rds.button>

        <x-rds.button
            href="{{ route('labours.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>

    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="POST"
    action="{{ route('labours.update', $labour) }}"
    enctype="multipart/form-data"
>
    @csrf
    @method('PUT')

    <div class="space-y-6">

        @include('labours._form', [
            'labour' => $labour,
            'canManageFinancial' => $canManageFinancial,
            'genders' => $genders,
            'manpowerSources' => $manpowerSources,
            'labourCategories' => $labourCategories,
            'labourTypes' => $labourTypes,
            'skillCategories' => $skillCategories,
            'designationRoles' => $designationRoles,
            'shifts' => $shifts,
            'contractors' => $contractors,
            'projects' => $projects,
        ])

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('labours.show', $labour) }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Update Labour
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>

</form>

@endsection