@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Create Gender"
    subtitle="Add a new gender classification for Labour Master and attendance."
>
    <x-slot:actions>
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
    action="{{ route('genders.store') }}"
>
    @csrf

    <div class="space-y-6">

        @include('genders._form')

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('genders.index') }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Save Gender
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection