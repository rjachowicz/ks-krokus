@extends('layouts.admin')

@section('title', 'Nowe wydarzenie — panel KS Krokus')
@section('admin_title', 'Nowe wydarzenie')

@section('content')
    <x-admin-page-header
        title="Dodaj wydarzenie"
        description="Utwórz zawody lub trening i przypisz konkurencje."
    />

    <form method="POST" action="{{ route('admin.events.store') }}" class="form-layout panel-card">
        @csrf
        @include('admin.events._form')
    </form>
@endsection
