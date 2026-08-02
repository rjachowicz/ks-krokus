@extends('layouts.admin')
@section('title', 'Edycja ogłoszenia — panel KS Krokus')
@section('admin_title', 'Edycja ogłoszenia')
@section('content')
    <x-admin-page-header :title="$listing->title" :description="$listing->status->label()" />
    @if ($listing->rejection_reason)<div class="form-error-summary" role="alert"><strong>Powód odrzucenia:</strong> {{ $listing->rejection_reason }}</div>@endif
    <form method="POST" action="{{ route('admin.my-listings.update', $listing) }}" class="form-layout listing-form" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('listings._form')
    </form>
@endsection
