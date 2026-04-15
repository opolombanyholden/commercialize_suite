@props(['column', 'label', 'class' => '', 'currentSort' => request('sort'), 'currentDirection' => request('direction', 'desc')])

@php
    $isActive = $currentSort === $column;
    $nextDirection = ($isActive && $currentDirection === 'asc') ? 'desc' : 'asc';
    $sortUrl = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDirection, 'page' => null]);
@endphp

<th class="{{ $class }}">
    <a href="{{ $sortUrl }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1 sortable-th {{ $isActive ? 'active' : '' }}">
        {{ $label }}
        <span class="sort-icon">
            @if($isActive)
                <i class="fas fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} text-primary"></i>
            @else
                <i class="fas fa-sort text-muted opacity-25"></i>
            @endif
        </span>
    </a>
</th>
