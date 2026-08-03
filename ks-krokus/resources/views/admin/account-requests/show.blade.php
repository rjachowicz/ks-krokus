@extends('layouts.admin')

@section('title', 'Wniosek o konto — panel KS Krokus')
@section('admin_title', 'Wniosek o konto')

@section('content')
    <x-admin-page-header :title="$accountRequest->fullName()" :description="'Wniosek złożony '.$accountRequest->created_at->format('d.m.Y H:i')">
        <x-slot:actions><a href="{{ route('admin.account-requests.index') }}" class="btn btn-secondary">Wróć do listy</a></x-slot:actions>
    </x-admin-page-header>

    <div class="account-request-admin-status">
        <span class="status-badge {{ $accountRequest->status->badgeClass() }}">{{ $accountRequest->status->label() }}</span>
    </div>

    <div class="account-request-admin-layout sidebar-layout">
        <div class="ui-stack">
            <section class="panel-card">
                <h2>Dane osobowe i członkowskie</h2>
                <dl class="account-request-facts">
                    <div><dt>Imię i nazwisko</dt><dd>{{ $accountRequest->fullName() }}</dd></div>
                    <div><dt>Adres e-mail</dt><dd>{{ $accountRequest->email }}</dd></div>
                    <div><dt>Telefon</dt><dd>{{ $accountRequest->phone }}</dd></div>
                    <div><dt>Data urodzenia</dt><dd>{{ $accountRequest->birth_date->format('d.m.Y') }}</dd></div>
                    <div><dt>Numer licencji PZSS</dt><dd>{{ $accountRequest->pzss_license_number }}</dd></div>
                    <div><dt>Ważność licencji</dt><dd>{{ $accountRequest->pzss_license_expires_at->format('d.m.Y') }}</dd></div>
                    <div><dt>Numer patentu</dt><dd>{{ $accountRequest->patent_number }}</dd></div>
                    <div><dt>Numer pozwolenia</dt><dd>{{ $accountRequest->firearm_permit_number ?: '—' }}</dd></div>
                    <div><dt>Numer członkowski</dt><dd>{{ $accountRequest->member_number ?: '—' }}</dd></div>
                    <div><dt>Rok wstąpienia</dt><dd>{{ $accountRequest->joined_year ?: '—' }}</dd></div>
                    <div><dt>Dyscypliny</dt><dd>{{ collect($accountRequest->disciplines)->map(fn ($value) => $disciplines[$value] ?? $value)->join(', ') }}</dd></div>
                    <div><dt>Zgoda na przetwarzanie danych</dt><dd>{{ $accountRequest->data_processing_consent ? 'Tak' : 'Nie' }}</dd></div>
                </dl>
            </section>

            <section class="panel-card">
                <h2>Dodatkowe informacje</h2>
                <p class="preserve-lines">{{ $accountRequest->additional_information ?: 'Nie podano dodatkowych informacji.' }}</p>
            </section>

            <section class="panel-card">
                <h2>Historia decyzji</h2>
                <dl class="account-request-facts">
                    <div><dt>Rozpatrzył</dt><dd>{{ $accountRequest->reviewer?->name ?? '—' }}</dd></div>
                    <div><dt>Data rozpatrzenia</dt><dd>{{ $accountRequest->reviewed_at?->format('d.m.Y H:i') ?? '—' }}</dd></div>
                    <div><dt>Utworzone konto</dt><dd>
                        @if ($accountRequest->createdUser)
                            <a href="{{ route('admin.users.edit', $accountRequest->createdUser) }}">{{ $accountRequest->createdUser->name }}</a>
                        @else
                            —
                        @endif
                    </dd></div>
                    <div><dt>Powód odrzucenia</dt><dd>{{ $accountRequest->rejection_reason ?: '—' }}</dd></div>
                </dl>
            </section>
        </div>

        <aside class="ui-stack" aria-label="Narzędzia weryfikacji wniosku">
            <section class="panel-card">
                <h2>Notatki wewnętrzne</h2>
                <form method="POST" action="{{ route('admin.account-requests.notes.update', $accountRequest) }}" class="form-layout">
                    @csrf
                    @method('PUT')
                    <label for="account-request-internal-notes">
                        <span class="form-label-text">Notatki dla administratorów</span>
                        <textarea id="account-request-internal-notes" name="internal_notes" rows="7" maxlength="5000"
                            aria-describedby="account-request-notes-help @error('internal_notes') account-request-notes-error @enderror"
                            @error('internal_notes') aria-invalid="true" @enderror>{{ old('internal_notes', $accountRequest->internal_notes) }}</textarea>
                        <span id="account-request-notes-help" class="form-help">Te informacje nie są wysyłane wnioskodawcy.</span>
                        @error('internal_notes')<span id="account-request-notes-error" class="form-error">{{ $message }}</span>@enderror
                    </label>
                    <button type="submit" class="btn btn-secondary">Zapisz notatki</button>
                </form>
            </section>

            @if ($accountRequest->status === \App\Enums\AccountRequestStatus::Pending)
                <section class="panel-card account-request-decision">
                    <h2>Decyzja administratora</h2>

                    <form method="POST" action="{{ route('admin.account-requests.approve', $accountRequest) }}" data-confirm="Zatwierdzić wniosek, utworzyć aktywne konto i wysłać link ustawienia hasła?">
                        @csrf
                        <button type="submit" class="btn btn-primary">Zatwierdź i utwórz konto</button>
                    </form>

                    <details @error('rejection_reason') open @enderror>
                        <summary>Odrzuć wniosek</summary>
                        <form method="POST" action="{{ route('admin.account-requests.reject', $accountRequest) }}" class="form-layout">
                            @csrf
                            <label for="account-request-rejection-reason">
                                <span class="form-label-text">Powód odrzucenia <span class="form-required" aria-hidden="true">*</span><span class="sr-only">(pole wymagane)</span></span>
                                <textarea id="account-request-rejection-reason" name="rejection_reason" rows="6" minlength="10" maxlength="2000" required
                                    aria-describedby="account-request-rejection-help @error('rejection_reason') account-request-rejection-error @enderror"
                                    @error('rejection_reason') aria-invalid="true" @enderror>{{ old('rejection_reason') }}</textarea>
                                <span id="account-request-rejection-help" class="form-help">Powód pozostaje w panelu. E-mail do wnioskodawcy jest neutralny i nie zawiera notatek wewnętrznych.</span>
                                @error('rejection_reason')<span id="account-request-rejection-error" class="form-error">{{ $message }}</span>@enderror
                            </label>
                            <button type="submit" class="btn btn-danger-outline">Potwierdź odrzucenie</button>
                        </form>
                    </details>
                </section>
            @endif
        </aside>
    </div>
@endsection
