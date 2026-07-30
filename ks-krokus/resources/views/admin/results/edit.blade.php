@extends('layouts.admin')

@section('title', 'Edycja wyniku — panel KS Krokus')
@section('admin_title', 'Edycja wyniku')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>{{ $result->displayName() }}</h1>
            <p>
                {{ $result->eventCompetition->event->title }} —
                {{ $result->eventCompetition->competition->name }}
            </p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.results.update', $result) }}" class="admin-card admin-form">
        @csrf
        @method('PUT')
        @include('admin.results._form')
    </form>
@endsection
