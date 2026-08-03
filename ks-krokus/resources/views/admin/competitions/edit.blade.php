@extends('layouts.admin')

@section('title', 'Edycja konkurencji — panel KS Krokus')
@section('admin_title', 'Edycja konkurencji')

@section('content')
    <x-admin-page-header :title="$definition->name" :description="$definition->code" />

    <form method="POST" action="{{ route('admin.competitions.update', $definition) }}" class="form-layout panel-card">
        @csrf
        @method('PUT')
        @include('admin.competitions._form')
    </form>
@endsection
