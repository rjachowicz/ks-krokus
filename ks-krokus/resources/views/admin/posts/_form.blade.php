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
@endphp

<div class="admin-form-grid">
    <h2 class="admin-section-title span-full">Treść aktualności</h2>
    <label class="span-full">
        Tytuł
        <input id="post-title" type="text" name="title" value="{{ old('title', $post->title ?? '') }}" autocomplete="off" required autofocus
            @error('title') aria-invalid="true" aria-describedby="post-title-error" @enderror>
        @error('title') <span id="post-title-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="span-full">
        Krótkie streszczenie
        <textarea id="post-excerpt" name="excerpt" rows="3" autocomplete="off"
            aria-describedby="post-excerpt-help @error('excerpt') post-excerpt-error @enderror"
            @error('excerpt') aria-invalid="true" @enderror>{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
        <span id="post-excerpt-help" class="form-help">Widoczne na listach aktualności i jako wprowadzenie do artykułu.</span>
        @error('excerpt') <span id="post-excerpt-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="span-full">
        Treść
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
    </label>

    <h2 class="admin-section-title span-full">Publikacja</h2>

    <label>
        Status
        <select id="post-status" name="status" required @error('status') aria-invalid="true" aria-describedby="post-status-error" @enderror>
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

    <h2 class="admin-section-title span-full">Zdjęcie główne</h2>

    @if (isset($post) && $post->coverUrl())
        <div class="span-full image-edit-card">
            <img src="{{ $post->coverUrl() }}" alt="{{ $post->cover_image_alt ?: $post->title }}">
            <div class="image-edit-card__body">
                <label class="form-check">
                    <input id="post-remove-cover" type="checkbox" name="remove_cover" value="1"
                        @checked(old('remove_cover', false))
                        @error('remove_cover') aria-invalid="true" aria-describedby="post-remove-cover-error" @enderror>
                    Usuń obecne zdjęcie główne
                    @error('remove_cover') <span id="post-remove-cover-error" class="form-error">{{ $message }}</span> @enderror
                </label>
            </div>
        </div>
    @endif

    <div
        class="file-upload"
        data-file-upload
        data-max-files="1"
        data-max-size-kb="{{ config('content.image_max_size_kb') }}"
    >
        <label class="file-upload__dropzone">
            <strong>Nowe zdjęcie główne</strong>
            <span>Przeciągnij obraz tutaj lub wybierz plik</span>
            <input id="post-cover-image" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"
                aria-describedby="post-cover-image-help @error('cover_image') cover-image-error @enderror"
                @error('cover_image') aria-invalid="true" @enderror>
            <small id="post-cover-image-help">JPG, PNG lub WebP, maksymalnie 6 MB.</small>
            @error('cover_image') <span id="cover-image-error" class="form-error" role="alert">{{ $message }}</span> @enderror
        </label>
        <div class="file-preview-list" data-file-preview aria-live="polite"></div>
        <p class="form-error" data-file-error aria-live="assertive" hidden></p>
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

    <h2 class="admin-section-title span-full">Galeria zdjęć</h2>

    <div
        class="file-upload span-full"
        data-file-upload
        data-max-files="{{ config('content.gallery_max_images') }}"
        data-max-size-kb="{{ config('content.image_max_size_kb') }}"
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
                aria-describedby="post-gallery-images-help{{ $galleryErrorIds !== '' ? ' '.$galleryErrorIds : '' }}"
                @if ($galleryErrorIds !== '')
                    aria-invalid="true"
                @endif
            >
            <small id="post-gallery-images-help">Łącznie maksymalnie 12 zdjęć, każde do 6 MB.</small>
            @error('gallery_images') <span id="gallery-images-error" class="form-error" role="alert">{{ $message }}</span> @enderror
            @error('gallery_images.*') <span id="gallery-image-items-error" class="form-error" role="alert">{{ $message }}</span> @enderror
        </label>
        <div class="file-preview-list" data-file-preview aria-live="polite"></div>
        <p class="form-error" data-file-error aria-live="assertive" hidden></p>
    </div>

    @if (isset($post) && $post->images->isNotEmpty())
        @error('existing_images')
            <span id="post-existing-images-error" class="form-error span-full" role="alert">{{ $message }}</span>
        @enderror
        @error('delete_images.*')
            <span id="post-delete-images-error" class="form-error span-full" role="alert">{{ $message }}</span>
        @enderror

        <div
            class="image-preview-grid span-full"
            @if ($errors->has('existing_images') || $errors->has('delete_images.*'))
                aria-invalid="true"
                aria-describedby="@error('existing_images') post-existing-images-error @enderror @error('delete_images.*') post-delete-images-error @enderror"
            @endif
        >
            @foreach ($post->images as $image)
                <div class="image-edit-card">
                    <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?: $post->title }}">

                    <div class="image-edit-card__body">
                        <label>
                            Tekst alternatywny
                            <input
                                id="post-image-{{ $image->id }}-alt"
                                type="text"
                                name="existing_images[{{ $image->id }}][alt_text]"
                                value="{{ old("existing_images.{$image->id}.alt_text", $image->alt_text) }}"
                                autocomplete="off"
                                @error("existing_images.{$image->id}.alt_text") aria-invalid="true" aria-describedby="post-image-{{ $image->id }}-alt-error" @enderror
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
                                @error("existing_images.{$image->id}.caption") aria-invalid="true" aria-describedby="post-image-{{ $image->id }}-caption-error" @enderror
                            >{{ old("existing_images.{$image->id}.caption", $image->caption) }}</textarea>
                            @error("existing_images.{$image->id}.caption")
                                <span id="post-image-{{ $image->id }}-caption-error" class="form-error">{{ $message }}</span>
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
                                @error("existing_images.{$image->id}.sort_order") aria-invalid="true" aria-describedby="post-image-{{ $image->id }}-order-error" @enderror
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

<div class="admin-form-actions">
    <button type="submit" class="btn btn-primary">Zapisz aktualność</button>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
