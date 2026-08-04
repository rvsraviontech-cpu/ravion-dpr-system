@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Edit Skill Category"
    subtitle="Update the skill classification and availability."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('skill-categories.show', $skillCategory) }}"
            variant="secondary"
        >
            View Skill Category
        </x-rds.button>

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
    action="{{ route('skill-categories.update', $skillCategory) }}"
>
    @csrf
    @method('PUT')

    <div class="space-y-6">

        @include('skill-categories._form', [
            'skillCategory' => $skillCategory,
        ])

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('skill-categories.show', $skillCategory) }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Update Skill Category
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection