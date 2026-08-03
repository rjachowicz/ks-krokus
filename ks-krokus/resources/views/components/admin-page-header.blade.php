@props([
    'title',
    'description' => null,
])

<header {{ $attributes->class(['admin-page-header', 'ui-cluster', 'ui-cluster--between']) }}>
    <hgroup>
        <h1>{{ $title }}</h1>

        @if ($description)
            <p>{{ $description }}</p>
        @endif
    </hgroup>

    @if (isset($actions) && $actions->hasActualContent())
        <div class="admin-actions ui-cluster">
            {{ $actions }}
        </div>
    @endif
</header>
