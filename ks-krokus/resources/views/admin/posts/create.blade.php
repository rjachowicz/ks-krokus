@extends('layouts.admin')

@section('title', 'Nowa aktualność — panel KS Krokus')
@section('admin_title', 'Nowa aktualność')

@section('content')
    <x-admin-page-header
        title="Dodaj aktualność"
        description="Utwórz wpis z opisem, zdjęciem głównym i galerią."
    />

    <form
        method="POST"
        action="{{ route('admin.posts.store') }}"
        class="admin-card admin-form"
        enctype="multipart/form-data"
    >
        @csrf
        @include('admin.posts._form')
    </form>
@endsection
