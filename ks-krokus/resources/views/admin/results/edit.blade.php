@extends('layouts.admin')

@section('title', 'Edycja wyniku — panel KS Krokus')
@section('admin_title', 'Edycja wyniku')

@section('content')
    <x-admin-page-header
        :title="$result->displayName()"
        :description="$result->eventCompetition->event->title.' — '.$result->eventCompetition->competition->name"
    />

    <form method="POST" action="{{ route('admin.results.update', $result) }}" class="form-layout panel-card">
        @csrf
        @method('PUT')
        @include('admin.results._form')
    </form>
@endsection
