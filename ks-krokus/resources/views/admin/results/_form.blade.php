@php
    $currentEventCompetition = old(
        'event_competition_id',
        $result->event_competition_id ?? $selectedEventCompetitionId ?? '',
    );

    $currentUserId = old('user_id', $result->user_id ?? '');
    $currentStatus = old(
        'status',
        isset($result) ? $result->status->value : \App\Enums\ResultStatus::Official->value,
    );
@endphp

<div class="admin-form-grid">
    <h2 class="admin-section-title span-full">Wydarzenie i zawodnik</h2>
    <label class="span-full">
        Wydarzenie i konkurencja
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

    <label>
        Powiązany użytkownik
        <select id="result-user" name="user_id" data-result-user aria-controls="result-participant-name"
            aria-describedby="result-user-help @error('user_id') result-user-error @enderror"
            @error('user_id') aria-invalid="true" @enderror>
            <option value="">Zawodnik zewnętrzny / bez konta</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((string) $currentUserId === (string) $user->id)>
                    {{ $user->name }} — {{ $user->email }}{{ $user->trashed() ? ' — konto usunięte' : '' }}
                </option>
            @endforeach
        </select>
        <span id="result-user-help" class="form-help">Przy wybranym użytkowniku system automatycznie zapisze jego aktualne imię i nazwisko.</span>
        @error('user_id') <span id="result-user-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Imię i nazwisko zawodnika zewnętrznego
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
        <span id="result-participant-help" class="form-help">Wymagane tylko wtedy, gdy nie wybierzesz użytkownika.</span>
        @error('participant_name') <span id="result-participant-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <h2 class="admin-section-title span-full">Wynik i klasyfikacja</h2>

    <label>
        Klub
        <input id="result-club" type="text" name="club_name" value="{{ old('club_name', $result->club_name ?? '') }}" autocomplete="organization"
            @error('club_name') aria-invalid="true" aria-describedby="result-club-error" @enderror>
        @error('club_name') <span id="result-club-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Kategoria
        <input id="result-category" type="text" name="category" value="{{ old('category', $result->category ?? '') }}" placeholder="np. Senior, Lady, Junior" autocomplete="off"
            @error('category') aria-invalid="true" aria-describedby="result-category-error" @enderror>
        @error('category') <span id="result-category-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Wynik
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
        Klasyfikacja
        <input
            type="text"
            id="result-classification"
            name="classification"
            value="{{ old('classification', $result->classification ?? '') }}"
            placeholder="np. Open, Production, Standard"
            autocomplete="off"
            @error('classification') aria-invalid="true" aria-describedby="result-classification-error" @enderror
        >
        @error('classification') <span id="result-classification-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Status
        <select id="result-status" name="status" required @error('status') aria-invalid="true" aria-describedby="result-status-error" @enderror>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <span id="result-status-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="span-full">
        Uwagi
        <textarea id="result-notes" name="notes" autocomplete="off" @error('notes') aria-invalid="true" aria-describedby="result-notes-error" @enderror>{{ old('notes', $result->notes ?? '') }}</textarea>
        @error('notes') <span id="result-notes-error" class="form-error">{{ $message }}</span> @enderror
    </label>
</div>

<div class="admin-form-actions">
    <button type="submit" class="btn btn-primary" @disabled($eventCompetitions->isEmpty())
        @if ($eventCompetitions->isEmpty()) aria-describedby="result-event-competition-empty" @endif>
        Zapisz wynik
    </button>
    <a href="{{ route('admin.results.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
