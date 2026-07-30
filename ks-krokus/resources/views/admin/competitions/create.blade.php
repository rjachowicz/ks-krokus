@extends('layouts.admin')

@section('title', 'Nowa konkurencja — panel KS Krokus')
@section('admin_title', 'Nowa konkurencja')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>Dodaj konkurencję</h1>
            <p>Rozszerz listę konkurencji ISSF lub IPSC.</p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.competitions.store') }}" class="admin-card admin-form">
        @csrf
        @include('admin.competitions._form')
    </form>
@endsection
