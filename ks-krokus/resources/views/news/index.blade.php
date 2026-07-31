@extends('layouts.app')

@section('title', 'Aktualności — KS Krokus')
@section('meta_description', 'Aktualności, komunikaty i relacje z działalności Klubu Strzeleckiego Krokus LOK w Nowym Sączu.')

@section('content')
    <x-page-hero id="news-title" eyebrow="WIADOMOŚCI KLUBOWE" class="content-hero">
        <x-slot:title>
            Aktualności <span class="highlight">KS Krokus</span>
        </x-slot:title>

        <x-slot:description>
            <p>
                Komunikaty organizacyjne, relacje z zawodów, szkolenia i najważniejsze informacje dla członków klubu.
            </p>
        </x-slot:description>
    </x-page-hero>

    <section class="features-section" aria-labelledby="news-list-title">
        <x-section-heading id="news-list-title" title="Wszystkie aktualności" meta="NEWS_ARCHIVE" />

        <form method="GET" class="content-toolbar">
            <label>
                Szukaj
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Tytuł lub treść"
                >
            </label>

            <button type="submit" class="btn btn-primary">Filtruj</button>

            @if (request()->hasAny(['q']))
                <a href="{{ route('news.index') }}" class="btn btn-secondary">Wyczyść</a>
            @endif
        </form>

        @if ($posts->isEmpty())
            <div class="content-empty">Nie znaleziono opublikowanych aktualności.</div>
        @else
            <div class="news-grid">
                @foreach ($posts as $post)
                    <x-content-card class="news-card">
                        @if ($post->coverUrl())
                            <div class="news-card__media">
                                <img
                                    src="{{ $post->coverUrl() }}"
                                    alt="{{ $post->cover_image_alt ?: $post->title }}"
                                    loading="lazy"
                                >
                            </div>
                        @endif

                        <div class="news-card__meta">
                            <span>{{ $post->published_at?->format('d.m.Y') }}</span>
                            <span>{{ $post->author?->name ?? 'KS Krokus' }}</span>
                        </div>

                        <h3>{{ $post->title }}</h3>

                        <p>
                            {{ $post->excerpt ?: \Illuminate\Support\Str::limit($post->plainTextContent(), 220) }}
                        </p>

                        <x-slot:footer>
                            <span class="card-status">AKTUALNOŚĆ</span>
                            <a href="{{ route('news.show', $post) }}" class="card-link">
                                Czytaj dalej →
                            </a>
                        </x-slot:footer>
                    </x-content-card>
                @endforeach
            </div>

            {{ $posts->links() }}
        @endif
    </section>
@endsection
