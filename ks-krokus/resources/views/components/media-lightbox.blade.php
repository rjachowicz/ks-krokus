@props([
    'id',
    'title',
    'initialSrc',
    'initialAlt' => '',
    'initialCaption' => '',
])

<dialog
    id="{{ $id }}"
    class="media-lightbox"
    aria-labelledby="{{ $id }}-title"
    data-media-lightbox="{{ $id }}"
>
    <div class="media-lightbox__header">
        <h2 id="{{ $id }}-title">{{ $title }}</h2>
        <button
            type="button"
            class="media-lightbox__close"
            aria-label="Zamknij podgląd zdjęcia"
            data-media-lightbox-close
        >
            <span aria-hidden="true">×</span>
        </button>
    </div>
    <figure>
        <img
            src="{{ $initialSrc }}"
            alt="{{ $initialAlt }}"
            loading="lazy"
            data-media-lightbox-image
        >
        <figcaption data-media-lightbox-caption @if (! $initialCaption) hidden @endif>{{ $initialCaption }}</figcaption>
    </figure>
</dialog>
