@extends('layouts.app')

@section('content')

@php
    use Illuminate\Support\Str;
@endphp

<x-rds.resource.show
    title="Activity Division Details"
    description="View construction activity division details."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Execution Masters'],
        ['label' => 'Activity Divisions', 'url' => route('activity-divisions.index')],
        ['label' => 'Division Details'],
    ]"
>
    <x-slot name="actions">
        <x-rds.button
            variant="secondary"
            href="{{ route('activity-divisions.index') }}"
        >
            Back
        </x-rds.button>

        <x-rds.button
            href="{{ route('activity-divisions.edit', $activityDivision) }}"
        >
            Edit Division
        </x-rds.button>
    </x-slot>

    <x-rds.description-list>

        <x-rds.description-item label="Division Code">
            <x-rds.badge variant="info">
                {{ $activityDivision->code }}
            </x-rds.badge>
        </x-rds.description-item>

        <x-rds.description-item label="Division Name">
            {{ Str::title(strtolower($activityDivision->name)) }}
        </x-rds.description-item>

        <x-rds.description-item label="Sequence">
            {{ $activityDivision->sequence }}
        </x-rds.description-item>

        <x-rds.description-item label="Status">
            @if($activityDivision->is_active)
                <x-rds.badge variant="success">Active</x-rds.badge>
            @else
                <x-rds.badge variant="danger">Inactive</x-rds.badge>
            @endif
        </x-rds.description-item>

        <x-rds.description-item label="Remarks">
            {{ $activityDivision->remarks ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Created On">
            {{ optional($activityDivision->created_at)->format('d M Y, h:i A') }}
        </x-rds.description-item>

        <x-rds.description-item label="Last Modified">
            {{ optional($activityDivision->updated_at)->format('d M Y, h:i A') }}
        </x-rds.description-item>

    </x-rds.description-list>

</x-rds.resource.show>

@endsection