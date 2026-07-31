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
@endphp

<div class="admin-form-grid">
    <div class="span-full"><h3 class="admin-section-title">Treść aktualności</h3></div>
    <label class="span-full">
        Tytuł
        <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" autocomplete="off" required autofocus>
        @error('title') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="span-full">
        Krótkie streszczenie
        <textarea name="excerpt" rows="3">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
        <span class="form-help">Widoczne na listach aktualności i jako wprowadzenie do artykułu.</span>
        @error('excerpt') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="span-full">
        Treść
        <input type="hidden" name="content_format" value="html">
        <textarea
            name="content"
            rows="16"
            required
            data-rich-text
            @error('content') aria-invalid="true" aria-describedby="post-content-error" @enderror
        >{{ old('content', $post->content ?? '') }}</textarea>
        <span class="form-help">
            Użyj paska narzędzi do formatowania nagłówków, list i wyróżnień.
        </span>
        @error('content') <span id="post-content-error" class="form-error" role="alert">{{ $message }}</span> @enderror
    </label>

    <div class="span-full">
        <h3 class="admin-section-title">Publikacja</h3>
    </div>

    <label>
        Status
        <select name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Data publikacji
        <input type="datetime-local" name="published_at" value="{{ $publishedAt }}">
        <span class="form-help">Przy statusie „Opublikowane” puste pole zostanie ustawione na bieżący czas.</span>
        @error('published_at') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <div class="span-full">
        <h3 class="admin-section-title">Zdjęcie główne</h3>
    </div>

    @if (isset($post) && $post->coverUrl())
        <div class="span-full image-edit-card">
            <img src="{{ $post->coverUrl() }}" alt="{{ $post->cover_image_alt ?: $post->title }}">
            <div class="image-edit-card__body">
                <label class="form-check">
                    <input type="checkbox" name="remove_cover" value="1">
                    Usuń obecne zdjęcie główne
                </label>
            </div>
        </div>
    @endif

    <div class="file-upload" data-file-upload>
        <label class="file-upload__dropzone">
            <strong>Nowe zdjęcie główne</strong>
            <span>Przeciągnij obraz tutaj lub wybierz plik</span>
            <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"
                @error('cover_image') aria-invalid="true" aria-describedby="cover-image-error" @enderror>
            <small>JPG, PNG lub WebP, maksymalnie 6 MB.</small>
            @error('cover_image') <span id="cover-image-error" class="form-error" role="alert">{{ $message }}</span> @enderror
        </label>
        <div class="file-preview-list" data-file-preview aria-live="polite"></div>
    </div>

    <label>
        Tekst alternatywny zdjęcia
        <input
            type="text"
            name="cover_image_alt"
            value="{{ old('cover_image_alt', $post->cover_image_alt ?? '') }}"
        >
        <span class="form-help">Krótki opis dla dostępności i wyszukiwarek.</span>
        @error('cover_image_alt') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <div class="span-full">
        <h3 class="admin-section-title">Galeria zdjęć</h3>
    </div>

    <div class="file-upload span-full" data-file-upload>
        <label class="file-upload__dropzone">
            <strong>Dodaj zdjęcia do galerii</strong>
            <span>Przeciągnij obrazy tutaj lub wybierz pliki</span>
            <input
                type="file"
                name="gallery_images[]"
                accept="image/jpeg,image/png,image/webp"
                multiple
                @if ($errors->has('gallery_images') || $errors->has('gallery_images.*'))
                    aria-invalid="true" aria-describedby="gallery-images-error"
                @endif
            >
            <small>Łącznie maksymalnie 12 zdjęć, każde do 6 MB.</small>
            @error('gallery_images') <span id="gallery-images-error" class="form-error" role="alert">{{ $message }}</span> @enderror
            @error('gallery_images.*') <span id="gallery-images-error" class="form-error" role="alert">{{ $message }}</span> @enderror
        </label>
        <div class="file-preview-list" data-file-preview aria-live="polite"></div>
    </div>

    @if (isset($post) && $post->images->isNotEmpty())
        <div class="image-preview-grid span-full">
            @foreach ($post->images as $image)
                <article class="image-edit-card">
                    <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?: $post->title }}">

                    <div class="image-edit-card__body">
                        <label>
                            Tekst alternatywny
                            <input
                                type="text"
                                name="existing_images[{{ $image->id }}][alt_text]"
                                value="{{ old("existing_images.{$image->id}.alt_text", $image->alt_text) }}"
                            >
                            @error("existing_images.{$image->id}.alt_text")
                                <span class="form-error" role="alert">{{ $message }}</span>
                            @enderror
                        </label>

                        <label>
                            Podpis
                            <textarea
                                name="existing_images[{{ $image->id }}][caption]"
                                rows="3"
                            >{{ old("existing_images.{$image->id}.caption", $image->caption) }}</textarea>
                            @error("existing_images.{$image->id}.caption")
                                <span class="form-error" role="alert">{{ $message }}</span>
                            @enderror
                        </label>

                        <label>
                            Kolejność
                            <input
                                type="number"
                                name="existing_images[{{ $image->id }}][sort_order]"
                                min="0"
                                value="{{ old("existing_images.{$image->id}.sort_order", $image->sort_order) }}"
                            >
                            @error("existing_images.{$image->id}.sort_order")
                                <span class="form-error" role="alert">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="form-check">
                            <input type="checkbox" name="delete_images[]" value="{{ $image->id }}">
                            Usuń zdjęcie
                        </label>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

<div class="admin-form-actions">
    <button type="submit" class="btn btn-primary">Zapisz aktualność</button>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
