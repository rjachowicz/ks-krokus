<div class="sidebar-layout event-detail-grid" data-event-details>
    <article class="panel-card event-panel">
        <h2>Opis wydarzenia</h2>

        <div class="article-body">
            {!! nl2br(e($sportEvent->description ?: 'Szczegółowy opis nie został jeszcze opublikowany.')) !!}
        </div>

        <h3>Konkurencje</h3>

        @if ($sportEvent->competitions->isNotEmpty())
            <ul class="competition-list">
                @foreach ($sportEvent->competitions as $competition)
                    <li>
                        <strong>{{ $competition->name }}</strong><br>
                        {{ $competition->competition_system->label() }} /
                        {{ $competition->discipline->label() }}
                    </li>
                @endforeach
            </ul>
        @else
            <p class="event-detail-empty">Nie przypisano konkurencji do tego wydarzenia.</p>
        @endif
    </article>

    <aside class="panel-card event-panel">
        <h2>Informacje</h2>

        <dl class="event-facts">
            <div>
                <dt>Rodzaj</dt>
                <dd>{{ $sportEvent->event_type->label() }}</dd>
            </div>
            <div>
                <dt>Rozpoczęcie</dt>
                <dd>
                    <time datetime="{{ $sportEvent->start_at->toIso8601String() }}">
                        {{ $sportEvent->start_at->format('d.m.Y H:i') }}
                    </time>
                </dd>
            </div>
            <div>
                <dt>Zakończenie</dt>
                <dd>
                    @if ($sportEvent->end_at)
                        <time datetime="{{ $sportEvent->end_at->toIso8601String() }}">
                            {{ $sportEvent->end_at->format('d.m.Y H:i') }}
                        </time>
                    @else
                        Nie podano
                    @endif
                </dd>
            </div>
            <div>
                <dt>Miejsce</dt>
                <dd>{{ $sportEvent->location_name ?: 'Nie podano' }}</dd>
            </div>
            <div>
                <dt>Adres</dt>
                <dd>{{ $sportEvent->address ?: 'Nie podano' }}</dd>
            </div>
            <div>
                <dt>Dyscyplina</dt>
                <dd>{{ $sportEvent->discipline?->label() ?: 'Nie określono' }}</dd>
            </div>
            <div>
                <dt>System zawodów</dt>
                <dd>{{ $sportEvent->competition_system?->label() ?: 'Nie określono' }}</dd>
            </div>
        </dl>

        @if ($sportEvent->registration_url)
            <div class="btn-group content-actions">
                <a
                    href="{{ $sportEvent->registration_url }}"
                    class="btn btn-primary"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Przejdź do rejestracji — otwiera w nowej karcie"
                >
                    Przejdź do rejestracji ↗
                </a>
            </div>
        @endif

        @php($eventReminderId = 'event-reminder-'.$sportEvent->getKey())
        <section class="event-reminder" aria-labelledby="{{ $eventReminderId }}-title" data-event-reminder>
            <h3 id="{{ $eventReminderId }}-title">Przypomnienie e-mail</h3>

            @if ($reminderSubscribed)
                <p>Przypomnienie jest ustawione. Wiadomość zostanie wysłana około 24 godziny przed rozpoczęciem wydarzenia.</p>
                <form method="POST" action="{{ route('event-reminders.destroy', $sportEvent) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-secondary">Anuluj przypomnienie</button>
                </form>
            @elseif (! $sportEvent->email_reminders_enabled)
                <p>Organizator nie włączył e-mailowych przypomnień dla tego wydarzenia.</p>
            @elseif (! $eventReminderCanSubscribe)
                <p>Zapisy na przypomnienie są zamknięte dokładnie 24 godziny przed rozpoczęciem wydarzenia.</p>
            @elseif (auth()->guest())
                <p>Zaloguj się na aktywne konto, aby ustawić przypomnienie o wybranym wydarzeniu.</p>
                <a href="{{ route('login') }}" class="btn btn-secondary">Zaloguj się</a>
            @elseif ($reminderAccountActive)
                <p>Wiadomość zostanie wysłana na adres e-mail przypisany do Twojego konta.</p>
                <button
                    type="button"
                    class="btn btn-primary"
                    aria-haspopup="dialog"
                    aria-controls="{{ $eventReminderId }}-dialog"
                    data-event-reminder-open
                >Przypomnij mi o wydarzeniu</button>

                <dialog
                    id="{{ $eventReminderId }}-dialog"
                    class="event-reminder-dialog"
                    aria-labelledby="{{ $eventReminderId }}-dialog-title"
                    aria-describedby="{{ $eventReminderId }}-dialog-description"
                    data-event-reminder-dialog
                    @if ($errors->has('event_reminder_current_password') || $errors->has('event_reminder')) data-open-on-load @endif
                >
                    <form method="POST" action="{{ route('event-reminders.store', $sportEvent) }}" class="form-layout">
                        @csrf
                        <h2 id="{{ $eventReminderId }}-dialog-title">Potwierdź przypomnienie</h2>
                        <p id="{{ $eventReminderId }}-dialog-description">
                            Potwierdzenie hasłem wyklucza przypadkowe zapisanie konta na listę przypomnień.
                        </p>
                        @unless (auth()->user()->event_email_notifications_enabled)
                            <p class="form-help">Ta operacja włączy również globalną zgodę na e-mailowe przypomnienia o wybranych wydarzeniach.</p>
                        @endunless

                        @error('event_reminder')
                            <p class="form-error" role="alert">{{ $message }}</p>
                        @enderror

                        <label>
                            <span class="form-label-text">
                                Aktualne hasło
                                <span class="form-required" aria-hidden="true">*</span>
                                <span class="sr-only">(pole wymagane)</span>
                            </span>
                            <input
                                type="password"
                                name="event_reminder_current_password"
                                required
                                autocomplete="current-password"
                                aria-describedby="{{ $eventReminderId }}-password-help @error('event_reminder_current_password') {{ $eventReminderId }}-password-error @enderror"
                                @error('event_reminder_current_password') aria-invalid="true" @enderror
                            >
                            <span id="{{ $eventReminderId }}-password-help" class="form-help">Hasło służy tylko do potwierdzenia tej operacji.</span>
                            @error('event_reminder_current_password')
                                <span id="{{ $eventReminderId }}-password-error" class="form-error" role="alert">{{ $message }}</span>
                            @enderror
                        </label>

                        <div class="event-reminder-dialog__actions">
                            <button type="button" class="btn btn-secondary" data-event-reminder-close>Anuluj</button>
                            <button type="submit" class="btn btn-primary">Przypomnij mi o wydarzeniu</button>
                        </div>
                    </form>
                </dialog>
            @else
                <p>Twoje konto musi być aktywne, aby ustawić przypomnienie.</p>
            @endif
        </section>
    </aside>
</div>
