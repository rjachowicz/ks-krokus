@php
    $currentEventCompetition = old(
        'event_competition_id',
        $result->event_competition_id ?? $selectedEventCompetitionId ?? '',
    );

    $currentUserId = old('user_id', $result->user_id ?? '');
    $selectedUserLabel = filled($currentUserId)
        ? ((isset($currentUser) && (string) $currentUser?->id === (string) $currentUserId)
            ? $currentUser->name.($currentUser->trashed() ? ' — konto usunięte' : '')
            : old('participant_name', $result->participant_name ?? ''))
        : '';
    $storedCategory = $result->category ?? null;
    $currentCategory = old(
        'category',
        \App\Enums\MemberAgeCategory::fromStoredValue($storedCategory)?->value ?? $storedCategory ?? '',
    );
    $historicalCategory = filled($storedCategory)
        && \App\Enums\MemberAgeCategory::fromStoredValue($storedCategory) === null;
    $storedClassification = $result->classification ?? null;
    $currentClassification = old(
        'classification',
        \App\Enums\IpscDivision::fromStoredValue($storedClassification)?->value ?? $storedClassification ?? '',
    );
    $historicalClassification = filled($storedClassification)
        && \App\Enums\IpscDivision::fromStoredValue($storedClassification) === null;
    $currentStatus = old(
        'status',
        isset($result) ? $result->status->value : \App\Enums\ResultStatus::Official->value,
    );
@endphp

<div class="form-grid">
    <h2 class="admin-section-title form-grid--span-full">Wydarzenie i zawodnik</h2>
    <label class="form-grid--span-full">
        <span class="form-label-text">
            Wydarzenie i konkurencja <span class="form-required" aria-hidden="true">*</span>
            <span class="sr-only">(pole wymagane)</span>
        </span>
        <select id="result-event-competition" name="event_competition_id" required autofocus
            @disabled($eventCompetitions->isEmpty())
            @if ($eventCompetitions->isEmpty() || $errors->has('event_competition_id'))
                aria-describedby="@if ($eventCompetitions->isEmpty()) result-event-competition-empty @endif @error('event_competition_id') result-event-competition-error @enderror"
            @endif
            @error('event_competition_id') aria-invalid="true" @enderror>
            <option value="">{{ $eventCompetitions->isEmpty() ? 'Brak dostępnych konkurencji zawodów' : 'Wybierz' }}</option>
            @foreach ($eventCompetitions as $eventCompetition)
                <option
                    value="{{ $eventCompetition->id }}"
                    @selected((string) $currentEventCompetition === (string) $eventCompetition->id)
                >
                    {{ $eventCompetition->event->start_at->format('d.m.Y') }} —
                    {{ $eventCompetition->event->title }} —
                    {{ $eventCompetition->competition->name }}
                </option>
            @endforeach
        </select>
        @error('event_competition_id') <span id="result-event-competition-error" class="form-error">{{ $message }}</span> @enderror
        @if ($eventCompetitions->isEmpty())
            <span id="result-event-competition-empty" class="form-help">
                Najpierw utwórz zawody i przypisz do nich co najmniej jedną aktywną konkurencję.
            </span>
        @endif
    </label>

    <div class="result-user-combobox" data-result-user-combobox data-endpoint="{{ route('admin.results.participants') }}">
        <label for="result-user-search">Powiązany użytkownik</label>
        <div class="result-user-combobox__control">
            <input
                id="result-user-search"
                type="search"
                value="{{ $selectedUserLabel }}"
                autocomplete="off"
                role="combobox"
                aria-autocomplete="list"
                aria-expanded="false"
                aria-controls="result-user-options"
                aria-describedby="result-user-help result-user-status @error('user_id') result-user-error @enderror"
                data-result-user-search
                @error('user_id') aria-invalid="true" @enderror
            >
            <button type="button" class="btn btn-secondary result-user-combobox__clear" data-result-user-clear @if (! filled($currentUserId)) hidden @endif>
                Usuń powiązanie
            </button>
        </div>
        <input type="hidden" id="result-user" name="user_id" value="{{ $currentUserId }}" data-result-user-id>
        <ul id="result-user-options" class="result-user-options" role="listbox" data-result-user-options hidden></ul>
        <span id="result-user-help" class="form-help">Wpisz co najmniej 2 znaki. Po wyborze zapisany zostanie snapshot imienia, klubu i kategorii.</span>
        @if (isset($currentUser) && $currentUser?->trashed())
            <span class="form-help">Powiązane konto usunięte — zachowano historyczny snapshot zawodnika.</span>
        @endif
        <span id="result-user-status" class="form-help" role="status" aria-live="polite" data-result-user-status></span>
        @error('user_id') <span id="result-user-error" class="form-error">{{ $message }}</span> @enderror
    </div>

    <label>
        Imię i nazwisko zawodnika
        <input
            type="text"
            id="result-participant-name"
            name="participant_name"
            value="{{ old('participant_name', $result->participant_name ?? '') }}"
            autocomplete="name"
            data-result-participant
            aria-describedby="result-participant-help @error('participant_name') result-participant-error @enderror"
            @error('participant_name') aria-invalid="true" @enderror
        >
        <span id="result-participant-help" class="form-help">Dla zawodnika zewnętrznego wpisz dane ręcznie. Przy powiązanym koncie snapshot ustala serwer.</span>
        @error('participant_name') <span id="result-participant-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <h2 class="admin-section-title form-grid--span-full">Wynik i klasyfikacja</h2>

    <label>
        Klub
        <input id="result-club" type="text" name="club_name" value="{{ old('club_name', $result->club_name ?? '') }}" autocomplete="organization" data-result-club
            @error('club_name') aria-invalid="true" aria-describedby="result-club-error" @enderror>
        @error('club_name') <span id="result-club-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Kategoria wiekowa
        <select id="result-category" name="category" data-result-category
            @error('category') aria-invalid="true" aria-describedby="result-category-error" @enderror>
            <option value="">Nie określono</option>
            @foreach ($ageCategories as $value => $label)
                <option value="{{ $value }}" @selected($currentCategory === $value)>{{ $label }}</option>
            @endforeach
            @if ($historicalCategory)
                <option value="{{ $storedCategory }}" @selected($currentCategory === $storedCategory)>Wartość historyczna: {{ $storedCategory }}</option>
            @endif
        </select>
        @error('category') <span id="result-category-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        <span class="form-label-text">
            Wynik <span class="form-required" aria-hidden="true">*</span>
            <span class="sr-only">(pole wymagane)</span>
        </span>
        <input id="result-score" type="text" name="score" value="{{ old('score', $result->score ?? '') }}" required placeholder="np. 245.14, 89%, DNF" autocomplete="off"
            @error('score') aria-invalid="true" aria-describedby="result-score-error" @enderror>
        @error('score') <span id="result-score-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Miejsce
        <input id="result-place" type="number" name="place" min="1" value="{{ old('place', $result->place ?? '') }}"
            @error('place') aria-invalid="true" aria-describedby="result-place-error" @enderror>
        @error('place') <span id="result-place-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Dywizja IPSC
        <select id="result-classification" name="classification"
            @error('classification') aria-invalid="true" aria-describedby="result-classification-error" @enderror>
            <option value="">Nie dotyczy / nie określono</option>
            @foreach ($ipscDivisions as $value => $label)
                <option value="{{ $value }}" @selected($currentClassification === $value)>{{ $label }}</option>
            @endforeach
            @if ($historicalClassification)
                <option value="{{ $storedClassification }}" @selected($currentClassification === $storedClassification)>Wartość historyczna: {{ $storedClassification }}</option>
            @endif
        </select>
        @error('classification') <span id="result-classification-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        <span class="form-label-text">
            Status <span class="form-required" aria-hidden="true">*</span>
            <span class="sr-only">(pole wymagane)</span>
        </span>
        <select id="result-status" name="status" required @error('status') aria-invalid="true" aria-describedby="result-status-error" @enderror>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <span id="result-status-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="form-grid--span-full">
        Uwagi
        <textarea id="result-notes" name="notes" autocomplete="off" @error('notes') aria-invalid="true" aria-describedby="result-notes-error" @enderror>{{ old('notes', $result->notes ?? '') }}</textarea>
        @error('notes') <span id="result-notes-error" class="form-error">{{ $message }}</span> @enderror
    </label>
</div>

<div class="form-actions form-actions--sticky">
    <button type="submit" class="btn btn-primary" @disabled($eventCompetitions->isEmpty())
        @if ($eventCompetitions->isEmpty()) aria-describedby="result-event-competition-empty" @endif>
        Zapisz wynik
    </button>
    <a href="{{ route('admin.results.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
