@php
    $currentStatus = old(
        'status',
        isset($post) ? $post->status->value : \App\Enums\PublicationStatus::Draft->value,
    );

    $publishedAt = old(
        'published_at',
        isset($post) && $post->published_at
            ? $post->published_at->format('Y-m-d\TH:i')
            : '',
    );

    $selectedImageIds = old('delete_images', []);
    $selectedImageIds = is_array($selectedImageIds)
        ? array_map('intval', $selectedImageIds)
        : [];

    $galleryErrorIds = implode(' ', array_filter([
        $errors->has('gallery_images') ? 'gallery-images-error' : null,
        $errors->has('gallery_images.*') ? 'gallery-image-items-error' : null,
    ]));
    $coverUrl = isset($post) ? $post->coverUrl() : null;
@endphp

<div class="form-grid">
    <h2 class="admin-section-title form-grid--span-full">Treść aktualności</h2>
    <label class="form-grid--span-full">
        <span class="form-label-text">
            Tytuł <span class="form-required" aria-hidden="true">*</span>
            <span class="sr-only">(pole wymagane)</span>
        </span>
        <input id="post-title" type="text" name="title" value="{{ old('title', $post->title ?? '') }}"
               autocomplete="off" required autofocus
               @error('title') aria-invalid="true" aria-describedby="post-title-error" @enderror>
        @error('title') <span id="post-title-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="form-grid--span-full">
        Krótkie streszczenie
        <textarea id="post-excerpt" name="excerpt" rows="3" autocomplete="off"
                  aria-describedby="post-excerpt-help @error('excerpt') post-excerpt-error @enderror"
                  @error('excerpt') aria-invalid="true" @enderror>{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
        <span id="post-excerpt-help"
              class="form-help">Widoczne na listach aktualności i jako wprowadzenie do artykułu.</span>
        @error('excerpt') <span id="post-excerpt-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <div class="form-field form-grid--span-full">
        <label id="post-content-label" for="post-content" class="form-label-text">
            Treść <span class="form-required" aria-hidden="true">*</span>
            <span class="sr-only">(pole wymagane)</span>
        </label>
        <input type="hidden" name="content_format" value="html">
        <textarea
            id="post-content"
            name="content"
            rows="16"
            required
            data-rich-text
            autocomplete="off"
            aria-describedby="post-content-help @error('content') post-content-error @enderror"
            @error('content') aria-invalid="true" @enderror
        >{{ old('content', $post->content ?? '') }}</textarea>
        <span id="post-content-help" class="form-help">
            Użyj paska narzędzi do formatowania nagłówków, list i wyróżnień.
        </span>
        @error('content') <span id="post-content-error" class="form-error" role="alert">{{ $message }}</span> @enderror
    </div>

    <h2 class="admin-section-title form-grid--span-full">Publikacja</h2>

    <label>
        <span class="form-label-text">
            Status <span class="form-required" aria-hidden="true">*</span>
            <span class="sr-only">(pole wymagane)</span>
        </span>
        <select id="post-status" name="status" required @error('status') aria-invalid="true"
                aria-describedby="post-status-error" @enderror>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <span id="post-status-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Data publikacji
        <input id="post-published-at" type="datetime-local" name="published_at" value="{{ $publishedAt }}"
               aria-describedby="post-published-at-help @error('published_at') post-published-at-error @enderror"
               @error('published_at') aria-invalid="true" @enderror>
        <span id="post-published-at-help" class="form-help">Przy statusie „Opublikowane” puste pole zostanie ustawione na bieżący czas.</span>
        @error('published_at') <span id="post-published-at-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <h2 class="admin-section-title form-grid--span-full">Zdjęcie główne</h2>

    @if (isset($post) && $post->cover_image_path)
        <div class="form-grid--span-full image-edit-card" data-crop-scope data-existing-cover-crop>
            @if ($coverUrl)
                <img src="{{ $coverUrl }}" alt="{{ $post->cover_image_alt ?: $post->title }}">
            @else
                <x-image-placeholder />
            @endif
            <div class="image-edit-card__body">
                <x-media-crop-fields name="cover_crop" :crop="old('cover_crop', $post->cover_crop)" />
                @if ($coverUrl)
                    <button type="button" class="btn btn-secondary" data-crop-control
                            data-crop-url="{{ $coverUrl }}" data-crop-aspect="1.7777777778">
                        Ustaw ponownie kadr okładki
                    </button>
                @endif
                <label class="form-check">
                    <input id="post-remove-cover" type="checkbox" name="remove_cover" value="1"
                           data-cover-remove
                           @checked(old('remove_cover', false))
                           @error('remove_cover') aria-invalid="true"
                           aria-describedby="post-remove-cover-error" @enderror>
                    Usuń obecne zdjęcie główne
                </label>
                @error('remove_cover')
                    <span id="post-remove-cover-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    @endif

    <div
        class="file-upload form-grid--span-full"
        data-file-upload
        data-max-files="{{ config('content.gallery_max_images') }}"
        data-max-size-kb="{{ config('content.image_max_size_kb') }}"
    >
        <label class="file-upload__dropzone">
            <strong>Nowe zdjęcie główne</strong>
            <span>Przeciągnij obraz tutaj lub wybierz plik</span>
            <input id="post-cover-image" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"
                   data-cover-file data-crop-enabled data-crop-name="cover_crop" data-crop-aspect="1.7777777778"
                   aria-describedby="post-cover-image-help @error('cover_image') cover-image-error @enderror"
                   @error('cover_image') aria-invalid="true" @enderror>
            <small id="post-cover-image-help">
                JPG, PNG lub WebP, maksymalnie 6 MB.
                @if ($coverUrl)
                    Nowy plik zastąpi obecne zdjęcie.
                @endif
            </small>
            @error('cover_image') <span id="cover-image-error" class="form-error"
                                        role="alert">{{ $message }}</span> @enderror
        </label>
        <div class="file-preview-list" data-file-preview aria-live="polite"></div>
        <p class="form-error" data-file-error aria-live="assertive" hidden></p>
        @if ($errors->has('cover_crop') || $errors->has('cover_crop.*'))
            <span class="form-error" role="alert">{{ $errors->first('cover_crop') ?: $errors->first('cover_crop.*') }}</span>
        @endif
    </div>

    <label>
        Tekst alternatywny zdjęcia
        <input
            type="text"
            id="post-cover-alt"
            name="cover_image_alt"
            value="{{ old('cover_image_alt', $post->cover_image_alt ?? '') }}"
            autocomplete="off"
            aria-describedby="post-cover-alt-help @error('cover_image_alt') post-cover-alt-error @enderror"
            @error('cover_image_alt') aria-invalid="true" @enderror
        >
        <span id="post-cover-alt-help" class="form-help">Krótki opis dla dostępności i wyszukiwarek.</span>
        @error('cover_image_alt') <span id="post-cover-alt-error" class="form-error">{{ $message }}</span> @enderror
    </label>
    <h2 class="admin-section-title form-grid--span-full">Galeria zdjęć</h2>

    <div
        class="file-upload form-grid--span-full"
        data-file-upload
        data-max-files="{{ config('content.gallery_max_images') }}"
        data-max-size-kb="{{ config('content.image_max_size_kb') }}"
        data-existing-files="{{ isset($post) ? $post->images->count() : 0 }}"
    >
        <label class="file-upload__dropzone">
            <strong>Dodaj zdjęcia do galerii</strong>
            <span>Przeciągnij obrazy tutaj lub wybierz pliki</span>
            <input
                type="file"
                id="post-gallery-images"
                name="gallery_images[]"
                accept="image/jpeg,image/png,image/webp"
                multiple
                data-crop-enabled data-crop-name="gallery_crops" data-crop-aspect="1.3333333333"
                aria-describedby="post-gallery-images-help{{ $galleryErrorIds !== '' ? ' '.$galleryErrorIds : '' }}"
                @if ($galleryErrorIds !== '')
                    aria-invalid="true"
                @endif
            >
            <small id="post-gallery-images-help">Łącznie maksymalnie 12 zdjęć, każde do 6 MB.</small>
            @error('gallery_images') <span id="gallery-images-error" class="form-error"
                                           role="alert">{{ $message }}</span> @enderror
            @error('gallery_images.*') <span id="gallery-image-items-error" class="form-error"
                                             role="alert">{{ $message }}</span> @enderror
        </label>
        <div class="file-preview-list" data-file-preview aria-live="polite"></div>
        <p class="form-error" data-file-error aria-live="assertive" hidden></p>
        @if ($errors->has('gallery_crops') || $errors->has('gallery_crops.*'))
            <span class="form-error" role="alert">{{ $errors->first('gallery_crops') ?: $errors->first('gallery_crops.*') }}</span>
        @endif
    </div>

    @if (isset($post) && $post->images->isNotEmpty())
        @error('existing_images')
        <span id="post-existing-images-error" class="form-error form-grid--span-full" role="alert">{{ $message }}</span>
        @enderror
        @error('delete_images.*')
        <span id="post-delete-images-error" class="form-error form-grid--span-full" role="alert">{{ $message }}</span>
        @enderror

        <div
            class="image-preview-grid form-grid--span-full"
            @if ($errors->has('existing_images') || $errors->has('delete_images.*'))
                aria-invalid="true"
            aria-describedby="@error('existing_images') post-existing-images-error @enderror @error('delete_images.*') post-delete-images-error @enderror"
            @endif
        >
            @foreach ($post->images as $image)
                @php($imageUrl = $image->url())
                <div class="image-edit-card" data-crop-scope>
                    @if ($imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $image->alt_text ?: $post->title }}">
                    @else
                        <x-image-placeholder />
                    @endif

                    <div class="image-edit-card__body">
                        <x-media-crop-fields
                            name="existing_images[{{ $image->id }}][crop]"
                            :crop="old("existing_images.{$image->id}.crop", $image->crop)"
                        />
                        @if ($imageUrl)
                            <button type="button" class="btn btn-secondary" data-crop-control
                                    data-crop-url="{{ $imageUrl }}" data-crop-aspect="1.3333333333">
                                Ustaw ponownie kadr miniatury
                            </button>
                        @endif
                        @if ($errors->has("existing_images.{$image->id}.crop") || $errors->has("existing_images.{$image->id}.crop.*"))
                            <span class="form-error" role="alert">{{ $errors->first("existing_images.{$image->id}.crop") ?: $errors->first("existing_images.{$image->id}.crop.*") }}</span>
                        @endif
                        <label>
                            Tekst alternatywny
                            <input
                                id="post-image-{{ $image->id }}-alt"
                                type="text"
                                name="existing_images[{{ $image->id }}][alt_text]"
                                value="{{ old("existing_images.{$image->id}.alt_text", $image->alt_text) }}"
                                autocomplete="off"
                                @error("existing_images.{$image->id}.alt_text") aria-invalid="true"
                                aria-describedby="post-image-{{ $image->id }}-alt-error" @enderror
                            >
                            @error("existing_images.{$image->id}.alt_text")
                            <span id="post-image-{{ $image->id }}-alt-error" class="form-error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label>
                            Podpis
                            <textarea
                                id="post-image-{{ $image->id }}-caption"
                                name="existing_images[{{ $image->id }}][caption]"
                                rows="3"
                                autocomplete="off"
                                @error("existing_images.{$image->id}.caption") aria-invalid="true"
                                aria-describedby="post-image-{{ $image->id }}-caption-error" @enderror
                            >{{ old("existing_images.{$image->id}.caption", $image->caption) }}</textarea>
                            @error("existing_images.{$image->id}.caption")
                            <span id="post-image-{{ $image->id }}-caption-error"
                                  class="form-error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label>
                            Kolejność
                            <input
                                id="post-image-{{ $image->id }}-order"
                                type="number"
                                name="existing_images[{{ $image->id }}][sort_order]"
                                min="0"
                                value="{{ old("existing_images.{$image->id}.sort_order", $image->sort_order) }}"
                                @error("existing_images.{$image->id}.sort_order") aria-invalid="true"
                                aria-describedby="post-image-{{ $image->id }}-order-error" @enderror
                            >
                            @error("existing_images.{$image->id}.sort_order")
                            <span id="post-image-{{ $image->id }}-order-error" class="form-error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="form-check">
                            <input
                                type="checkbox"
                                name="delete_images[]"
                                value="{{ $image->id }}"
                                data-existing-file-delete
                                @checked(in_array($image->id, $selectedImageIds, true))
                                @if ($errors->has('delete_images.*'))
                                    aria-invalid="true" aria-describedby="post-delete-images-error"
                                @endif
                            >
                            Usuń zdjęcie
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<x-media-cropper-dialog />

<div class="form-actions form-actions--sticky">
    <button type="submit" class="btn btn-primary">Zapisz aktualność</button>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
