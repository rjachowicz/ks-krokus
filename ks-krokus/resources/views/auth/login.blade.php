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
                Adres e-mail
                <input
                    id="login-email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                    autofocus
                    @error('email') aria-invalid="true" aria-describedby="login-email-error" @enderror
                >
                @error('email')
                    <span id="login-email-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <label for="login-password">
                Hasło
                <input
                    id="login-password"
                    type="password"
                    name="password"
                    autocomplete="current-password"
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
