@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Edit Gender"
    subtitle="Update the gender classification and availability."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('genders.show', $gender) }}"
            variant="secondary"
        >
            View Gender
        </x-rds.button>

        <x-rds.button
            href="{{ route('genders.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="POST"
    action="{{ route('genders.update', $gender) }}"
>
    @csrf
    @method('PUT')

    <div class="space-y-6">

        @include('genders._form', [
            'gender' => $gender,
        ])

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('genders.show', $gender) }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Update Gender
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection