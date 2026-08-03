@extends('layouts.auth')

@section('title', 'Logowanie — KS Krokus')

@section('content')
    <section class="auth-card">
        <a href="{{ route('home') }}" class="auth-card__brand">
            <span class="logo-mark" aria-hidden="true">KS</span>
            KS KROKUS
        </a>

        <h1>Logowanie</h1>
        <p class="auth-card__intro">
            Panel zarządzania aktualnościami, kalendarzem i wynikami zawodów.
        </p>

        <form method="POST" action="{{ route('login.store') }}" class="auth-form">
            @csrf
            <x-form-errors />

            <label for="login-email">
                <span class="form-label-text">
                    Adres e-mail <span class="form-required" aria-hidden="true">*</span>
                    <span class="sr-only">(pole wymagane)</span>
                </span>
                <input
                    id="login-email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    maxlength="255"
                    required
                    autofocus
                    @error('email') aria-invalid="true" aria-describedby="login-email-error" @enderror
                >
                @error('email')
                    <span id="login-email-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <label for="login-password">
                <span class="form-label-text">
                    Hasło <span class="form-required" aria-hidden="true">*</span>
                    <span class="sr-only">(pole wymagane)</span>
                </span>
                <input
                    id="login-password"
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    maxlength="4096"
                    required
                    @error('password') aria-invalid="true" aria-describedby="login-password-error" @enderror
                >
                @error('password')
                    <span id="login-password-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <label class="form-check">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Zapamiętaj mnie
            </label>

            <button type="submit" class="btn btn-primary">Zaloguj się</button>
        </form>
    </section>
@endsection
