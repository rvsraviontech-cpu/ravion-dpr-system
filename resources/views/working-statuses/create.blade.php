@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Create Working Status"
    subtitle="Add a working condition for labour attendance and PMO review."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('working-statuses.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="POST"
    action="{{ route('working-statuses.store') }}"
>
    @csrf

    <div class="space-y-6">

        @include('working-statuses._form')

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('working-statuses.index') }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Save Working Status
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection