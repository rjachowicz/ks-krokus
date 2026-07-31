@props([
    'title',
    'description' => null,
])

<header {{ $attributes->class(['admin-page-header']) }}>
    <hgroup>
        <h1>{{ $title }}</h1>

        @if ($description)
            <p>{{ $description }}</p>
        @endif
    </hgroup>

    @if (isset($actions) && $actions->hasActualContent())
        <div class="admin-actions">
            {{ $actions }}
        </div>
    @endif
</header>
