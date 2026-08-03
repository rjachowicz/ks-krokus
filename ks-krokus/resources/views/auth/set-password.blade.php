@extends('layouts.auth')

@section('title', 'Ustaw hasło — KS Krokus')

@section('content')
    <section class="auth-card">
        <a href="{{ route('home') }}" class="auth-card__brand">
            <span class="logo-mark" aria-hidden="true">KS</span>
            KS KROKUS
        </a>

        <h1>Ustaw nowe hasło</h1>
        <p class="auth-card__intro">Ustaw bezpieczne hasło do konta. Hasło pozostaje znane wyłącznie Tobie.</p>

        <form method="POST" action="{{ route('password.update') }}" class="auth-form">
            @csrf
            <x-form-errors />
            <input type="hidden" name="token" value="{{ $token }}">

            <label for="password-email">
                <span class="form-label-text">Adres e-mail <span class="form-required" aria-hidden="true">*</span><span class="sr-only">(pole wymagane)</span></span>
                <input id="password-email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="username" maxlength="255" readonly required
                    @error('email') aria-invalid="true" aria-describedby="password-email-error" @enderror>
                @error('email')<span id="password-email-error" class="form-error" role="alert">{{ $message }}</span>@enderror
            </label>

            <label for="new-password">
                <span class="form-label-text">Nowe hasło <span class="form-required" aria-hidden="true">*</span><span class="sr-only">(pole wymagane)</span></span>
                <input id="new-password" type="password" name="password" autocomplete="new-password" minlength="12" maxlength="4096" required
                    aria-describedby="new-password-help @error('password') new-password-error @enderror"
                    @error('password') aria-invalid="true" @enderror>
                <span id="new-password-help" class="form-help">Minimum 12 znaków, mała i wielka litera oraz cyfra.</span>
                @error('password')<span id="new-password-error" class="form-error" role="alert">{{ $message }}</span>@enderror
            </label>

            <label for="new-password-confirmation">
                <span class="form-label-text">Powtórz nowe hasło <span class="form-required" aria-hidden="true">*</span><span class="sr-only">(pole wymagane)</span></span>
                <input id="new-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="12" maxlength="4096" required>
            </label>

            <button type="submit" class="btn btn-primary">Ustaw nowe hasło</button>
        </form>
    </section>
@endsection
