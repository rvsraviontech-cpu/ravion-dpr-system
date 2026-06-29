@extends('layouts.app')

@section('content')

<x-rds.resource.form
    title="Create Activity"
    description="Add construction activity used in DPR, Planning and BOQ."
    action="{{ route('activities.store') }}"
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Masters'],
        ['label' => 'Activities', 'url' => route('activities.index')],
        ['label' => 'Create Activity'],
    ]"
>
    @if ($errors->any())
        <x-rds.alert type="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </x-rds.alert>
    @endif

    @include('activities._form')

    <x-slot name="footer">
        <x-rds.button
            variant="secondary"
            href="{{ route('activities.index') }}"
        >
            Cancel
        </x-rds.button>

        <x-rds.button type="submit">
            Save Activity
        </x-rds.button>
    </x-slot>
</x-rds.resource.form>

@endsection