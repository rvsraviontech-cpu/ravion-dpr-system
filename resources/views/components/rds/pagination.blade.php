@props([
    'paginator',
])

@if($paginator->hasPages())
    <div {{ $attributes->merge([
        'class' => 'mt-4 border-t border-gray-200 pt-4'
    ]) }}>
        {{ $paginator->links() }}
    </div>
@endif