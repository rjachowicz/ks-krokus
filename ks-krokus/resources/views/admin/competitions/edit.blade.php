@extends('layouts.admin')

@section('title', 'Edycja konkurencji — panel KS Krokus')
@section('admin_title', 'Edycja konkurencji')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>{{ $definition->name }}</h1>
            <p>{{ $definition->code }}</p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.competitions.update', $definition) }}" class="admin-card admin-form">
        @csrf
        @method('PUT')
        @include('admin.competitions._form')
    </form>
@endsection
