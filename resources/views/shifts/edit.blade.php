@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Edit Shift"
    subtitle="Update shift timings, normal working hours, overnight behaviour, and availability."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('shifts.show', $shift) }}"
            variant="secondary"
        >
            View Shift
        </x-rds.button>

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
    action="{{ route('shifts.update', $shift) }}"
>
    @csrf
    @method('PUT')

    <div class="space-y-6">

        @include('shifts._form', [
            'shift' => $shift,
        ])

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('shifts.show', $shift) }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Update Shift
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection