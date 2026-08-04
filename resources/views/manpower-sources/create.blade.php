@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Create Manpower Source"
    subtitle="Add a new manpower source for Labour Master and attendance management."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('manpower-sources.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="POST"
    action="{{ route('manpower-sources.store') }}"
>
    @csrf

    <div class="space-y-6">

        @include('manpower-sources._form')

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('manpower-sources.index') }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Save Manpower Source
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection