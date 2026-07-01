@props([
    'striped' => false,
    'hover' => true,
])

<div class="rounded-xl border border-gray-200 bg-white shadow-sm">
    <table {{ $attributes->merge([
        'class' => 'min-w-full divide-y divide-gray-200 text-sm'
    ]) }}>
        @isset($head)
            <thead class="bg-gray-50">
                <tr>
                    {{ $head }}
                </tr>
            </thead>
        @endisset

        <tbody class="divide-y divide-gray-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>