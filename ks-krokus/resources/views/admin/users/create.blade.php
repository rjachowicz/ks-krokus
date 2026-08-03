@extends('layouts.admin')

@section('title', 'Nowy użytkownik — panel KS Krokus')
@section('admin_title', 'Nowy użytkownik')

@section('content')
    <x-admin-page-header
        title="Dodaj użytkownika"
        description="Utwórz konto i przypisz rolę systemową."
    />

    <form method="POST" action="{{ route('admin.users.store') }}" class="form-layout panel-card">
        @csrf
        @include('admin.users._form')
    </form>
@endsection
