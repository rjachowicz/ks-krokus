@extends('layouts.admin')

@section('title', 'Nowy użytkownik — panel KS Krokus')
@section('admin_title', 'Nowy użytkownik')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>Dodaj użytkownika</h1>
            <p>Utwórz konto i przypisz rolę systemową.</p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.users.store') }}" class="admin-card admin-form">
        @csrf
        @include('admin.users._form')
    </form>
@endsection
