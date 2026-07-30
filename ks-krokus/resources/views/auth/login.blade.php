@extends('layouts.auth')

@section('title', 'Logowanie — KS Krokus')

@section('content')
    <section class="auth-card">
        <a href="{{ route('home') }}" class="auth-card__brand">
            <span class="logo-mark">KS</span>
            KS KROKUS
        </a>

        <h1>Logowanie</h1>
        <p class="auth-card__intro">
            Panel zarządzania aktualnościami, kalendarzem i wynikami zawodów.
        </p>

        <form method="POST" action="{{ route('login.store') }}" class="auth-form">
            @csrf

            <label>
                Adres e-mail
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                    autofocus
                >
                @error('email')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                Hasło
                <input
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
                @error('password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </label>

            <label class="form-check">
                <input type="checkbox" name="remember" value="1">
                Zapamiętaj mnie
            </label>

            <button type="submit" class="btn btn-primary">Zaloguj się</button>
        </form>
    </section>
@endsection
