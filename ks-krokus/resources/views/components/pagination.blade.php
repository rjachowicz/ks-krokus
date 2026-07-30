@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Nawigacja stron">
        @if ($paginator->onFirstPage())
            <span class="is-disabled" aria-disabled="true">←</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Poprzednia strona">←</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="is-disabled">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <span class="is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Następna strona">→</a>
        @else
            <span class="is-disabled" aria-disabled="true">→</span>
        @endif
    </nav>
@endif
