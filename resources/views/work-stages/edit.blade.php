@extends('layouts.app')

@section('content')

<x-rds.resource.form
    title="Edit Work Stage"
    description="Update construction execution stage details."
    method="PUT"
    action="{{ route('work-stages.update', $workStage) }}"
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Execution Masters'],
        ['label' => 'Work Stages', 'url' => route('work-stages.index')],
        ['label' => 'Edit Work Stage'],
    ]"
>
    @if ($errors->any())
        <x-rds.alert type="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </x-rds.alert>
    @endif

    <x-rds.section
        title="Basic Information"
        description="Work stages are used to group activities for DPR, planning and reporting."
    >
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

            <x-rds.input
                name="code"
                label="Code"
                value="{{ old('code', $workStage->code) }}"
                required
            />

            <x-rds.input
                type="number"
                name="sequence"
                label="Sequence"
                value="{{ old('sequence', $workStage->sequence) }}"
            />

            <div class="md:col-span-2">
                <x-rds.input
                    name="name"
                    label="Work Stage Name"
                    value="{{ old('name', $workStage->name) }}"
                    required
                />
            </div>

            <div class="md:col-span-2">
                <x-rds.textarea
                    name="remarks"
                    label="Remarks"
                    rows="3"
                    value="{{ old('remarks', $workStage->remarks) }}"
                />
            </div>

            <div class="flex items-center pt-2 md:col-span-2">
                <label class="inline-flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        class="rounded border-gray-300"
                        @checked(old('is_active', $workStage->is_active))
                    >

                    <span class="text-sm font-semibold text-gray-700">
                        Active
                    </span>
                </label>
            </div>

        </div>
    </x-rds.section>

    <x-slot name="footer">
        <x-rds.button
            variant="secondary"
            href="{{ route('work-stages.index') }}"
        >
            Cancel
        </x-rds.button>

        <x-rds.button type="submit">
            Update Work Stage
        </x-rds.button>
    </x-slot>
</x-rds.resource.form>

@endsection