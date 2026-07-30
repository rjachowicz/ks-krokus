@extends('layouts.admin')

@section('title', 'Edycja użytkownika — panel KS Krokus')
@section('admin_title', 'Edycja użytkownika')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>{{ $editedUser->name }}</h1>
            <p>{{ $editedUser->email }}</p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.users.update', $editedUser) }}" class="admin-card admin-form">
        @csrf
        @method('PUT')
        @include('admin.users._form')
    </form>
@endsection
