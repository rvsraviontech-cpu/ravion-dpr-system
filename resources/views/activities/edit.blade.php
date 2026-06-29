@extends('layouts.app')

@section('content')

<x-rds.resource.form
    title="Edit Activity"
    description="Update construction activity."
    method="PUT"
    action="{{ route('activities.update', $activity) }}"
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Masters'],
        ['label' => 'Activities', 'url' => route('activities.index')],
        ['label' => 'Edit Activity'],
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
            Update Activity
        </x-rds.button>

    </x-slot>

</x-rds.resource.form>

@endsection