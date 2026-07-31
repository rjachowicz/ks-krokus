@extends('layouts.admin')

@section('title', 'Nowy wynik — panel KS Krokus')
@section('admin_title', 'Nowy wynik')

@section('content')
    <x-admin-page-header
        title="Dodaj wynik"
        description="Przypisz rezultat do wydarzenia, konkurencji i opcjonalnie konta użytkownika."
    />

    <form method="POST" action="{{ route('admin.results.store') }}" class="admin-card admin-form">
        @csrf
        @include('admin.results._form')
    </form>
@endsection
