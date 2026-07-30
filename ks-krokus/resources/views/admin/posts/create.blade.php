@extends('layouts.admin')

@section('title', 'Nowa aktualność — panel KS Krokus')
@section('admin_title', 'Nowa aktualność')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>Dodaj aktualność</h1>
            <p>Utwórz wpis z opisem, zdjęciem głównym i galerią.</p>
        </div>
    </header>

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
