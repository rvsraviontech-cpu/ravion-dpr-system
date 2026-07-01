@extends('layouts.app')

@section('content')

<x-rds.resource.form
    title="Edit Contractor"
    description="Update contractor profile, service categories and registration details."
    method="PUT"
    action="{{ route('contractors.update', $contractor) }}"
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Labour & Contractors'],
        ['label' => 'Contractors', 'url' => route('contractors.index')],
        ['label' => 'Edit Contractor'],
    ]"
>
    @if ($errors->any())
        <x-rds.alert type="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </x-rds.alert>
    @endif

    @include('contractors._form')

    <x-slot name="footer">
        <x-rds.button variant="secondary" href="{{ route('contractors.index') }}">Cancel</x-rds.button>
        <x-rds.button type="submit">Update Contractor</x-rds.button>
    </x-slot>
</x-rds.resource.form>

@endsection