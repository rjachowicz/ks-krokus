@extends('layouts.admin')

@section('title', 'Edycja użytkownika — panel KS Krokus')
@section('admin_title', 'Edycja użytkownika')

@section('content')
    <x-admin-page-header :title="$editedUser->name" :description="$editedUser->email">
        <x-slot:actions>
            <a href="{{ route('admin.member-profiles.edit', $editedUser) }}" class="btn btn-secondary">Dane członkowskie</a>
            <form method="POST" action="{{ route('admin.users.password.resend', $editedUser) }}"
                data-confirm="Wysłać użytkownikowi „{{ $editedUser->name }}” nowy link ustawienia hasła? Poprzedni link przestanie działać.">
                @csrf
                <button type="submit" class="btn btn-secondary" @disabled(! $editedUser->is_active)>Wyślij ponownie link ustawienia hasła</button>
            </form>
        </x-slot:actions>
    </x-admin-page-header>

    @error('password_link')
        <div class="form-errors" role="alert">{{ $message }}</div>
    @enderror

    @if ($editedUser->password_link_sent_at)
        <p class="form-help">
            Ostatni link wysłał {{ $editedUser->passwordLinkSender?->name ?? 'usunięty administrator' }}
            {{ $editedUser->password_link_sent_at->format('d.m.Y H:i') }}.
        </p>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $editedUser) }}" class="form-layout panel-card">
        @csrf
        @method('PUT')
        @include('admin.users._form')
    </form>
@endsection
