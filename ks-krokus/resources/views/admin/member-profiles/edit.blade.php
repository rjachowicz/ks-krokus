@extends('layouts.admin')

@section('title', 'Dane członkowskie — panel KS Krokus')
@section('admin_title', 'Dane członkowskie')

@section('content')
    <x-admin-page-header
        :title="$editedUser->name"
        :description="'Dane członkowskie powiązane z kontem '.$editedUser->email.'.'"
    >
        <x-slot:actions>
            <a href="{{ route('admin.users.edit', $editedUser) }}" class="btn btn-secondary">Wróć do konta</a>
        </x-slot:actions>
    </x-admin-page-header>

    @if ($sourceRequest)
        <div class="panel-card ui-stack ui-stack--sm">
            <h2>Źródło danych</h2>
            <p>
                Profil jest powiązany z zatwierdzonym wnioskiem o konto.
                Notatki wewnętrzne pozostają w audytowalnym wniosku.
            </p>
            <a href="{{ route('admin.account-requests.show', $sourceRequest) }}" class="btn btn-secondary">Otwórz wniosek i notatki</a>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.member-profiles.update', $editedUser) }}" class="form-layout panel-card">
        @csrf
        @method('PUT')

        <section class="form-section" aria-labelledby="member-identifiers-title">
            <div class="form-section__header">
                <span class="form-section__number" aria-hidden="true">1</span>
                <h2 id="member-identifiers-title">Dokumenty i identyfikatory</h2>
                <p>Pola pozostaw puste, jeżeli klub nie posiada potwierdzonych danych.</p>
            </div>

            <div class="form-grid">
                <label>
                    Numer licencji PZSS
                    <input id="member-pzss-license" type="text" name="pzss_license_number" maxlength="100"
                        value="{{ old('pzss_license_number', $profile?->pzss_license_number) }}"
                        @error('pzss_license_number') aria-invalid="true" aria-describedby="member-pzss-license-error" @enderror>
                    @error('pzss_license_number') <span id="member-pzss-license-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label>
                    Data ważności licencji PZSS
                    <input id="member-pzss-license-expires" type="date" name="pzss_license_expires_at"
                        value="{{ old('pzss_license_expires_at', $profile?->pzss_license_expires_at?->format('Y-m-d')) }}"
                        @error('pzss_license_expires_at') aria-invalid="true" aria-describedby="member-pzss-license-expires-error" @enderror>
                    @error('pzss_license_expires_at') <span id="member-pzss-license-expires-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label>
                    Numer patentu strzeleckiego
                    <input id="member-patent" type="text" name="shooting_patent_number" maxlength="100"
                        value="{{ old('shooting_patent_number', $profile?->shooting_patent_number) }}"
                        @error('shooting_patent_number') aria-invalid="true" aria-describedby="member-patent-error" @enderror>
                    @error('shooting_patent_number') <span id="member-patent-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label>
                    Numer pozwolenia na broń
                    <input id="member-firearm-permit" type="text" name="firearm_permit_number" maxlength="100"
                        value="{{ old('firearm_permit_number', $profile?->firearm_permit_number) }}"
                        @error('firearm_permit_number') aria-invalid="true" aria-describedby="member-firearm-permit-error" @enderror>
                    @error('firearm_permit_number') <span id="member-firearm-permit-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label>
                    Numer członkowski
                    <input id="member-club-number" type="text" name="club_member_number" maxlength="100"
                        value="{{ old('club_member_number', $profile?->club_member_number) }}"
                        @error('club_member_number') aria-invalid="true" aria-describedby="member-club-number-error" @enderror>
                    @error('club_member_number') <span id="member-club-number-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label>
                    Rok wstąpienia do klubu
                    <input id="member-joined-year" type="number" name="joined_club_year" min="1900" max="{{ now()->year }}" inputmode="numeric"
                        value="{{ old('joined_club_year', $profile?->joined_club_year) }}"
                        @error('joined_club_year') aria-invalid="true" aria-describedby="member-joined-year-error" @enderror>
                    @error('joined_club_year') <span id="member-joined-year-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>
            </div>
        </section>

        <section class="form-section" aria-labelledby="member-disciplines-title">
            <div class="form-section__header">
                <span class="form-section__number" aria-hidden="true">2</span>
                <h2 id="member-disciplines-title">Dyscypliny</h2>
                <p>Wybierz dyscypliny potwierdzone w dokumentacji członka.</p>
            </div>

            @php($selectedDisciplines = is_array(old('disciplines')) ? old('disciplines') : ($profile?->disciplines ?? []))
            <fieldset class="form-choice-group" @error('disciplines') aria-invalid="true" aria-describedby="member-disciplines-error" @enderror>
                <legend>Dyscypliny członka</legend>
                <div class="ui-cluster">
                    @foreach ($disciplines as $value => $label)
                        <label class="form-check">
                            <input type="checkbox" name="disciplines[]" value="{{ $value }}" @checked(in_array($value, $selectedDisciplines, true))>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                @error('disciplines') <span id="member-disciplines-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                @error('disciplines.*') <span class="form-error" role="alert">{{ $message }}</span> @enderror
            </fieldset>
        </section>

        <section class="form-section" aria-labelledby="member-verification-title">
            <div class="form-section__header">
                <span class="form-section__number" aria-hidden="true">3</span>
                <h2 id="member-verification-title">Weryfikacja</h2>
                <p>Oznaczenie „Zweryfikowane” zapisuje bieżącego administratora i czas operacji.</p>
            </div>

            <label>
                <span class="form-label-text">
                    Status weryfikacji
                    <span class="form-required" aria-hidden="true">*</span>
                    <span class="sr-only">(pole wymagane)</span>
                </span>
                <select id="member-verification-status" name="verification_status" required
                    @error('verification_status') aria-invalid="true" aria-describedby="member-verification-status-error" @enderror>
                    @foreach ($verificationStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('verification_status', $profile?->verification_status?->value ?? 'unverified') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('verification_status') <span id="member-verification-status-error" class="form-error" role="alert">{{ $message }}</span> @enderror
            </label>

            @if ($profile?->verified_at)
                <p class="form-help">
                    Ostatnia weryfikacja: {{ $profile->verified_at->format('d.m.Y H:i') }},
                    administrator: {{ $profile->verifier?->name ?? 'konto usunięte' }}.
                </p>
            @endif
        </section>

        <div class="form-actions form-actions--sticky">
            <button type="submit" class="btn btn-primary">Zapisz dane członkowskie</button>
            <a href="{{ route('admin.users.edit', $editedUser) }}" class="btn btn-secondary">Anuluj</a>
        </div>
    </form>
@endsection
