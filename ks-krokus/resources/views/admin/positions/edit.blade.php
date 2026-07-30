@extends('layouts.admin')

@section('title', 'Edycja funkcji klubowej — panel KS Krokus')
@section('admin_title', 'Edycja funkcji klubowej')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>{{ $position->name }}</h1>
            <p>Edytuj opis, kolejność oraz przypisane osoby.</p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.positions.update', $position) }}" class="admin-card admin-form">
        @csrf
        @method('PUT')
        @include('admin.positions._form')
    </form>
@endsection
