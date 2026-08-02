@extends('layouts.admin')

@section('title', 'Moderacja ogłoszenia — panel KS Krokus')
@section('admin_title', 'Moderacja ogłoszenia')

@section('content')
    <x-admin-page-header :title="$listing->title" :description="$listing->status->label().' · autor: '.($listing->author?->name ?? 'usunięte konto')">
        @if ($listing->isPubliclyVisible())
            <x-slot:actions>
                <a href="{{ route('listings.show', $listing) }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary" aria-label="Podgląd publiczny ogłoszenia — otwiera w nowej karcie">Podgląd publiczny</a>
            </x-slot:actions>
        @endif
    </x-admin-page-header>

    @if ($listing->reports->isNotEmpty())
        <div class="form-error-summary" role="alert"><strong>Nierozpatrzone zgłoszenia użytkowników: {{ $listing->reports->count() }}</strong><ul>@foreach ($listing->reports as $report)<li>{{ $report->reason->label() }}@if ($report->details) — {{ $report->details }}@endif</li>@endforeach</ul></div>
    @endif

    <div class="listing-moderation-layout">
        <form method="POST" action="{{ route('admin.sale-listings.update', $listing) }}" class="admin-card admin-form listing-form" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('listings._form')
        </form>

        <aside class="listing-moderation-sidebar">
            <section class="admin-card">
                <h2>Decyzja moderacyjna</h2>
                <dl class="listing-facts">
                    <div><dt>Status</dt><dd>{{ $listing->status->label() }}</dd></div>
                    <div><dt>Wysłano</dt><dd>{{ $listing->submitted_at?->format('d.m.Y H:i') ?? '—' }}</dd></div>
                    <div><dt>Zatwierdził</dt><dd>{{ $listing->approver?->name ?? '—' }}</dd></div>
                    <div><dt>Odrzucił</dt><dd>{{ $listing->rejecter?->name ?? '—' }}</dd></div>
                    <div><dt>Wyświetlenia</dt><dd>{{ $listing->view_count }}</dd></div>
                </dl>

                <div class="listing-moderation-actions">
                    @can('approve', $listing)<form method="POST" action="{{ route('admin.sale-listings.approve', $listing) }}" data-confirm="Zatwierdzić i opublikować to ogłoszenie?">@csrf<button class="btn btn-primary" type="submit">Zatwierdź i opublikuj</button></form>@endcan
                    @can('reject', $listing)
                        <form method="POST" action="{{ route('admin.sale-listings.reject', $listing) }}" class="admin-form">
                            @csrf
                            <label>Powód odrzucenia <textarea name="rejection_reason" minlength="10" maxlength="2000" required @error('rejection_reason') aria-invalid="true" aria-describedby="listing-rejection-error" @enderror>{{ old('rejection_reason') }}</textarea>@error('rejection_reason')<span id="listing-rejection-error" class="form-error">{{ $message }}</span>@enderror</label>
                            <button class="btn btn-danger-outline" type="submit">Odrzuć ogłoszenie</button>
                        </form>
                    @endcan
                    @can('hide', $listing)<form method="POST" action="{{ route('admin.sale-listings.hide', $listing) }}">@csrf<button class="btn btn-secondary" type="submit">Ukryj</button></form>@endcan
                    @can('unhide', $listing)<form method="POST" action="{{ route('admin.sale-listings.unhide', $listing) }}">@csrf<button class="btn btn-secondary" type="submit">Przywróć widoczność</button></form>@endcan
                    @can('markAsSold', $listing)<form method="POST" action="{{ route('admin.sale-listings.sold', $listing) }}">@csrf<button class="btn btn-secondary" type="submit">Oznacz jako sprzedane</button></form>@endcan
                    @can('archive', $listing)<form method="POST" action="{{ route('admin.sale-listings.archive', $listing) }}">@csrf<button class="btn btn-secondary" type="submit">Archiwizuj</button></form>@endcan
                    @can('delete', $listing)<form method="POST" action="{{ route('admin.sale-listings.destroy', $listing) }}" data-confirm="Przenieść ogłoszenie „{{ $listing->title }}” do kosza? Pliki zostaną zachowane do ewentualnego przywrócenia.">@csrf @method('DELETE')<button class="btn btn-danger-outline" type="submit">Usuń</button></form>@endcan
                </div>
            </section>

            @can('flag', $listing)
                <section class="admin-card"><h2>Zgłoś administratorowi</h2><form method="POST" action="{{ route('admin.sale-listings.flag', $listing) }}" class="admin-form">@csrf<label>Uzasadnienie<textarea name="note" minlength="10" maxlength="2000" required>{{ old('note') }}</textarea></label><button type="submit" class="btn btn-secondary">Wyślij zgłoszenie</button></form></section>
            @endcan

            <section class="admin-card"><h2>Historia moderacji</h2><ol class="listing-history">@forelse ($listing->moderations as $entry)<li><strong>{{ $entry->action->label() }}</strong><span>{{ $entry->created_at->format('d.m.Y H:i') }} · {{ $entry->actor?->name ?? 'System' }}</span>@if ($entry->note)<p>{{ $entry->note }}</p>@endif</li>@empty<li>Brak zapisanych operacji.</li>@endforelse</ol></section>
        </aside>
    </div>
@endsection
