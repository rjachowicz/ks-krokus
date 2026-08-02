@extends('layouts.admin')

@section('title', 'Zgłoszenia ogłoszeń — panel KS Krokus')
@section('admin_title', 'Zgłoszenia ogłoszeń')

@section('content')
    <x-admin-page-header title="Zgłoszenia ogłoszeń" description="Nierozpatrzone sygnały o nieaktualnych, błędnych lub niewłaściwych ofertach.">
        <x-slot:actions><a href="{{ route('admin.sale-listings.index') }}" class="btn btn-secondary">Wróć do ogłoszeń</a></x-slot:actions>
    </x-admin-page-header>

    <div class="admin-table-wrap" role="region" aria-label="Zgłoszenia ogłoszeń" tabindex="0">
        <table class="admin-table listing-reports-table">
            <caption class="sr-only">Nierozpatrzone zgłoszenia ogłoszeń</caption>
            <thead><tr><th scope="col">Ogłoszenie</th><th scope="col">Powód</th><th scope="col">Zgłaszający</th><th scope="col">Data</th><th scope="col">Operacje</th></tr></thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr>
                        <td data-label="Ogłoszenie"><strong>{{ $report->listing?->title ?? 'Usunięte ogłoszenie' }}</strong></td>
                        <td data-label="Powód"><span class="status-badge status-badge--danger">{{ $report->reason->label() }}</span>@if ($report->details)<p class="listing-report-details">{{ $report->details }}</p>@endif</td>
                        <td data-label="Zgłaszający">{{ $report->reporter?->name ?? 'Gość' }}</td>
                        <td data-label="Data"><time datetime="{{ $report->created_at->toIso8601String() }}">{{ $report->created_at->format('d.m.Y H:i') }}</time></td>
                        <td data-label="Operacje"><div class="admin-table__actions">@if ($report->listing && !$report->listing->trashed())<a href="{{ route('admin.sale-listings.edit', $report->listing) }}" class="btn btn-secondary">Otwórz ogłoszenie</a>@endif<form method="POST" action="{{ route('admin.sale-listings.reports.resolve', $report) }}">@csrf<button class="btn btn-primary" type="submit">Oznacz jako rozpatrzone</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="listing-table-empty"><strong>Brak zgłoszeń</strong><span>Wszystkie zgłoszenia zostały rozpatrzone.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $reports->links() }}
@endsection
