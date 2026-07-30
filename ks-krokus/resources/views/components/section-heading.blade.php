@props([
    'id',
    'title',
    'meta' => null,
])

<header class="section-header">
    <h2 id="{{ $id }}" class="section-title">{{ $title }}</h2>

    @if ($meta)
        <span class="section-meta">{{ $meta }}</span>
    @endif
</header>
