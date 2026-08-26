@extends('layouts.app')

@section('title', $post->title.' — KS Krokus')
@section('meta_description', $post->excerpt ?: \Illuminate\Support\Str::limit($post->plainTextContent(), 155))

@section('content')
    <article class="article-shell page-container">
        <header class="article-header">
            <div class="news-card__meta">
                <span>{{ $post->published_at?->format('d.m.Y H:i') }}</span>
                <span>{{ $post->author?->name ?? 'KS Krokus' }}</span>
            </div>

            <h1>{{ $post->title }}</h1>

            @if ($post->excerpt)
                <p class="article-lead">{{ $post->excerpt }}</p>
            @endif
        </header>

        @php($coverUrl = $post->coverVariantUrl())
        @if ($coverUrl)
            <figure class="article-cover">
                <img
                    src="{{ $coverUrl }}"
                    alt="{{ $post->cover_image_alt ?: $post->title }}"
                >
            </figure>
        @elseif ($post->cover_image_path || $post->cover_variant_path)
            <div class="article-cover">
                <x-image-placeholder label="Zdjęcie aktualności jest chwilowo niedostępne" />
            </div>
        @endif

        <div class="article-body">
            {!! $post->safeContentHtml() !!}
        </div>

        @if ($post->images->isNotEmpty())
            <div class="article-gallery">
                @foreach ($post->images as $image)
                    @php($imageUrl = $image->url())
                    <figure>
                        @if ($imageUrl)
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $image->alt_text ?: $post->title }}"
                                loading="lazy"
                            >
                        @else
                            <x-image-placeholder />
                        @endif

                        @if ($image->caption)
                            <figcaption>{{ $image->caption }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif

        <div class="btn-group content-actions">
            <a href="{{ route('news.index') }}" class="btn btn-secondary">← Wróć do aktualności</a>
        </div>
    </article>

    @if ($morePosts->isNotEmpty())
        <section class="page-container page-section" aria-labelledby="more-news-title">
            <x-section-heading id="more-news-title" title="Więcej aktualności" meta="READ_MORE" />

            <div class="news-grid ui-grid ui-grid--3">
                @foreach ($morePosts as $morePost)
                    <x-content-card
                        title="{{ $morePost->title }}"
                        status="{{ $morePost->published_at?->format('d.m.Y') }}"
                        :href="route('news.show', $morePost)"
                        link-label="Czytaj dalej →"
                    >
                        <p>{{ $morePost->excerpt ?: \Illuminate\Support\Str::limit($morePost->plainTextContent(), 150) }}</p>
                    </x-content-card>
                @endforeach
            </div>
        </section>
    @endif
@endsection
