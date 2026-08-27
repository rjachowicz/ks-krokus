@extends('layouts.admin')

@section('title', 'Edycja aktualności — panel KS Krokus')
@section('admin_title', 'Edycja aktualności')

@section('content')
    <x-admin-page-header :title="$post->title" :description="$post->status->label()">
        <x-slot:actions>
            <a
                href="{{ route('admin.posts.show', $post) }}"
                class="btn btn-secondary"
                aria-label="Podgląd aktualności: {{ $post->title }}"
            >
                Podgląd
            </a>
        </x-slot:actions>
    </x-admin-page-header>

    <form
        method="POST"
        action="{{ route('admin.posts.update', $post) }}"
        class="form-layout panel-card"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')
        @include('admin.posts._form')
    </form>
@endsection
