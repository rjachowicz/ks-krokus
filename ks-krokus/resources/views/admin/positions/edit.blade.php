@extends('layouts.admin')

@section('title', 'Edycja funkcji klubowej — panel KS Krokus')
@section('admin_title', 'Edycja funkcji klubowej')

@section('content')
    <x-admin-page-header
        :title="$position->name"
        description="Edytuj opis, kolejność oraz przypisane osoby."
    />

    <form method="POST" action="{{ route('admin.positions.update', $position) }}" class="form-layout panel-card">
        @csrf
        @method('PUT')
        @include('admin.positions._form')
    </form>
@endsection
