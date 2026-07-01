@extends('layouts.app')

@section('content')

<x-rds.resource.form
    title="Edit Service Category"
    description="Update contractor service category and work stage mapping."
    method="PUT"
    action="{{ route('contractor-service-categories.update', $contractorServiceCategory) }}"
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Labour & Contractors'],
        ['label' => 'Service Categories', 'url' => route('contractor-service-categories.index')],
        ['label' => 'Edit Service Category'],
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
            Update Service Category
        </x-rds.button>
    </x-slot>
</x-rds.resource.form>

@endsection