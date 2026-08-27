@props([
    'post',
    'backUrl',
    'backLabel',
    'editUrl' => null,
    'showStatus' => false,
])

@php
    $coverUrl = $post->coverVariantUrl();
    $coverFullUrl = $post->coverUrl() ?: $coverUrl;
    $galleryMedia = $post->images
        ->map(fn ($image) => ['image' => $image, 'url' => $image->url()])
        ->filter(fn ($media) => $media['url'] !== null)
        ->values();
    $firstGalleryMedia = $galleryMedia->first();
    $initialLightboxUrl = $coverUrl ? $coverFullUrl : ($firstGalleryMedia['url'] ?? null);
    $initialLightboxAlt = $coverUrl
        ? ($post->cover_image_alt ?: $post->title)
        : (($firstGalleryMedia['image']->alt_text ?? null) ?: $post->title);
    $initialLightboxCaption = $coverUrl ? '' : ($firstGalleryMedia['image']->caption ?? '');
    $lightboxId = 'news-lightbox-'.$post->getKey();
@endphp

<article class="article-shell page-container">
    <header class="article-header">
        <div class="news-card__meta">
            @if ($showStatus)
                <span>Status: {{ $post->status->label() }}</span>
            @endif
            <span>{{ $post->published_at?->format('d.m.Y H:i') }}</span>
            <span>{{ $post->author?->name ?? 'KS Krokus' }}</span>
        </div>

        <h1>{{ $post->title }}</h1>

        @if ($post->excerpt)
            <p class="article-lead">{{ $post->excerpt }}</p>
        @endif
    </header>

    @if ($coverUrl)
        <figure class="article-cover">
            <a
                href="{{ $coverFullUrl }}"
                class="article-media-link"
                data-media-lightbox-trigger="{{ $lightboxId }}"
                aria-label="Powiększ zdjęcie główne: {{ $post->cover_image_alt ?: $post->title }}"
            >
                <img
                    src="{{ $coverUrl }}"
                    alt="{{ $post->cover_image_alt ?: $post->title }}"
                >
            </a>
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
                        <a
                            href="{{ $imageUrl }}"
                            class="article-media-link"
                            data-media-lightbox-trigger="{{ $lightboxId }}"
                            data-media-lightbox-caption="{{ $image->caption }}"
                            aria-label="Powiększ zdjęcie: {{ $image->alt_text ?: $post->title }}"
                        >
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $image->alt_text ?: $post->title }}"
                                loading="lazy"
                            >
                        </a>
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
        @if ($editUrl)
            <a href="{{ $editUrl }}" class="btn btn-primary">Edytuj aktualność</a>
        @endif
        <a href="{{ $backUrl }}" class="btn btn-secondary">{{ $backLabel }}</a>
    </div>

    @if ($coverUrl || $galleryMedia->isNotEmpty())
        <x-media-lightbox
            :id="$lightboxId"
            title="Powiększone zdjęcie aktualności"
            :initial-src="$initialLightboxUrl"
            :initial-alt="$initialLightboxAlt"
            :initial-caption="$initialLightboxCaption"
        />
    @endif
</article>
