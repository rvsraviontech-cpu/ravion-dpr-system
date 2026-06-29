@extends('layouts.app')

@section('content')

<x-rds.resource.show
    title="Work Stage Details"
    description="View construction execution stage details."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Execution Masters'],
        ['label' => 'Work Stages', 'url' => route('work-stages.index')],
        ['label' => 'Work Stage Details'],
    ]"
>
    <x-slot name="actions">
        <x-rds.button variant="secondary" href="{{ route('work-stages.index') }}">
            Back
        </x-rds.button>

        <x-rds.button href="{{ route('work-stages.edit', $workStage) }}">
            Edit Work Stage
        </x-rds.button>
    </x-slot>

    <x-rds.description-list>
        <x-rds.description-item label="Code">
            <x-rds.badge variant="info">{{ $workStage->code }}</x-rds.badge>
        </x-rds.description-item>

        <x-rds.description-item label="Work Stage">
            {{ $workStage->name }}
        </x-rds.description-item>

        <x-rds.description-item label="Sequence">
            {{ $workStage->sequence }}
        </x-rds.description-item>

        <x-rds.description-item label="Status">
            @if($workStage->is_active)
                <x-rds.badge variant="success">Active</x-rds.badge>
            @else
                <x-rds.badge variant="danger">Inactive</x-rds.badge>
            @endif
        </x-rds.description-item>

        <x-rds.description-item label="Remarks">
            {{ $workStage->remarks ?: '-' }}
        </x-rds.description-item>

        <x-rds.description-item label="Created On">
            {{ optional($workStage->created_at)->format('d M Y, h:i A') }}
        </x-rds.description-item>

        <x-rds.description-item label="Last Modified">
            {{ optional($workStage->updated_at)->format('d M Y, h:i A') }}
        </x-rds.description-item>
    </x-rds.description-list>
</x-rds.resource.show>

@endsection