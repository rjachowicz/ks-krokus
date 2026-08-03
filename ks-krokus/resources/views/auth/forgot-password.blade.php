@extends('layouts.auth')

@section('title', 'Odzyskiwanie hasła — KS Krokus')

@section('content')
    <section class="auth-card">
        <a href="{{ route('home') }}" class="auth-card__brand">
            <span class="logo-mark" aria-hidden="true">KS</span>
            KS KROKUS
        </a>

        <h1>Odzyskiwanie hasła</h1>
        <p class="auth-card__intro">Podaj adres e-mail przypisany do konta. Jeśli konto jest aktywne, wyślemy bezpieczny link do ustawienia nowego hasła.</p>

        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
            @csrf
            <x-form-errors />

            <label for="forgot-password-email">
                <span class="form-label-text">
                    Adres e-mail <span class="form-required" aria-hidden="true">*</span>
                    <span class="sr-only">(pole wymagane)</span>
                </span>
                <input
                    id="forgot-password-email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    maxlength="255"
                    required
                    autofocus
                    @error('email') aria-invalid="true" aria-describedby="forgot-password-email-error" @enderror
                >
                @error('email')
                    <span id="forgot-password-email-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <button type="submit" class="btn btn-primary">Wyślij link do hasła</button>
        </form>

        <p class="auth-card__account-request">
            <a href="{{ route('login') }}">Wróć do logowania</a>
        </p>
    </section>
@endsection
