@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Edit Designation Role"
    subtitle="Update the designation, trade mapping, skill category, and availability."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('designation-roles.show', $designationRole) }}"
            variant="secondary"
        >
            View Designation Role
        </x-rds.button>

        <x-rds.button
            href="{{ route('designation-roles.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="POST"
    action="{{ route('designation-roles.update', $designationRole) }}"
>
    @csrf
    @method('PUT')

    <div class="space-y-6">

        @include('designation-roles._form', [
            'designationRole' => $designationRole,
            'labourTypes' => $labourTypes,
            'skillCategories' => $skillCategories,
        ])

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('designation-roles.show', $designationRole) }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Update Designation Role
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection