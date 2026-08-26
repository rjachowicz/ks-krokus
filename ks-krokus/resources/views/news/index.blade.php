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

    <section class="page-container page-section" aria-labelledby="news-list-title">
        <x-section-heading id="news-list-title" title="Wszystkie aktualności" meta="NEWS_ARCHIVE" />

        <form method="GET" class="content-toolbar filter-form panel-card" aria-label="Filtrowanie aktualności">
            <x-form-errors />

            <div class="filter-form__row">
                <label for="news-filter-query">
                    Szukaj
                    <input
                        id="news-filter-query"
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Tytuł lub treść"
                        autocomplete="off"
                        maxlength="100"
                        @error('q') aria-invalid="true" aria-describedby="news-filter-query-error" @enderror
                    >
                    @error('q')
                        <span id="news-filter-query-error" class="form-error" role="alert">{{ $message }}</span>
                    @enderror
                </label>

                <div class="filter-form__actions">
                    <div class="filter-form__action-group">
                        <button type="submit" class="btn btn-primary">Filtruj</button>
                        @if (request()->hasAny(['q']))
                            <a href="{{ route('news.index') }}" class="btn btn-secondary">Wyczyść</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        @if ($posts->isEmpty())
            <p class="empty-state">
                {{ request()->filled('q')
                    ? 'Nie znaleziono aktualności pasujących do wyszukiwanej frazy.'
                    : 'Nie opublikowano jeszcze żadnej aktualności.' }}
            </p>
        @else
            <div class="news-grid ui-grid ui-grid--3">
                @foreach ($posts as $post)
                    <x-content-card class="news-card">
                        @php($coverUrl = $post->coverVariantUrl())
                        @if ($coverUrl)
                            <div class="news-card__media">
                                <img
                                    src="{{ $coverUrl }}"
                                    alt="{{ $post->cover_image_alt ?: $post->title }}"
                                    loading="lazy"
                                >
                            </div>
                        @else
                            <div class="news-card__media">
                                <x-image-placeholder label="Brak zdjęcia aktualności" />
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
