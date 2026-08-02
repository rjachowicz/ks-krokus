@extends('layouts.admin')
@section('title', 'Nowe ogłoszenie — panel KS Krokus')
@section('admin_title', 'Nowe ogłoszenie')
@section('content')
    <x-admin-page-header title="Dodaj ogłoszenie" description="Przygotuj ofertę i wyślij ją do moderacji administratora." />
    <form method="POST" action="{{ route('admin.my-listings.store') }}" class="admin-card admin-form listing-form" enctype="multipart/form-data">
        @csrf
        @include('listings._form')
    </form>
@endsection
