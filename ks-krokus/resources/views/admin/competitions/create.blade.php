@extends('layouts.admin')

@section('title', 'Nowa konkurencja — panel KS Krokus')
@section('admin_title', 'Nowa konkurencja')

@section('content')
    <x-admin-page-header
        title="Dodaj konkurencję"
        description="Rozszerz listę konkurencji ISSF lub IPSC."
    />

    <form method="POST" action="{{ route('admin.competitions.store') }}" class="form-layout panel-card">
        @csrf
        @include('admin.competitions._form')
    </form>
@endsection
