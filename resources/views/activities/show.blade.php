@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Str;
@endphp

<x-rds.resource.show
    title="Activity Details"
    description="View construction activity details used across DPR, Planning and BOQ."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Execution Masters'],
        ['label' => 'Activities', 'url' => route('activities.index')],
        ['label' => 'Activity Details'],
    ]"
>
    <x-slot name="actions">
        <x-rds.button
            variant="secondary"
            href="{{ route('activities.index', [
                'activity_division_id' => $activity->activity_division_id
            ]) }}"
        >
            Back
        </x-rds.button>

        <x-rds.button
            href="{{ route('activities.edit', $activity) }}"
        >
            Edit Activity
        </x-rds.button>
    </x-slot>

    <x-rds.description-list>

        <x-rds.description-item label="Activity Name">
            {{ $activity->activity_name }}
        </x-rds.description-item>

        <x-rds.description-item label="Activity Division">
    {{ $activity->division ? Str::title(strtolower($activity->division->name)) : '-' }}
</x-rds.description-item>

        <x-rds.description-item label="Work Stage">
            {{ $activity->work_stage ?? '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Unit">
            <x-rds.badge variant="info">
                {{ $activity->unit ?? '-' }}
            </x-rds.badge>
        </x-rds.description-item>

        <x-rds.description-item label="Status">
            @if($activity->is_active)
                <x-rds.badge variant="success">Active</x-rds.badge>
            @else
                <x-rds.badge variant="danger">Inactive</x-rds.badge>
            @endif
        </x-rds.description-item>

        <x-rds.description-item label="Remarks">
            {{ $activity->remarks ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Created On">
            {{ optional($activity->created_at)->format('d M Y, h:i A') }}
        </x-rds.description-item>

        <x-rds.description-item label="Last Modified">
            {{ optional($activity->updated_at)->format('d M Y, h:i A') }}
        </x-rds.description-item>

    </x-rds.description-list>

</x-rds.resource.show>

@endsection