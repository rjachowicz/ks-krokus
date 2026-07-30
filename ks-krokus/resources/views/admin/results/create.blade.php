@extends('layouts.admin')

@section('title', 'Nowy wynik — panel KS Krokus')
@section('admin_title', 'Nowy wynik')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>Dodaj wynik</h1>
            <p>Przypisz rezultat do wydarzenia, konkurencji i opcjonalnie konta użytkownika.</p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.results.store') }}" class="admin-card admin-form">
        @csrf
        @include('admin.results._form')
    </form>
@endsection
