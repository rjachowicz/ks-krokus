@extends('layouts.admin')

@section('title', 'Edycja aktualności — panel KS Krokus')
@section('admin_title', 'Edycja aktualności')

@section('content')
    <x-admin-page-header :title="$post->title" :description="$post->status->label()">
        @if ($post->isPubliclyVisible())
            <x-slot:actions>
                <a
                    href="{{ route('news.show', $post) }}"
                    class="btn btn-secondary"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Podgląd aktualności: {{ $post->title }} — otwiera w nowej karcie"
                >
                    Podgląd publiczny
                </a>
            </x-slot:actions>
        @endif
    </x-admin-page-header>

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
