@extends('layouts.app')

@section('title', $post->title.' — KS Krokus')
@section('meta_description', $post->excerpt ?: \Illuminate\Support\Str::limit($post->plainTextContent(), 155))

@section('content')
    <x-news.article
        :post="$post"
        :back-url="route('news.index')"
        back-label="← Wróć do aktualności"
    />

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
