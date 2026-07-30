@extends('layouts.admin')

@section('title', 'Nowe wydarzenie — panel KS Krokus')
@section('admin_title', 'Nowe wydarzenie')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>Dodaj wydarzenie</h1>
            <p>Utwórz zawody lub trening i przypisz konkurencje.</p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.events.store') }}" class="admin-card admin-form">
        @csrf
        @include('admin.events._form')
    </form>
@endsection
