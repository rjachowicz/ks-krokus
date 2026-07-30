@extends('layouts.admin')

@section('title', 'Nowa funkcja klubowa — panel KS Krokus')
@section('admin_title', 'Nowa funkcja klubowa')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>Dodaj funkcję klubową</h1>
            <p>Utwórz rolę organizacyjną i przypisz do niej użytkowników.</p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.positions.store') }}" class="admin-card admin-form">
        @csrf
        @include('admin.positions._form')
    </form>
@endsection
