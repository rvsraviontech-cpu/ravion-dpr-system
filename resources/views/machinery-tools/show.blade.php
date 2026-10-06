@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $machineryTool->machine_name }}
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Machinery & Equipment Master
            </p>
        </div>

        <div class="flex gap-2">

            <a
                href="{{ route('machinery-tools.index') }}"
                class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700"
            >
                Back
            </a>

            <a
                href="{{ route('machinery-tools.edit', $machineryTool) }}"
                class="px-4 py-2 rounded-lg bg-[#0F2A52] text-white text-sm font-semibold"
            >
                Edit
            </a>

        </div>

    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">

            @foreach([
                'Code' => $machineryTool->code,
                'Category' => $machineryTool->category,
                'Unit' => $machineryTool->unit,
                'Tracking Mode' => ucfirst($machineryTool->tracking_mode),
                'Meter Type' => ucwords(str_replace('_', ' ', $machineryTool->meter_type)),
                'Fuel / Power' => ucfirst($machineryTool->fuel_type),
            ] as $label => $value)

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                        {{ $label }}
                    </div>

                    <div class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $value ?: '—' }}
                    </div>
                </div>

            @endforeach

        </div>

        <div class="border-t border-gray-200 p-6">

            <h2 class="font-bold text-gray-900 mb-4">
                Operational Behaviour
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                @foreach([
                    'Operator Required' => $machineryTool->requires_operator,
                    'Meter Required' => $machineryTool->requires_meter_reading,
                    'Fuel Tracking' => $machineryTool->requires_fuel_tracking,
                    'Active' => $machineryTool->is_active,
                ] as $label => $enabled)

                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="text-xs text-gray-500">
                            {{ $label }}
                        </div>

                        <div class="mt-1 font-semibold {{ $enabled ? 'text-green-700' : 'text-gray-500' }}">
                            {{ $enabled ? 'Yes' : 'No' }}
                        </div>
                    </div>

                @endforeach

            </div>

        </div>

        @if($machineryTool->description)
            <div class="border-t border-gray-200 p-6">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                    Description
                </div>

                <div class="mt-2 text-sm text-gray-700 whitespace-pre-line">
                    {{ $machineryTool->description }}
                </div>
            </div>
        @endif

    </div>

</div>

@endsection