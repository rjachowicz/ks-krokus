@extends(request()->routeIs('admin.account.*') ? 'layouts.admin' : 'layouts.app')

@section('title', 'Moje konto — KS Krokus')
@section('admin_title', 'Moje konto')
@section('robots', 'noindex, nofollow, noarchive')
@section('body_class', 'account-page')

@section('content')
    @php($accountRoutePrefix = request()->routeIs('admin.account.*') ? 'admin.account' : 'account')

    @if (request()->routeIs('admin.account.*'))
        <x-admin-page-header
            title="Moje konto"
            description="Zarządzaj danymi kontaktowymi i bezpieczeństwem oraz sprawdź dane członkowskie zapisane w klubie."
        />
    @else
        <x-page-hero id="account-title" eyebrow="Strefa użytkownika">
            <x-slot:title>Moje konto</x-slot:title>
            <x-slot:description>
                Zarządzaj danymi kontaktowymi i bezpieczeństwem oraz sprawdź dane członkowskie zapisane w klubie.
            </x-slot:description>
        </x-page-hero>
    @endif

    <div @class([
        'account-content',
        'ui-stack',
        'ui-stack--lg',
        'page-container page-section page-section--flush' => ! request()->routeIs('admin.account.*'),
    ])>
        @unless (request()->routeIs('admin.account.*'))
            <x-form-errors />
        @endunless

        <form method="POST" action="{{ route($accountRoutePrefix.'.profile.update') }}" class="form-layout account-profile-form">
            @csrf
            @method('PATCH')

            <section class="form-section" aria-labelledby="account-basic-title">
                <div class="form-section__header">
                    <span class="form-section__number" aria-hidden="true">1</span>
                    <h2 id="account-basic-title">Dane podstawowe</h2>
                    <p>Możesz zmienić wyłącznie własne dane podstawowe.</p>
                </div>

                <div class="form-grid">
                    <label>
                        <span class="form-label-text">
                            Imię i nazwisko
                            <span class="form-required" aria-hidden="true">*</span>
                            <span class="sr-only">(pole wymagane)</span>
                        </span>
                        <input id="account-name" type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name"
                            @error('name') aria-invalid="true" aria-describedby="account-name-error" @enderror>
                        @error('name') <span id="account-name-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        Telefon
                        <input id="account-phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="32" autocomplete="tel" inputmode="tel"
                            @error('phone') aria-invalid="true" aria-describedby="account-phone-error" @enderror>
                        @error('phone') <span id="account-phone-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                    </label>
                </div>
            </section>

            <section class="form-section" aria-labelledby="account-contact-title">
                <div class="form-section__header">
                    <span class="form-section__number" aria-hidden="true">2</span>
                    <h2 id="account-contact-title">Dane kontaktowe</h2>
                    <p>Te zgody dotyczą publicznej prezentacji danych przy funkcjach klubowych lub profilu trenera.</p>
                </div>

                <div class="account-consents">
                    <label class="form-check account-consent">
                        <input type="hidden" name="show_email_publicly" value="0">
                        <input id="account-public-email" type="checkbox" name="show_email_publicly" value="1"
                            @checked(old('show_email_publicly', $user->show_email_publicly))
                            @error('show_email_publicly') aria-invalid="true" aria-describedby="account-public-email-error" @enderror>
                        <span><strong>Pokazuj mój e-mail publicznie</strong><br>Adres może być widoczny na stronach kontaktowych, jeśli pełnisz funkcję klubową.</span>
                    </label>
                    @error('show_email_publicly') <span id="account-public-email-error" class="form-error" role="alert">{{ $message }}</span> @enderror

                    <label class="form-check account-consent">
                        <input type="hidden" name="show_phone_publicly" value="0">
                        <input id="account-public-phone" type="checkbox" name="show_phone_publicly" value="1"
                            @checked(old('show_phone_publicly', $user->show_phone_publicly))
                            @error('show_phone_publicly') aria-invalid="true" aria-describedby="account-public-phone-error" @enderror>
                        <span><strong>Pokazuj mój telefon publicznie</strong><br>Numer może być widoczny przy profilu trenera lub funkcji klubowej.</span>
                    </label>
                    @error('show_phone_publicly') <span id="account-public-phone-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Zapisz dane profilu</button>
                </div>
            </section>
        </form>

        <form method="POST" action="{{ route($accountRoutePrefix.'.event-notifications.update') }}" class="form-layout">
            @csrf
            @method('PATCH')

            <section class="form-section" aria-labelledby="account-event-notifications-title">
                <div class="form-section__header">
                    <span class="form-section__number" aria-hidden="true">3</span>
                    <h2 id="account-event-notifications-title">Przypomnienia o wydarzeniach</h2>
                    <p>Ta zgoda pozwala samodzielnie wybierać wydarzenia, o których chcesz otrzymać jednorazowe przypomnienie około 24 godziny przed rozpoczęciem.</p>
                </div>

                <div class="account-consents">
                    <label class="form-check account-consent">
                        <input type="hidden" name="event_email_notifications_enabled" value="0">
                        <input
                            id="account-event-email-notifications"
                            type="checkbox"
                            name="event_email_notifications_enabled"
                            value="1"
                            @checked(old('event_email_notifications_enabled', $user->event_email_notifications_enabled))
                            @error('event_email_notifications_enabled') aria-invalid="true" aria-describedby="account-event-email-notifications-error" @enderror
                        >
                        <span>
                            <strong>Chcę otrzymywać e-mailowe przypomnienia o wybranych wydarzeniach</strong><br>
                            Zgoda nie zapisuje Cię jako uczestnika wydarzenia i nie ujawnia publicznie informacji o ustawionych przypomnieniach.
                        </span>
                    </label>
                    @error('event_email_notifications_enabled')
                        <span id="account-event-email-notifications-error" class="form-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <label>
                    Aktualne hasło — wymagane tylko przy włączaniu zgody
                    <input
                        id="account-event-notifications-current-password"
                        type="password"
                        name="event_notifications_current_password"
                        autocomplete="current-password"
                        aria-describedby="account-event-notifications-password-help @error('event_notifications_current_password') account-event-notifications-password-error @enderror"
                        @error('event_notifications_current_password') aria-invalid="true" @enderror
                    >
                    <span id="account-event-notifications-password-help" class="form-help">Wyłączenie zgody nie wymaga hasła i natychmiast anuluje wszystkie zapisane przypomnienia.</span>
                    @error('event_notifications_current_password')
                        <span id="account-event-notifications-password-error" class="form-error" role="alert">{{ $message }}</span>
                    @enderror
                </label>

                @if ($user->event_email_notifications_enabled && $user->event_email_notifications_confirmed_at)
                    <p class="form-help">Zgoda potwierdzona: {{ $user->event_email_notifications_confirmed_at->format('d.m.Y H:i') }}.</p>
                @endif

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Zapisz ustawienie przypomnień</button>
                </div>
            </section>
        </form>

        <section class="form-section account-security" aria-labelledby="account-security-title">
            <div class="form-section__header">
                <span class="form-section__number" aria-hidden="true">4</span>
                <h2 id="account-security-title">Bezpieczeństwo</h2>
                <p>Zmiana e-maila i hasła zawsze wymaga podania aktualnego hasła.</p>
            </div>

            <div class="ui-grid ui-grid--2 account-security-grid">
                <form method="POST" action="{{ route($accountRoutePrefix.'.email.update') }}" class="panel-card form-layout">
                    @csrf
                    @method('PATCH')
                    <h3>Zmień adres e-mail</h3>

                    <label>
                        <span class="form-label-text">
                            Nowy adres e-mail
                            <span class="form-required" aria-hidden="true">*</span>
                            <span class="sr-only">(pole wymagane)</span>
                        </span>
                        <input id="account-email" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email"
                            @error('email') aria-invalid="true" aria-describedby="account-email-error" @enderror>
                        @error('email') <span id="account-email-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="form-label-text">
                            Aktualne hasło
                            <span class="form-required" aria-hidden="true">*</span>
                            <span class="sr-only">(pole wymagane)</span>
                        </span>
                        <input id="account-email-current-password" type="password" name="email_current_password" required autocomplete="current-password"
                            @error('email_current_password') aria-invalid="true" aria-describedby="account-email-current-password-error" @enderror>
                        @error('email_current_password') <span id="account-email-current-password-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                    </label>

                    <button type="submit" class="btn btn-primary">Zmień e-mail</button>
                </form>

                <form method="POST" action="{{ route($accountRoutePrefix.'.password.update') }}" class="panel-card form-layout">
                    @csrf
                    @method('PUT')
                    <h3>Zmień hasło</h3>

                    <label>
                        <span class="form-label-text">
                            Aktualne hasło
                            <span class="form-required" aria-hidden="true">*</span>
                            <span class="sr-only">(pole wymagane)</span>
                        </span>
                        <input id="account-current-password" type="password" name="current_password" required autocomplete="current-password"
                            @error('current_password') aria-invalid="true" aria-describedby="account-current-password-error" @enderror>
                        @error('current_password') <span id="account-current-password-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="form-label-text">
                            Nowe hasło
                            <span class="form-required" aria-hidden="true">*</span>
                            <span class="sr-only">(pole wymagane)</span>
                        </span>
                        <input id="account-password" type="password" name="password" required autocomplete="new-password"
                            aria-describedby="account-password-help @error('password') account-password-error @enderror"
                            @error('password') aria-invalid="true" @enderror>
                        <span id="account-password-help" class="form-help">Minimum 12 znaków, mała i wielka litera oraz cyfra.</span>
                        @error('password') <span id="account-password-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="form-label-text">
                            Powtórz nowe hasło
                            <span class="form-required" aria-hidden="true">*</span>
                            <span class="sr-only">(pole wymagane)</span>
                        </span>
                        <input id="account-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                            @error('password_confirmation') aria-invalid="true" aria-describedby="account-password-confirmation-error" @enderror>
                        @error('password_confirmation') <span id="account-password-confirmation-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                    </label>

                    <button type="submit" class="btn btn-primary">Zmień hasło</button>
                </form>
            </div>
        </section>

        <section class="form-section" aria-labelledby="account-member-title">
            <div class="form-section__header">
                <span class="form-section__number" aria-hidden="true">5</span>
                <h2 id="account-member-title">Dane członkowskie</h2>
                <p>Dane weryfikacyjne są tylko do odczytu. Ich zmianę zgłoś administratorowi klubu.</p>
            </div>

            @if ($user->memberProfile)
                <dl class="account-data-grid">
                    <div><dt>Numer licencji PZSS</dt><dd>{{ $user->memberProfile->pzss_license_number ?: '—' }}</dd></div>
                    <div><dt>Ważność licencji PZSS</dt><dd>{{ $user->memberProfile->pzss_license_expires_at?->format('d.m.Y') ?: '—' }}</dd></div>
                    <div><dt>Numer patentu strzeleckiego</dt><dd>{{ $user->memberProfile->shooting_patent_number ?: '—' }}</dd></div>
                    <div><dt>Numer pozwolenia na broń</dt><dd>{{ $user->memberProfile->firearm_permit_number ?: '—' }}</dd></div>
                    <div><dt>Numer członkowski</dt><dd>{{ $user->memberProfile->club_member_number ?: '—' }}</dd></div>
                    <div><dt>Rok wstąpienia do klubu</dt><dd>{{ $user->memberProfile->joined_club_year ?: '—' }}</dd></div>
                    <div class="account-data-grid__full">
                        <dt>Dyscypliny</dt>
                        <dd>
                            @forelse ($user->memberProfile->disciplines ?? [] as $discipline)
                                <span class="status-badge status-badge--muted">{{ $disciplines[$discipline] ?? 'Nieznana dyscyplina' }}</span>
                            @empty
                                —
                            @endforelse
                        </dd>
                    </div>
                </dl>
            @else
                <div class="empty-state">
                    <h3>Brak profilu członkowskiego</h3>
                    <p>Dane nie zostały jeszcze przypisane do konta. Skontaktuj się z administratorem klubu.</p>
                </div>
            @endif
        </section>

        <section class="form-section" aria-labelledby="account-status-title">
            <div class="form-section__header">
                <span class="form-section__number" aria-hidden="true">6</span>
                <h2 id="account-status-title">Status konta</h2>
                <p>Rola, aktywność i weryfikacja są zarządzane przez administratora.</p>
            </div>

            <dl class="account-data-grid">
                <div><dt>Rola</dt><dd>{{ $user->role->label() }}</dd></div>
                <div><dt>Status konta</dt><dd><span class="status-badge {{ $user->is_active ? 'status-badge--success' : 'status-badge--danger' }}">{{ $user->is_active ? 'Aktywne' : 'Nieaktywne' }}</span></dd></div>
                <div><dt>Data utworzenia konta</dt><dd>{{ $user->created_at->format('d.m.Y H:i') }}</dd></div>
                <div>
                    <dt>Weryfikacja danych członkowskich</dt>
                    <dd>
                        @if ($user->memberProfile)
                            <span class="status-badge {{ $user->memberProfile->verification_status->badgeClass() }}">{{ $user->memberProfile->verification_status->label() }}</span>
                        @else
                            <span class="status-badge status-badge--muted">Brak danych</span>
                        @endif
                    </dd>
                </div>
                @if ($user->memberProfile?->verified_at)
                    <div><dt>Data weryfikacji</dt><dd>{{ $user->memberProfile->verified_at->format('d.m.Y H:i') }}</dd></div>
                @endif
            </dl>
        </section>
    </div>
@endsection
