@php
    $listing = $listing ?? null;
    $isOwnerForm = $formContext === 'owner';
    $existingImages = $listing?->images ?? collect();
    $selectedDeletes = array_map('intval', (array) old('delete_images', []));
    $fieldValue = static fn (string $name, mixed $fallback = ''): mixed => old($name, data_get($listing, $name, $fallback));
@endphp

<section class="form-section" aria-labelledby="listing-basic-heading">
    <header class="form-section__header">
        <span class="form-section__number" aria-hidden="true">01</span>
        <h2 id="listing-basic-heading">Podstawowe informacje</h2>
        <p>Krótko nazwij przedmiot i przypisz go do właściwej kategorii.</p>
    </header>
    <div class="form-grid">
        <label class="form-grid--full">
            Tytuł ogłoszenia <span aria-hidden="true">*</span>
            <input id="listing-title" name="title" type="text" maxlength="255" required value="{{ $fieldValue('title') }}"
                placeholder="np. Pistolet sportowy z kaburą" autocomplete="off"
                @error('title') aria-invalid="true" aria-describedby="listing-title-error" @enderror>
            <span class="form-help">Nazwij przedmiot konkretnie; unikaj samych wielkich liter.</span>
            @error('title') <span id="listing-title-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <label>
            Kategoria <span aria-hidden="true">*</span>
            <select id="listing-category" name="category" required @error('category') aria-invalid="true" aria-describedby="listing-category-error" @enderror>
                <option value="">Wybierz kategorię</option>
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}" @selected($fieldValue('category') instanceof \BackedEnum ? $fieldValue('category')->value === $value : $fieldValue('category') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('category') <span id="listing-category-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <label>
            Rodzaj
            <select id="listing-firearm-type" name="firearm_type" @error('firearm_type') aria-invalid="true" aria-describedby="listing-firearm-type-error" @enderror>
                <option value="">Nie dotyczy / nie podano</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}" @selected($fieldValue('firearm_type') instanceof \BackedEnum ? $fieldValue('firearm_type')->value === $value : $fieldValue('firearm_type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('firearm_type') <span id="listing-firearm-type-error" class="form-error">{{ $message }}</span> @enderror
        </label>
    </div>
</section>

<section class="form-section" aria-labelledby="listing-parameters-heading">
    <header class="form-section__header">
        <span class="form-section__number" aria-hidden="true">02</span>
        <h2 id="listing-parameters-heading">Parametry</h2>
        <p>Uzupełnij tylko dane, które dotyczą oferowanego przedmiotu.</p>
    </header>
    <div class="form-grid">
        @foreach ([
            ['manufacturer', 'Producent', 'np. CZ'],
            ['model', 'Model', 'np. Shadow 2'],
            ['caliber', 'Kaliber', 'np. 9×19 mm'],
        ] as [$name, $label, $placeholder])
            <label>
                {{ $label }}
                <input id="listing-{{ str_replace('_', '-', $name) }}" name="{{ $name }}" type="text" maxlength="{{ $name === 'caliber' ? 64 : 120 }}"
                    value="{{ $fieldValue($name) }}" placeholder="{{ $placeholder }}" autocomplete="off"
                    @error($name) aria-invalid="true" aria-describedby="listing-{{ str_replace('_', '-', $name) }}-error" @enderror>
                @error($name) <span id="listing-{{ str_replace('_', '-', $name) }}-error" class="form-error">{{ $message }}</span> @enderror
            </label>
        @endforeach

        <label>
            Stan
            <select id="listing-condition" name="condition" @error('condition') aria-invalid="true" aria-describedby="listing-condition-error" @enderror>
                <option value="">Nie podano</option>
                @foreach ($conditions as $value => $label)
                    <option value="{{ $value }}" @selected($fieldValue('condition') instanceof \BackedEnum ? $fieldValue('condition')->value === $value : $fieldValue('condition') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('condition') <span id="listing-condition-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <label>
            Rok produkcji
            <input id="listing-year" name="year_of_manufacture" type="number" min="1800" max="{{ now()->year }}" value="{{ $fieldValue('year_of_manufacture') }}"
                @error('year_of_manufacture') aria-invalid="true" aria-describedby="listing-year-error" @enderror>
            @error('year_of_manufacture') <span id="listing-year-error" class="form-error">{{ $message }}</span> @enderror
        </label>
    </div>
</section>

<section class="form-section" aria-labelledby="listing-price-heading">
    <header class="form-section__header">
        <span class="form-section__number" aria-hidden="true">03</span>
        <h2 id="listing-price-heading">Cena</h2>
        <p>Podaj oczekiwaną kwotę albo pozostaw pole puste, jeśli cena wymaga ustalenia.</p>
    </header>
    <div class="form-grid form-grid--price">
        <label>
            Cena w zł
            <input id="listing-price" name="price" type="number" min="0" max="9999999999.99" step="0.01" inputmode="decimal" value="{{ $fieldValue('price') }}"
                placeholder="np. 2500,00" @error('price') aria-invalid="true" aria-describedby="listing-price-error" @enderror>
            @error('price') <span id="listing-price-error" class="form-error">{{ $message }}</span> @enderror
        </label>
        <label class="form-switch">
            <input type="checkbox" name="price_negotiable" value="1" @checked(old('price_negotiable', $listing?->price_negotiable ?? false))>
            <span>Cena do negocjacji</span>
        </label>
    </div>
</section>

<section class="form-section" aria-labelledby="listing-description-heading">
    <header class="form-section__header">
        <span class="form-section__number" aria-hidden="true">04</span>
        <h2 id="listing-description-heading">Opis</h2>
        <p>Przedstaw stan, historię i wyposażenie zestawu w czytelnej formie.</p>
    </header>
    <label>
        Pełny opis <span aria-hidden="true">*</span>
        <textarea id="listing-description" name="description" rows="10" minlength="30" maxlength="20000" required
            placeholder="Opisz historię, stan techniczny, przebieg, wyposażenie zestawu i zauważone ślady użytkowania. Nie wpisuj kodu HTML."
            @error('description') aria-invalid="true" aria-describedby="listing-description-error" @enderror>{{ $fieldValue('description') }}</textarea>
        <span class="form-help">Minimum 30 znaków. Treść jest publikowana jako zwykły tekst.</span>
        @error('description') <span id="listing-description-error" class="form-error">{{ $message }}</span> @enderror
    </label>
</section>

<section class="form-section" aria-labelledby="listing-images-heading">
    <header class="form-section__header">
        <span class="form-section__number" aria-hidden="true">05</span>
        <h2 id="listing-images-heading">Zdjęcia</h2>
        <p>Dodaj czytelne fotografie i ustaw kolejność, w której zobaczą je użytkownicy.</p>
    </header>

    <div class="file-upload" data-listing-images data-max-files="{{ $maxImages }}" data-max-size-kb="{{ $maxImageSizeKb }}" data-existing-files="{{ $existingImages->count() }}">
        <label for="listing-images" class="file-upload__dropzone">
            <span class="file-upload__icon" aria-hidden="true">＋</span>
            <strong>Przeciągnij zdjęcia tutaj lub wybierz pliki</strong>
            <span>JPG, PNG lub WebP · maks. {{ (int) ($maxImageSizeKb / 1024) }} MB na zdjęcie · do {{ $maxImages }} plików</span>
            <small>Po dodaniu możesz zmienić kolejność, opisy i zdjęcie główne.</small>
        </label>
        <input id="listing-images" name="images[]" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple
            aria-describedby="listing-images-help @if ($errors->has('images') || $errors->has('images.*')) listing-images-error @endif"
            @if ($errors->has('images') || $errors->has('images.*')) aria-invalid="true" @endif>
        <span id="listing-images-help" class="form-help">Pierwsze zdjęcie zostanie główne, jeśli nie wskażesz innego.</span>
        <div class="listing-upload-preview" data-listing-image-preview aria-live="polite"></div>
        @if ($errors->has('images') || $errors->has('images.*'))
            <span id="listing-images-error" class="form-error">{{ $errors->first('images') ?: $errors->first('images.*') }}</span>
        @endif
        <span class="form-error" data-listing-image-error hidden></span>
    </div>

    @if ($existingImages->isNotEmpty())
        <fieldset class="listing-existing-images">
            <legend>Zapisane zdjęcia</legend>
            <div class="listing-existing-images__grid">
                @foreach ($existingImages as $image)
                    <article class="listing-image-editor">
                        <div class="listing-image-editor__preview">
                            <img src="{{ $image->thumbnailUrl() }}" alt="{{ $image->alt_text ?: $listing->title }}">
                            @if ($image->is_primary)<span class="listing-image-primary">Zdjęcie główne</span>@endif
                        </div>
                        <div class="listing-image-editor__fields">
                            <label class="form-check listing-image-editor__primary">
                                <input type="radio" name="primary_image_id" value="{{ $image->id }}" @checked((int) old('primary_image_id', $existingImages->firstWhere('is_primary', true)?->id) === $image->id)>
                                Ustaw jako zdjęcie główne
                            </label>
                            <label>
                                Tekst alternatywny
                                <input id="listing-image-{{ $image->id }}-alt" name="existing_images[{{ $image->id }}][alt_text]" type="text" maxlength="255" value="{{ old("existing_images.{$image->id}.alt_text", $image->alt_text) }}"
                                    @error("existing_images.{$image->id}.alt_text") aria-invalid="true" aria-describedby="listing-image-{{ $image->id }}-alt-error" @enderror>
                                @error("existing_images.{$image->id}.alt_text") <span id="listing-image-{{ $image->id }}-alt-error" class="form-error">{{ $message }}</span> @enderror
                            </label>
                            <label>
                                Podpis
                                <textarea id="listing-image-{{ $image->id }}-caption" name="existing_images[{{ $image->id }}][caption]" rows="2" maxlength="1000"
                                    @error("existing_images.{$image->id}.caption") aria-invalid="true" aria-describedby="listing-image-{{ $image->id }}-caption-error" @enderror>{{ old("existing_images.{$image->id}.caption", $image->caption) }}</textarea>
                                @error("existing_images.{$image->id}.caption") <span id="listing-image-{{ $image->id }}-caption-error" class="form-error">{{ $message }}</span> @enderror
                            </label>
                            <label>
                                Kolejność
                                <input id="listing-image-{{ $image->id }}-order" name="existing_images[{{ $image->id }}][sort_order]" type="number" min="0" max="9999" value="{{ old("existing_images.{$image->id}.sort_order", $image->sort_order) }}"
                                    @error("existing_images.{$image->id}.sort_order") aria-invalid="true" aria-describedby="listing-image-{{ $image->id }}-order-error" @enderror>
                                @error("existing_images.{$image->id}.sort_order") <span id="listing-image-{{ $image->id }}-order-error" class="form-error">{{ $message }}</span> @enderror
                            </label>
                            <label class="form-check listing-image-editor__delete">
                                <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" data-existing-listing-image-delete @checked(in_array($image->id, $selectedDeletes, true))>
                                Usuń zdjęcie po zapisaniu
                            </label>
                        </div>
                    </article>
                @endforeach
            </div>
            @error('existing_images') <span class="form-error">{{ $message }}</span> @enderror
            @error('delete_images.*') <span class="form-error">{{ $message }}</span> @enderror
        </fieldset>
    @endif
</section>

<section class="form-section" aria-labelledby="listing-contact-heading">
    <header class="form-section__header">
        <span class="form-section__number" aria-hidden="true">06</span>
        <h2 id="listing-contact-heading">Kontakt</h2>
        <p>Wybierz dane, które mogą zostać publicznie pokazane przy ogłoszeniu.</p>
    </header>
    <div class="form-grid">
        <label>
            Lokalizacja
            <input id="listing-location" name="location" type="text" maxlength="255" value="{{ $fieldValue('location') }}" placeholder="np. Nowy Sącz" autocomplete="address-level2"
                @error('location') aria-invalid="true" aria-describedby="listing-location-error" @enderror>
            @error('location') <span id="listing-location-error" class="form-error">{{ $message }}</span> @enderror
        </label>
        <label>
            Imię kontaktowe
            <input id="listing-contact-name" name="contact_name" type="text" maxlength="120" value="{{ $fieldValue('contact_name', auth()->user()->name) }}" autocomplete="name"
                @error('contact_name') aria-invalid="true" aria-describedby="listing-contact-name-error" @enderror>
            @error('contact_name') <span id="listing-contact-name-error" class="form-error">{{ $message }}</span> @enderror
        </label>
        <label>
            Telefon
            <input id="listing-contact-phone" name="contact_phone" type="tel" maxlength="32" value="{{ $fieldValue('contact_phone', auth()->user()->phone) }}" autocomplete="tel"
                @error('contact_phone') aria-invalid="true" aria-describedby="listing-contact-phone-error" @enderror>
            @error('contact_phone') <span id="listing-contact-phone-error" class="form-error">{{ $message }}</span> @enderror
        </label>
        <label>
            E-mail
            <input id="listing-contact-email" name="contact_email" type="email" maxlength="255" value="{{ $fieldValue('contact_email', auth()->user()->email) }}" autocomplete="email"
                @error('contact_email') aria-invalid="true" aria-describedby="listing-contact-email-error" @enderror>
            @error('contact_email') <span id="listing-contact-email-error" class="form-error">{{ $message }}</span> @enderror
        </label>
    </div>
    <div class="listing-consents">
        <label class="form-check">
            <input type="checkbox" name="show_phone" value="1" @checked(old('show_phone', $listing?->show_phone ?? false)) @error('show_phone') aria-invalid="true" aria-describedby="listing-show-phone-error" @enderror>
            Zgadzam się na publiczne pokazanie telefonu w tym ogłoszeniu
        </label>
        @error('show_phone') <span id="listing-show-phone-error" class="form-error">{{ $message }}</span> @enderror
        <label class="form-check">
            <input type="checkbox" name="show_email" value="1" @checked(old('show_email', $listing?->show_email ?? false)) @error('show_email') aria-invalid="true" aria-describedby="listing-show-email-error" @enderror>
            Zgadzam się na publiczne pokazanie adresu e-mail w tym ogłoszeniu
        </label>
        @error('show_email') <span id="listing-show-email-error" class="form-error">{{ $message }}</span> @enderror
    </div>
</section>

<section class="form-section listing-form-summary" aria-labelledby="listing-summary-heading">
    <header class="form-section__header">
        <span class="form-section__number" aria-hidden="true">07</span>
        <h2 id="listing-summary-heading">Podsumowanie</h2>
        <p>Sprawdź dane przed zapisaniem szkicu lub przekazaniem ogłoszenia do moderacji.</p>
    </header>
    <p>KS Krokus publikuje ogłoszenie informacyjnie i nie jest stroną transakcji. Za treść oraz zgodność oferty z prawem odpowiada autor.</p>

    <div class="form-actions">
        @if ($isOwnerForm)
            <div class="form-actions__secondary">
                <a href="{{ route('admin.my-listings.index') }}" class="btn btn-secondary">Anuluj</a>
                @if ($listing?->status !== \App\Enums\SaleListingStatus::Approved)
                    <button type="submit" name="intent" value="draft" class="btn btn-secondary">Zapisz bez wysyłania</button>
                @endif
            </div>
            <div class="form-actions__primary">
                @if ($listing?->status === \App\Enums\SaleListingStatus::Approved)
                    <button type="submit" name="intent" value="pending" class="btn btn-primary">Zapisz i wyślij ponownie do moderacji</button>
                @else
                    <button type="submit" name="intent" value="pending" class="btn btn-primary">Zapisz i wyślij do moderacji</button>
                @endif
            </div>
        @else
            <input type="hidden" name="intent" value="save">
            <div class="form-actions__secondary">
                <a href="{{ route('admin.sale-listings.index') }}" class="btn btn-secondary">Wróć do kolejki</a>
            </div>
            <div class="form-actions__primary">
                <button type="submit" class="btn btn-primary">Zapisz treść ogłoszenia</button>
            </div>
        @endif
    </div>
</section>
