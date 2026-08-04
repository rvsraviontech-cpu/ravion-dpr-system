@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Create Designation Role"
    subtitle="Add a designation and optionally map it to a Trade / Manpower Category and Skill Category."
>
    <x-slot:actions>
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
    action="{{ route('designation-roles.store') }}"
>
    @csrf

    <div class="space-y-6">

        @include('designation-roles._form')

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('designation-roles.index') }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Save Designation Role
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection