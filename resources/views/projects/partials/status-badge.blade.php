@php
    $statusClasses = [
        'Not Started' => 'bg-gray-100 text-gray-700',
        'Active' => 'bg-green-100 text-green-700',
        'On Hold' => 'bg-yellow-100 text-yellow-700',
        'Delayed' => 'bg-red-100 text-red-700',
        'Under Snagging' => 'bg-orange-100 text-orange-700',
        'Completed' => 'bg-blue-100 text-blue-700',
        'Handed Over' => 'bg-indigo-100 text-indigo-700',
        'Closed' => 'bg-slate-200 text-slate-700',
    ];

    $class = $statusClasses[$status ?? 'Active'] ?? 'bg-gray-100 text-gray-700';
@endphp

<span class="px-2 py-1 rounded-full text-xs font-semibold {{ $class }}">
    {{ $status ?? 'Active' }}
</span>