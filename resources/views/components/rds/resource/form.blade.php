@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
    'method' => 'POST',
    'action',
])

<x-rds.page.form
    :title="$title"
    :description="$description"
    :breadcrumbs="$breadcrumbs"
>

    <form method="{{ $method === 'GET' ? 'GET' : 'POST' }}" action="{{ $action }}">
        @csrf

        @if(!in_array(strtoupper($method), ['GET', 'POST']))
            @method($method)
        @endif

        <div class="space-y-6">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4">
                {{ $footer }}
            </div>
        @endisset
    </form>

</x-rds.page.form>