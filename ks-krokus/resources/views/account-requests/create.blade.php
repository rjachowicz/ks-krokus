@extends('layouts.app')

@section('title', 'Wniosek o konto — KS Krokus')
@section('meta_description', 'Bezpieczny formularz wniosku o konto dla istniejących członków KS Krokus.')
@section('robots', 'noindex, nofollow, noarchive')

@section('content')
    <x-page-hero id="account-request-title" eyebrow="Dla członków KS Krokus">
        <x-slot:title>Wniosek o <span class="highlight">utworzenie konta</span></x-slot:title>
        <x-slot:description>
            <p>Formularz służy wyłącznie obecnym członkom klubu. Wniosek zostanie zweryfikowany przez administratora — konto nie powstaje automatycznie.</p>
        </x-slot:description>
    </x-page-hero>

    <section class="page-container page-section account-request-section" aria-labelledby="account-request-form-title">
        <x-section-heading id="account-request-form-title" title="Dane do weryfikacji" meta="ACCOUNT_REQUEST" />

        <form method="POST" action="{{ route('account-requests.store') }}" class="form-layout account-request-form">
            @csrf
            <x-form-errors />

            <section class="form-section" aria-labelledby="account-personal-title">
                <header class="form-section__header">
                    <span class="form-section__number" aria-hidden="true">01</span>
                    <h2 id="account-personal-title">Dane osobowe i kontaktowe</h2>
                    <p>Podaj dane zgodne z dokumentacją członkowską klubu.</p>
                </header>

                <div class="form-grid">
                    @foreach ([
                        ['first_name', 'Imię', 'text', 'given-name', 120],
                        ['last_name', 'Nazwisko', 'text', 'family-name', 120],
                        ['email', 'Adres e-mail', 'email', 'email', 255],
                        ['phone', 'Telefon', 'tel', 'tel', 32],
                    ] as [$name, $label, $type, $autocomplete, $max])
                        <label for="account-{{ str_replace('_', '-', $name) }}">
                            <span class="form-label-text">{{ $label }} <span class="form-required" aria-hidden="true">*</span><span class="sr-only">(pole wymagane)</span></span>
                            <input id="account-{{ str_replace('_', '-', $name) }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name) }}" autocomplete="{{ $autocomplete }}" maxlength="{{ $max }}" required
                                @error($name) aria-invalid="true" aria-describedby="account-{{ str_replace('_', '-', $name) }}-error" @enderror>
                            @error($name)<span id="account-{{ str_replace('_', '-', $name) }}-error" class="form-error" role="alert">{{ $message }}</span>@enderror
                        </label>
                    @endforeach

                    <label for="account-birth-date">
                        <span class="form-label-text">Data urodzenia <span class="form-required" aria-hidden="true">*</span><span class="sr-only">(pole wymagane)</span></span>
                        <input id="account-birth-date" type="date" name="birth_date" value="{{ old('birth_date') }}" max="{{ now()->toDateString() }}" autocomplete="bday" required
                            @error('birth_date') aria-invalid="true" aria-describedby="account-birth-date-error" @enderror>
                        @error('birth_date')<span id="account-birth-date-error" class="form-error" role="alert">{{ $message }}</span>@enderror
                    </label>
                </div>
            </section>

            <section class="form-section" aria-labelledby="account-membership-title">
                <header class="form-section__header">
                    <span class="form-section__number" aria-hidden="true">02</span>
                    <h2 id="account-membership-title">Dokumenty i członkostwo</h2>
                    <p>Format numeru licencji nie jest sztucznie ograniczany — wpisz go dokładnie tak, jak w dokumencie.</p>
                </header>

                <div class="form-grid">
                    @foreach ([
                        ['pzss_license_number', 'Numer licencji PZSS', true, 100],
                        ['patent_number', 'Numer patentu', true, 100],
                        ['firearm_permit_number', 'Numer pozwolenia', false, 100],
                        ['member_number', 'Numer członkowski', false, 100],
                    ] as [$name, $label, $required, $max])
                        <label for="account-{{ str_replace('_', '-', $name) }}">
                            <span class="form-label-text">{{ $label }}
                                @if ($required)<span class="form-required" aria-hidden="true">*</span><span class="sr-only">(pole wymagane)</span>@else<span class="form-optional">(opcjonalnie)</span>@endif
                            </span>
                            <input id="account-{{ str_replace('_', '-', $name) }}" type="text" name="{{ $name }}" value="{{ old($name) }}" maxlength="{{ $max }}" @required($required)
                                @error($name) aria-invalid="true" aria-describedby="account-{{ str_replace('_', '-', $name) }}-error" @enderror>
                            @error($name)<span id="account-{{ str_replace('_', '-', $name) }}-error" class="form-error" role="alert">{{ $message }}</span>@enderror
                        </label>
                    @endforeach

                    <label for="account-license-expires-at">
                        <span class="form-label-text">Data ważności licencji <span class="form-required" aria-hidden="true">*</span><span class="sr-only">(pole wymagane)</span></span>
                        <input id="account-license-expires-at" type="date" name="pzss_license_expires_at" value="{{ old('pzss_license_expires_at') }}" required
                            @error('pzss_license_expires_at') aria-invalid="true" aria-describedby="account-license-expires-at-error" @enderror>
                        @error('pzss_license_expires_at')<span id="account-license-expires-at-error" class="form-error" role="alert">{{ $message }}</span>@enderror
                    </label>

                    <label for="account-joined-year">
                        <span class="form-label-text">Rok wstąpienia <span class="form-optional">(opcjonalnie)</span></span>
                        <input id="account-joined-year" type="number" name="joined_year" value="{{ old('joined_year') }}" min="1900" max="{{ now()->year }}" inputmode="numeric"
                            @error('joined_year') aria-invalid="true" aria-describedby="account-joined-year-error" @enderror>
                        @error('joined_year')<span id="account-joined-year-error" class="form-error" role="alert">{{ $message }}</span>@enderror
                    </label>
                </div>

                @php($selectedDisciplines = is_array(old('disciplines')) ? old('disciplines') : [])
                <fieldset class="form-choice-group" @error('disciplines') aria-invalid="true" aria-describedby="account-disciplines-error" @enderror>
                    <legend>Dyscypliny <span class="form-required" aria-hidden="true">*</span><span class="sr-only">(pole wymagane)</span></legend>
                    <div class="account-request-disciplines">
                        @foreach ($disciplines as $value => $label)
                            <label class="form-check"><input type="checkbox" name="disciplines[]" value="{{ $value }}" @checked(in_array($value, $selectedDisciplines, true))> {{ $label }}</label>
                        @endforeach
                    </div>
                    @error('disciplines')<span id="account-disciplines-error" class="form-error" role="alert">{{ $message }}</span>@enderror
                </fieldset>
            </section>

            <section class="form-section" aria-labelledby="account-additional-title">
                <header class="form-section__header">
                    <span class="form-section__number" aria-hidden="true">03</span>
                    <h2 id="account-additional-title">Dodatkowe informacje i zgoda</h2>
                    <p>Nie podawaj hasła ani innych sekretów — administrator nigdy nie potrzebuje ich do weryfikacji.</p>
                </header>

                <label for="account-additional-information">
                    <span class="form-label-text">Dodatkowe informacje <span class="form-optional">(opcjonalnie)</span></span>
                    <textarea id="account-additional-information" name="additional_information" rows="6" maxlength="5000"
                        aria-describedby="account-additional-help @error('additional_information') account-additional-error @enderror"
                        @error('additional_information') aria-invalid="true" @enderror>{{ old('additional_information') }}</textarea>
                    <span id="account-additional-help" class="form-help">Maksymalnie 5000 znaków.</span>
                    @error('additional_information')<span id="account-additional-error" class="form-error" role="alert">{{ $message }}</span>@enderror
                </label>

                <label class="form-check account-request-consent">
                    <input type="checkbox" name="data_processing_consent" value="1" required @checked(old('data_processing_consent'))
                        @error('data_processing_consent') aria-invalid="true" aria-describedby="account-consent-error" @enderror>
                    <span>Wyrażam zgodę na przetwarzanie podanych danych w celu weryfikacji członkostwa i utworzenia konta. <a href="{{ route('rodo') }}">Zobacz klauzulę RODO</a>. <span class="form-required" aria-hidden="true">*</span><span class="sr-only">(pole wymagane)</span></span>
                </label>
                @error('data_processing_consent')<span id="account-consent-error" class="form-error" role="alert">{{ $message }}</span>@enderror

                <div class="honeypot" aria-hidden="true">
                    <label for="account-website">Strona internetowa</label>
                    <input id="account-website" type="text" name="website" value="" tabindex="-1" autocomplete="off">
                </div>
            </section>

            <div class="form-actions">
                <div class="form-actions__secondary"><a href="{{ route('login') }}" class="btn btn-secondary">Wróć do logowania</a></div>
                <div class="form-actions__primary"><button type="submit" class="btn btn-primary">Złóż wniosek</button></div>
            </div>
        </form>
    </section>
@endsection
