@php
    $selectedCompetitionIds = old(
        'competition_ids',
        isset($event) ? $event->competitions->pluck('id')->all() : [],
    );
    $selectedCompetitionIds = is_array($selectedCompetitionIds)
        ? array_map('intval', $selectedCompetitionIds)
        : [];

    $competitionErrorIds = implode(' ', array_filter([
        $errors->has('competition_ids') ? 'event-competitions-error' : null,
        $errors->has('competition_ids.*') ? 'event-competition-items-error' : null,
    ]));

    $currentType = old(
        'event_type',
        isset($event) ? $event->event_type->value : \App\Enums\EventType::Competition->value,
    );

    $currentStatus = old(
        'status',
        isset($event) ? $event->status->value : \App\Enums\PublicationStatus::Draft->value,
    );

    $currentDiscipline = old(
        'discipline',
        isset($event) ? $event->discipline?->value : '',
    );

    $currentSystem = old(
        'competition_system',
        isset($event) ? $event->competition_system?->value : '',
    );

    $startAt = old(
        'start_at',
        isset($event) ? $event->start_at->format('Y-m-d\TH:i') : '',
    );
    $endAt = old(
        'end_at',
        isset($event) && $event->end_at ? $event->end_at->format('Y-m-d\TH:i') : '',
    );
@endphp

<div class="form-grid form-grid--3">
    <h2 class="admin-section-title form-grid--span-full">Podstawowe informacje</h2>
    <label class="form-grid--span-full">
        <span class="form-label-text">
            Nazwa wydarzenia <span class="form-required" aria-hidden="true">*</span>
            <span class="sr-only">(pole wymagane)</span>
        </span>
        <input id="event-title" type="text" name="title" value="{{ old('title', $event->title ?? '') }}" autocomplete="off" required autofocus
            @error('title') aria-invalid="true" aria-describedby="event-title-error" @enderror>
        @error('title') <span id="event-title-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        <span class="form-label-text">
            Rodzaj <span class="form-required" aria-hidden="true">*</span>
            <span class="sr-only">(pole wymagane)</span>
        </span>
        <select id="event-type" name="event_type" required @error('event_type') aria-invalid="true" aria-describedby="event-type-error" @enderror>
            @foreach ($eventTypes as $value => $label)
                <option value="{{ $value }}" @selected($currentType === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('event_type') <span id="event-type-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        <span class="form-label-text">
            Status publikacji <span class="form-required" aria-hidden="true">*</span>
            <span class="sr-only">(pole wymagane)</span>
        </span>
        <select id="event-status" name="status" required @error('status') aria-invalid="true" aria-describedby="event-status-error" @enderror>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <span id="event-status-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="is_public" value="0">
        <input id="event-public" type="checkbox" name="is_public" value="1" @checked(old('is_public', $event->is_public ?? true))
            @error('is_public') aria-invalid="true" aria-describedby="event-public-error" @enderror>
        <span>
            <strong>Wydarzenie publiczne</strong><br>
            Widoczne w kalendarzu po ustawieniu statusu „Opublikowane”.
        </span>
        @error('is_public') <span id="event-public-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <h2 class="admin-section-title form-grid--span-full">Termin i zapisy</h2>

    <label>
        <span class="form-label-text">
            Początek <span class="form-required" aria-hidden="true">*</span>
            <span class="sr-only">(pole wymagane)</span>
        </span>
        <input
            type="datetime-local"
            id="event-start-at"
            name="start_at"
            value="{{ $startAt }}"
            data-event-start
            required
            @error('start_at') aria-invalid="true" aria-describedby="event-start-at-error" @enderror
        >
        @error('start_at') <span id="event-start-at-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Koniec
        <input
            type="datetime-local"
            id="event-end-at"
            name="end_at"
            value="{{ $endAt }}"
            min="{{ $startAt }}"
            data-event-end
            @error('end_at') aria-invalid="true" aria-describedby="event-end-at-error" @enderror
        >
        @error('end_at') <span id="event-end-at-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Link do rejestracji
        <input
            type="url"
            id="event-registration-url"
            name="registration_url"
            value="{{ old('registration_url', $event->registration_url ?? '') }}"
            placeholder="https://..."
            autocomplete="url"
            aria-describedby="event-registration-url-help @error('registration_url') event-registration-url-error @enderror"
            @error('registration_url') aria-invalid="true" @enderror
        >
        <span id="event-registration-url-help" class="form-help">Podaj adres internetowy zaczynający się od http:// lub https://.</span>
        @error('registration_url') <span id="event-registration-url-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <h2 class="admin-section-title form-grid--span-full">Miejsce i klasyfikacja</h2>

    <label>
        <span class="form-label-text">
            Nazwa miejsca <span class="form-required" aria-hidden="true">*</span>
            <span class="sr-only">(pole wymagane)</span>
        </span>
        <input id="event-location" type="text" name="location_name" value="{{ old('location_name', $event->location_name ?? '') }}" autocomplete="organization" required
            @error('location_name') aria-invalid="true" aria-describedby="event-location-error" @enderror>
        @error('location_name') <span id="event-location-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="form-grid--span-2">
        Adres
        <input id="event-address" type="text" name="address" value="{{ old('address', $event->address ?? '') }}" autocomplete="street-address"
            @error('address') aria-invalid="true" aria-describedby="event-address-error" @enderror>
        @error('address') <span id="event-address-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Główna dyscyplina
        <select id="event-discipline" name="discipline" @error('discipline') aria-invalid="true" aria-describedby="event-discipline-error" @enderror>
            <option value="">Wydarzenie mieszane</option>
            @foreach ($disciplines as $value => $label)
                <option value="{{ $value }}" @selected($currentDiscipline === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('discipline') <span id="event-discipline-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Główny system
        <select id="event-system" name="competition_system" @error('competition_system') aria-invalid="true" aria-describedby="event-system-error" @enderror>
            <option value="">Wydarzenie mieszane</option>
            @foreach ($systems as $value => $label)
                <option value="{{ $value }}" @selected($currentSystem === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('competition_system') <span id="event-system-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="form-grid--span-full">
        Opis
        <textarea id="event-description" name="description" rows="10" autocomplete="off"
            @error('description') aria-invalid="true" aria-describedby="event-description-error" @enderror>{{ old('description', $event->description ?? '') }}</textarea>
        @error('description') <span id="event-description-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <fieldset class="admin-choice-section form-grid--span-full"
        @if ($competitionErrorIds !== '') aria-invalid="true" aria-describedby="{{ $competitionErrorIds }}" @endif>
        <legend class="admin-section-title">Konkurencje wydarzenia</legend>
        <p class="form-help">
            Dla treningów możesz wybrać ćwiczone konkurencje. Dla zawodów wybór definiuje pozycje dostępne
            przy dodawaniu wyników.
        </p>
        @error('competition_ids') <span id="event-competitions-error" class="form-error" role="alert">{{ $message }}</span> @enderror
        @error('competition_ids.*') <span id="event-competition-items-error" class="form-error" role="alert">{{ $message }}</span> @enderror

        <div class="admin-check-grid">
            @forelse ($competitionDefinitions as $group => $definitions)
                <fieldset class="admin-option-group">
                    <legend>{{ $group }}</legend>

                    @foreach ($definitions as $definition)
                        <label class="admin-check-option">
                            <input
                                type="checkbox"
                                name="competition_ids[]"
                                value="{{ $definition->id }}"
                                @checked(in_array($definition->id, $selectedCompetitionIds, true))
                                @if ($competitionErrorIds !== '')
                                    aria-invalid="true" aria-describedby="{{ $competitionErrorIds }}"
                                @endif
                            >
                            <span>
                                <strong>{{ $definition->name }}</strong><br>
                                <code>{{ $definition->code }}</code>
                            </span>
                        </label>
                    @endforeach
                </fieldset>
            @empty
                <p class="empty-state admin-choice-empty">
                    Brak aktywnych konkurencji do wyboru.
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.competitions.create') }}">Dodaj pierwszą konkurencję</a>.
                    @else
                        Poproś administratora o uzupełnienie słownika konkurencji.
                    @endif
                </p>
            @endforelse
        </div>
    </fieldset>
</div>

<div class="form-actions form-actions--sticky">
    <button type="submit" class="btn btn-primary">Zapisz wydarzenie</button>
    <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
