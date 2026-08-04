@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Create Shift"
    subtitle="Add a new labour shift with timings and normal working hours."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('shifts.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="POST"
    action="{{ route('shifts.store') }}"
>
    @csrf

    <div class="space-y-6">

        @include('shifts._form')

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('shifts.index') }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Save Shift
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection