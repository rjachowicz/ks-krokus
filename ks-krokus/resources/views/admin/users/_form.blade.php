@php
    $isEdit = isset($editedUser);
    $currentRole = old('role', $editedUser->role->value ?? \App\Enums\UserRole::User->value);
@endphp

<div class="form-grid">
    <h2 class="admin-section-title form-grid--span-full">Dane konta</h2>
    <label>
        Imię i nazwisko
        <input id="user-name" type="text" name="name" value="{{ old('name', $editedUser->name ?? '') }}" autocomplete="name" required autofocus
            @error('name') aria-invalid="true" aria-describedby="user-name-error" @enderror>
        @error('name') <span id="user-name-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Adres e-mail
        <input id="user-email" type="email" name="email" value="{{ old('email', $editedUser->email ?? '') }}" autocomplete="email" required
            @error('email') aria-invalid="true" aria-describedby="user-email-error" @enderror>
        @error('email') <span id="user-email-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Rola systemowa
        <select id="user-role" name="role" required @error('role') aria-invalid="true" aria-describedby="user-role-error" @enderror>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" @selected($currentRole === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('role') <span id="user-role-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Numer telefonu
        <input id="user-phone" type="tel" name="phone" value="{{ old('phone', $editedUser->phone ?? '') }}" autocomplete="tel" inputmode="tel"
            @error('phone') aria-invalid="true" aria-describedby="user-phone-error" @enderror>
        @error('phone') <span id="user-phone-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Hasło {{ $isEdit ? '(pozostaw puste bez zmiany)' : '' }}
        <input id="user-password" type="password" name="password" {{ $isEdit ? '' : 'required' }} autocomplete="new-password"
            aria-describedby="user-password-help @error('password') user-password-error @enderror"
            @error('password') aria-invalid="true" @enderror>
        <span id="user-password-help" class="form-help">Minimum 12 znaków, mała i wielka litera oraz cyfra.</span>
        @error('password') <span id="user-password-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Powtórz hasło
        <input id="user-password-confirmation" type="password" name="password_confirmation" {{ $isEdit ? '' : 'required' }} autocomplete="new-password"
            @error('password_confirmation') aria-invalid="true" aria-describedby="user-password-confirmation-error" @enderror>
        @error('password_confirmation') <span id="user-password-confirmation-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <h2 class="admin-section-title form-grid--span-full">Uprawnienia i widoczność</h2>

    <label class="admin-check-option">
        <input type="hidden" name="is_active" value="0">
        <input id="user-active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $editedUser->is_active ?? true))
            @error('is_active') aria-invalid="true" aria-describedby="user-active-error" @enderror>
        <span>
            <strong>Konto aktywne</strong><br>
            Użytkownik może się logować.
        </span>
        @error('is_active') <span id="user-active-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="is_trainer" value="0">
        <input id="user-trainer" type="checkbox" name="is_trainer" value="1" data-trainer-toggle aria-controls="user-trainer-bio" @checked(old('is_trainer', $editedUser->is_trainer ?? false))
            @error('is_trainer') aria-invalid="true" aria-describedby="user-trainer-error" @enderror>
        <span>
            <strong>Trener</strong><br>
            Osoba pojawi się na publicznej liście trenerów.
        </span>
        @error('is_trainer') <span id="user-trainer-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="has_range_access" value="0">
        <input id="user-range-access" type="checkbox" name="has_range_access" value="1" @checked(old('has_range_access', $editedUser->has_range_access ?? false))
            @error('has_range_access') aria-invalid="true" aria-describedby="user-range-access-error" @enderror>
        <span>
            <strong>Dostęp do strzelnicy</strong><br>
            Osoba pojawi się na liście dostępu elektronicznego.
        </span>
        @error('has_range_access') <span id="user-range-access-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="show_email_publicly" value="0">
        <input id="user-public-email" type="checkbox" name="show_email_publicly" value="1" @checked(old('show_email_publicly', $editedUser->show_email_publicly ?? false))
            @error('show_email_publicly') aria-invalid="true" aria-describedby="user-public-email-error" @enderror>
        <span>
            <strong>Pokazuj e-mail publicznie</strong><br>
            Dotyczy stron kontaktowych i funkcji klubowych.
        </span>
        @error('show_email_publicly') <span id="user-public-email-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="show_phone_publicly" value="0">
        <input id="user-public-phone" type="checkbox" name="show_phone_publicly" value="1" @checked(old('show_phone_publicly', $editedUser->show_phone_publicly ?? false))
            @error('show_phone_publicly') aria-invalid="true" aria-describedby="user-public-phone-error" @enderror>
        <span>
            <strong>Pokazuj telefon publicznie</strong><br>
            Dotyczy trenerów i osób funkcyjnych.
        </span>
        @error('show_phone_publicly') <span id="user-public-phone-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="form-grid--span-full" data-trainer-bio-field>
        Opis trenera
        <textarea id="user-trainer-bio" name="trainer_bio" data-trainer-bio
            aria-describedby="user-trainer-bio-help @error('trainer_bio') user-trainer-bio-error @enderror"
            @error('trainer_bio') aria-invalid="true" @enderror>{{ old('trainer_bio', $editedUser->trainer_bio ?? '') }}</textarea>
        <span id="user-trainer-bio-help" class="form-help">Pole jest dostępne wyłącznie po zaznaczeniu opcji „Trener”. Odznaczenie opcji usuwa zapisany opis.</span>
        @error('trainer_bio') <span id="user-trainer-bio-error" class="form-error">{{ $message }}</span> @enderror
    </label>
</div>

<div class="form-actions form-actions--sticky">
    <button type="submit" class="btn btn-primary">Zapisz użytkownika</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
