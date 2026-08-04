@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Record Labour Attendance"
    subtitle="Create a daily project-wise labour attendance sheet."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('labour-attendances.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="POST"
    action="{{ route('labour-attendances.store') }}"
>
    @csrf

    <div class="space-y-6">

        @include('labour-attendances._form')

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('labour-attendances.index') }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Save Attendance
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection