@extends('layouts.app')

@section('content')

<x-rds.resource.form
    title="Create Service Category"
    description="Create a contractor service category and map it to a work stage."
    action="{{ route('contractor-service-categories.store') }}"
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Labour & Contractors'],
        ['label' => 'Service Categories', 'url' => route('contractor-service-categories.index')],
        ['label' => 'Create Service Category'],
    ]"
>
    @if ($errors->any())
        <x-rds.alert type="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </x-rds.alert>
    @endif

    @include('contractor-service-categories._form')

    <x-slot name="footer">
        <x-rds.button variant="secondary" href="{{ route('contractor-service-categories.index') }}">
            Cancel
        </x-rds.button>

        <x-rds.button type="submit">
            Save Service Category
        </x-rds.button>
    </x-slot>
</x-rds.resource.form>

@endsection