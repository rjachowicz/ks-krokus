@extends('layouts.admin')

@section('title', 'Podgląd aktualności — panel KS Krokus')
@section('admin_title', 'Podgląd aktualności')

@section('content')
    <x-news.article
        :post="$post"
        :back-url="route('admin.posts.index')"
        back-label="← Wróć do listy aktualności"
        :edit-url="route('admin.posts.edit', $post)"
        :show-status="true"
    />
@endsection
