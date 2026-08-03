@extends('layouts.admin')

@section('title', 'Moderacja ogłoszenia — panel KS Krokus')
@section('admin_title', 'Moderacja ogłoszenia')

@section('content')
    <x-admin-page-header :title="$listing->title" :description="'Autor: '.($listing->author?->name ?? 'usunięte konto')">
        @if ($listing->isPubliclyVisible())
            <x-slot:actions>
                <a href="{{ route('listings.show', $listing) }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary" aria-label="Podgląd publiczny ogłoszenia — otwiera w nowej karcie">Podgląd publiczny</a>
            </x-slot:actions>
        @endif
    </x-admin-page-header>

    <div class="listing-moderation-status">
        <span class="status-badge {{ $listing->status->badgeClass() }}">{{ $listing->status->label() }}</span>
        @if ($listing->is_hidden)<span class="status-badge status-badge--danger">Ogłoszenie ukryte</span>@endif
    </div>

    @if ($listing->reports->isNotEmpty())
        <div class="form-error-summary listing-reports-alert" role="alert">
            <strong>Nierozpatrzone zgłoszenia użytkowników: {{ $listing->reports->count() }}</strong>
            <ul>@foreach ($listing->reports as $report)<li><strong>{{ $report->reason->label() }}</strong>@if ($report->details) — {{ $report->details }}@endif</li>@endforeach</ul>
        </div>
    @endif

    <div class="listing-moderation-layout sidebar-layout">
        <form method="POST" action="{{ route('admin.sale-listings.update', $listing) }}" class="form-layout listing-form" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('listings._form')
        </form>

        <aside class="listing-moderation-sidebar ui-stack" aria-label="Narzędzia moderacji">
            <section class="panel-card moderation-card">
                <header class="moderation-card__header">
                    <span class="moderation-card__eyebrow">Stan ogłoszenia</span>
                    <h2>Decyzja moderacyjna</h2>
                </header>
                <dl class="listing-facts listing-facts--single">
                    <div><dt>Status</dt><dd>{{ $listing->status->label() }}</dd></div>
                    <div><dt>Wysłano</dt><dd>{{ $listing->submitted_at?->format('d.m.Y H:i') ?? '—' }}</dd></div>
                    <div><dt>Zatwierdził</dt><dd>{{ $listing->approver?->name ?? '—' }}</dd></div>
                    <div><dt>Odrzucił</dt><dd>{{ $listing->rejecter?->name ?? '—' }}</dd></div>
                    <div><dt>Wyświetlenia</dt><dd>{{ $listing->view_count }}</dd></div>
                </dl>

                <div class="listing-moderation-actions">
                    @can('approve', $listing)
                        <form method="POST" action="{{ route('admin.sale-listings.approve', $listing) }}" data-confirm="Zatwierdzić i opublikować to ogłoszenie?">@csrf<button class="btn btn-primary" type="submit">Zatwierdź i opublikuj</button></form>
                    @endcan

                    @can('reject', $listing)
                        <details class="moderation-rejection" @error('rejection_reason') open @enderror>
                            <summary>Odrzuć ogłoszenie</summary>
                            <form method="POST" action="{{ route('admin.sale-listings.reject', $listing) }}" class="form-layout moderation-inline-form">
                                @csrf
                                <label for="listing-rejection-reason">Powód odrzucenia
                                    <textarea id="listing-rejection-reason" name="rejection_reason" minlength="10" maxlength="2000" required @error('rejection_reason') aria-invalid="true" aria-describedby="listing-rejection-error" @enderror>{{ old('rejection_reason') }}</textarea>
                                    <span class="form-help">Powód otrzyma autor ogłoszenia. Minimum 10 znaków.</span>
                                    @error('rejection_reason')<span id="listing-rejection-error" class="form-error">{{ $message }}</span>@enderror
                                </label>
                                <button class="btn btn-danger-outline" type="submit">Potwierdź odrzucenie</button>
                            </form>
                        </details>
                    @endcan

                    <div class="moderation-secondary-actions">
                        @can('hide', $listing)<form method="POST" action="{{ route('admin.sale-listings.hide', $listing) }}">@csrf<button class="btn btn-secondary" type="submit">Ukryj</button></form>@endcan
                        @can('unhide', $listing)<form method="POST" action="{{ route('admin.sale-listings.unhide', $listing) }}">@csrf<button class="btn btn-secondary" type="submit">Przywróć widoczność</button></form>@endcan
                        @can('markAsSold', $listing)<form method="POST" action="{{ route('admin.sale-listings.sold', $listing) }}">@csrf<button class="btn btn-secondary" type="submit">Oznacz jako sprzedane</button></form>@endcan
                        @can('archive', $listing)<form method="POST" action="{{ route('admin.sale-listings.archive', $listing) }}">@csrf<button class="btn btn-secondary" type="submit">Archiwizuj</button></form>@endcan
                    </div>

                    @can('delete', $listing)
                        <form method="POST" action="{{ route('admin.sale-listings.destroy', $listing) }}" class="moderation-danger-action" data-confirm="Przenieść ogłoszenie „{{ $listing->title }}” do kosza? Pliki zostaną zachowane do ewentualnego przywrócenia.">@csrf @method('DELETE')<button class="btn btn-danger-outline" type="submit">Przenieś do kosza</button></form>
                    @endcan
                </div>
            </section>

            @can('flag', $listing)
                <section class="panel-card moderation-card">
                    <header class="moderation-card__header"><span class="moderation-card__eyebrow">Eskalacja</span><h2>Zgłoś administratorowi</h2></header>
                    <form method="POST" action="{{ route('admin.sale-listings.flag', $listing) }}" class="form-layout moderation-inline-form">
                        @csrf
                        <label for="listing-flag-note">Uzasadnienie
                            <textarea id="listing-flag-note" name="note" minlength="10" maxlength="2000" required @error('note') aria-invalid="true" aria-describedby="listing-flag-note-error" @enderror>{{ old('note') }}</textarea>
                            @error('note')<span id="listing-flag-note-error" class="form-error">{{ $message }}</span>@enderror
                        </label>
                        <button type="submit" class="btn btn-secondary">Wyślij zgłoszenie</button>
                    </form>
                </section>
            @endcan

            <section class="panel-card moderation-card">
                <header class="moderation-card__header"><span class="moderation-card__eyebrow">Audyt</span><h2>Historia moderacji</h2></header>
                <ol class="listing-history">
                    @forelse ($listing->moderations as $entry)
                        <li>
                            <strong>{{ $entry->action->label() }}</strong>
                            <span><time datetime="{{ $entry->created_at->toIso8601String() }}">{{ $entry->created_at->format('d.m.Y H:i') }}</time> · {{ $entry->actor?->name ?? 'System' }}</span>
                            @if ($entry->note)<p>{{ $entry->note }}</p>@endif
                        </li>
                    @empty
                        <li class="listing-history__empty">Brak zapisanych operacji.</li>
                    @endforelse
                </ol>
            </section>
        </aside>
    </div>
@endsection
