@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Create Skill Category"
    subtitle="Add a new skill classification for Labour Master and attendance."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('skill-categories.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="POST"
    action="{{ route('skill-categories.store') }}"
>
    @csrf

    <div class="space-y-6">

        @include('skill-categories._form')

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('skill-categories.index') }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Save Skill Category
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection