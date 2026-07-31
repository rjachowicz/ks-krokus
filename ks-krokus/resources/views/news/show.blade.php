@extends('layouts.app')

@section('title', $post->title.' — KS Krokus')
@section('meta_description', $post->excerpt ?: \Illuminate\Support\Str::limit($post->plainTextContent(), 155))

@section('content')
    <article class="article-shell">
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

        @if ($post->coverUrl())
            <figure class="article-cover">
                <img
                    src="{{ $post->coverUrl() }}"
                    alt="{{ $post->cover_image_alt ?: $post->title }}"
                >
            </figure>
        @endif

        <div class="article-body">
            {!! $post->safeContentHtml() !!}
        </div>

        @if ($post->images->isNotEmpty())
            <div class="article-gallery">
                @foreach ($post->images as $image)
                    <figure>
                        <img
                            src="{{ $image->url() }}"
                            alt="{{ $image->alt_text ?: $post->title }}"
                            loading="lazy"
                        >

                        @if ($image->caption)
                            <figcaption>{{ $image->caption }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif
    </article>

    @if ($morePosts->isNotEmpty())
        <section class="features-section" aria-labelledby="more-news-title">
            <x-section-heading id="more-news-title" title="Więcej aktualności" meta="READ_MORE" />

            <div class="news-grid">
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
