@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Edit Manpower Source"
    subtitle="Update manpower source details, contractor dependency, and availability."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('manpower-sources.show', $manpowerSource) }}"
            variant="secondary"
        >
            View Source
        </x-rds.button>

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
    action="{{ route('manpower-sources.update', $manpowerSource) }}"
>
    @csrf
    @method('PUT')

    <div class="space-y-6">

        @include('manpower-sources._form', [
            'manpowerSource' => $manpowerSource,
        ])

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('manpower-sources.show', $manpowerSource) }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Update Manpower Source
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection