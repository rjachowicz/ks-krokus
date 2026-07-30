@props([
    'code' => null,
    'title' => null,
    'status' => null,
    'href' => null,
    'linkLabel' => null,
    'badge' => null,
    'footerClass' => null,
])

<article {{ $attributes->class(['card']) }}>
    <div class="card-content-inner">
        @if ($code)
            <span class="card-code">{{ $code }}</span>
        @endif

        @if ($badge)
            <span class="resource-badge">{{ $badge }}</span>
        @endif

        @if ($title)
            <h3>{{ $title }}</h3>
        @endif

        {{ $slot }}
    </div>

    @isset($footer)
        <footer @class(['card-footer', $footerClass])>
            {{ $footer }}
        </footer>
    @else
        @if ($status || $linkLabel)
            <footer @class(['card-footer', $footerClass])>
                @if ($status)
                    <span class="card-status">{{ $status }}</span>
                @endif

                @if ($linkLabel)
                    @if ($href)
                        <a href="{{ $href }}" class="card-link">{{ $linkLabel }}</a>
                    @else
                        <span class="card-link">{{ $linkLabel }}</span>
                    @endif
                @endif
            </footer>
        @endif
    @endisset
</article>
