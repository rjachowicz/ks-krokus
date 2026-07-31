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
    <div class="span-full"><h3 class="admin-section-title">Zawodnik i rezultat</h3></div>
    <label class="span-full">
        Wydarzenie i konkurencja
        <select name="event_competition_id" required>
            <option value="">Wybierz</option>
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
        @error('event_competition_id') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Powiązany użytkownik
        <select name="user_id">
            <option value="">Zawodnik zewnętrzny / bez konta</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((string) $currentUserId === (string) $user->id)>
                    {{ $user->name }} — {{ $user->email }}
                </option>
            @endforeach
        </select>
        <span class="form-help">Przy wybranym użytkowniku system automatycznie zapisze jego aktualne imię i nazwisko.</span>
        @error('user_id') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Imię i nazwisko zawodnika zewnętrznego
        <input
            type="text"
            name="participant_name"
            value="{{ old('participant_name', $result->participant_name ?? '') }}"
        >
        <span class="form-help">Wymagane tylko wtedy, gdy nie wybierzesz użytkownika.</span>
        @error('participant_name') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Klub
        <input type="text" name="club_name" value="{{ old('club_name', $result->club_name ?? '') }}">
        @error('club_name') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Kategoria
        <input type="text" name="category" value="{{ old('category', $result->category ?? '') }}" placeholder="np. Senior, Lady, Junior">
        @error('category') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Wynik
        <input type="text" name="score" value="{{ old('score', $result->score ?? '') }}" required placeholder="np. 245.14, 89%, DNF">
        @error('score') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Miejsce
        <input type="number" name="place" min="1" value="{{ old('place', $result->place ?? '') }}">
        @error('place') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Klasyfikacja
        <input
            type="text"
            name="classification"
            value="{{ old('classification', $result->classification ?? '') }}"
            placeholder="np. Open, Production, Standard"
        >
        @error('classification') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Status
        <select name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="span-full">
        Uwagi
        <textarea name="notes">{{ old('notes', $result->notes ?? '') }}</textarea>
        @error('notes') <span class="form-error">{{ $message }}</span> @enderror
    </label>
</div>

<div class="admin-actions">
    <button type="submit" class="btn btn-primary">Zapisz wynik</button>
    <a href="{{ route('admin.results.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
