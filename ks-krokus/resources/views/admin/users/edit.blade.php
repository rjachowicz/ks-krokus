@extends('layouts.admin')

@section('title', 'Edycja użytkownika — panel KS Krokus')
@section('admin_title', 'Edycja użytkownika')

@section('content')
    <x-admin-page-header :title="$editedUser->name" :description="$editedUser->email" />

    <form method="POST" action="{{ route('admin.users.update', $editedUser) }}" class="admin-card admin-form">
        @csrf
        @method('PUT')
        @include('admin.users._form')
    </form>
@endsection
