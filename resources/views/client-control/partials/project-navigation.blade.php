{{-- Edit menu labels, links and availability here only. $project can be an array or Eloquent model. --}}
@php
    $rvProjectId = data_get($project, 'id');
    $rvActive = match (true) {
        request()->routeIs('client.support.*') => 'support',
        request()->routeIs('client.drawings.*') => 'drawings',
        request()->routeIs('client.payments.*') => 'payments',
        request()->routeIs('client.decisions.*') => 'decisions',
        request()->routeIs('client.quality.*') => 'quality',
        request()->routeIs('client.vault.*') => 'documents',
        default => 'home',
    };
    $rvHomeUrl = route('client.projects.show', ['project' => $rvProjectId]);
    $rvMenu = [
        ['label' => 'Home', 'url' => $rvHomeUrl, 'key' => 'home', 'enabled' => true],
        ['label' => 'Progress', 'url' => $rvHomeUrl . '#published-progress', 'key' => 'progress', 'enabled' => true],
        ['label' => 'Quality & Testing', 'url' => route('client.quality.index', ['project' => $rvProjectId]), 'key' => 'quality', 'enabled' => true],
        ['label' => 'Documents', 'url' => route('client.vault.index', ['project' => $rvProjectId]), 'key' => 'documents', 'enabled' => true],
        ['label' => 'Drawings', 'url' => route('client.drawings.index', ['project' => $rvProjectId]), 'key' => 'drawings', 'enabled' => true],
        ['label' => 'My Decisions', 'url' => route('client.decisions.index', ['project' => $rvProjectId]), 'key' => 'decisions', 'enabled' => true],
        ['label' => 'Payments', 'url' => route('client.payments.show', ['project' => $rvProjectId]), 'key' => 'payments', 'enabled' => true],
        ['label' => 'Contact & Support', 'url' => route('client.support.index', ['project' => $rvProjectId]), 'key' => 'support', 'enabled' => true],
    ];
@endphp
<nav class="rv-project-navigation" aria-label="Client project navigation">
    @foreach($rvMenu as $rvItem)
        @if($rvItem['enabled'])
            <a class="rv-project-link {{ $rvActive === $rvItem['key'] ? 'is-active' : '' }}"
               href="{{ $rvItem['url'] }}"
               @if($rvActive === $rvItem['key']) aria-current="page" @endif>{{ $rvItem['label'] }}</a>
        @else
            <span class="rv-project-link is-disabled" aria-disabled="true" title="Available when this section is launched">{{ $rvItem['label'] }}</span>
        @endif
    @endforeach
</nav>
