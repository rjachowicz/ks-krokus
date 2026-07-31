@extends('layouts.admin')

@section('title', 'Edycja aktualności — panel KS Krokus')
@section('admin_title', 'Edycja aktualności')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>{{ $post->title }}</h1>
            <p>{{ $post->status->label() }}</p>
        </div>

        @if ($post->isPubliclyVisible())
            <a
                href="{{ route('news.show', $post) }}"
                class="btn btn-secondary"
                target="_blank"
                rel="noopener noreferrer"
            >
                Podgląd publiczny
            </a>
        @endif
    </header>

    <form
        method="POST"
        action="{{ route('admin.posts.update', $post) }}"
        class="admin-card admin-form"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')
        @include('admin.posts._form')
    </form>
@endsection
