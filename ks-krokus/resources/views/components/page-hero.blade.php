@props([
    'id',
    'eyebrow',
    'visual' => false,
    'visualClass' => null,
    'visualPrimary' => null,
    'visualSecondary' => null,
])

<section {{ $attributes->class(['hero', 'page-container']) }} aria-labelledby="{{ $id }}">
    <div class="hero-content">
        <span class="category-tag">{{ $eyebrow }}</span>

        <h1 id="{{ $id }}">{{ $title }}</h1>

        @isset($description)
            <div class="hero-description">
                {{ $description }}
            </div>
        @endisset

        @isset($actions)
            <div class="btn-group">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @if ($visual)
        <div class="hero-visual" aria-hidden="true">
            <div @class(['visual-canvas', $visualClass])></div>

            @if ($visualPrimary || $visualSecondary)
                <div class="telemetry-overlay">
                    @if ($visualPrimary)
                        <span>{{ $visualPrimary }}</span>
                    @endif

                    @if ($visualSecondary)
                        <span>{{ $visualSecondary }}</span>
                    @endif
                </div>
            @endif
        </div>
    @endif
</section>
