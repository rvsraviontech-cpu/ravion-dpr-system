@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Create Labour"
    subtitle="Register a new labour profile for attendance, DPR, and project allocation."
>
    <x-slot:actions>

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
    action="{{ route('labours.store') }}"
    enctype="multipart/form-data"
>
    @csrf

    <div class="space-y-6">

        @include('labours._form')

        <x-rds.card>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('labours.index') }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Save Labour
                </x-rds.button>

            </div>

        </x-rds.card>

    </div>

</form>

@endsection