@extends('layouts.admin')

@section('title', 'Nowa funkcja klubowa — panel KS Krokus')
@section('admin_title', 'Nowa funkcja klubowa')

@section('content')
    <x-admin-page-header
        title="Dodaj funkcję klubową"
        description="Utwórz rolę organizacyjną i przypisz do niej użytkowników."
    />

    <form method="POST" action="{{ route('admin.positions.store') }}" class="admin-card admin-form">
        @csrf
        @include('admin.positions._form')
    </form>
@endsection
