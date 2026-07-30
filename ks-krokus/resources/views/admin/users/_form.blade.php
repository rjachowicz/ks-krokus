@php
    $isEdit = isset($editedUser);
    $currentRole = old('role', $editedUser->role->value ?? \App\Enums\UserRole::User->value);
@endphp

<div class="admin-form-grid">
    <label>
        Imię i nazwisko
        <input type="text" name="name" value="{{ old('name', $editedUser->name ?? '') }}" required>
        @error('name') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Adres e-mail
        <input type="email" name="email" value="{{ old('email', $editedUser->email ?? '') }}" required>
        @error('email') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Rola systemowa
        <select name="role" required>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" @selected($currentRole === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('role') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Numer telefonu
        <input type="text" name="phone" value="{{ old('phone', $editedUser->phone ?? '') }}">
        @error('phone') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Hasło {{ $isEdit ? '(pozostaw puste bez zmiany)' : '' }}
        <input type="password" name="password" {{ $isEdit ? '' : 'required' }} autocomplete="new-password">
        @error('password') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Powtórz hasło
        <input type="password" name="password_confirmation" {{ $isEdit ? '' : 'required' }} autocomplete="new-password">
    </label>

    <div class="span-full">
        <h3 class="admin-section-title">Uprawnienia i widoczność</h3>
    </div>

    <label class="admin-check-option">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editedUser->is_active ?? true))>
        <span>
            <strong>Konto aktywne</strong><br>
            Użytkownik może się logować.
        </span>
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="is_trainer" value="0">
        <input type="checkbox" name="is_trainer" value="1" @checked(old('is_trainer', $editedUser->is_trainer ?? false))>
        <span>
            <strong>Trener</strong><br>
            Osoba pojawi się na publicznej liście trenerów.
        </span>
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="has_range_access" value="0">
        <input type="checkbox" name="has_range_access" value="1" @checked(old('has_range_access', $editedUser->has_range_access ?? false))>
        <span>
            <strong>Dostęp do strzelnicy</strong><br>
            Osoba pojawi się na liście dostępu elektronicznego.
        </span>
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="show_email_publicly" value="0">
        <input type="checkbox" name="show_email_publicly" value="1" @checked(old('show_email_publicly', $editedUser->show_email_publicly ?? false))>
        <span>
            <strong>Pokazuj e-mail publicznie</strong><br>
            Dotyczy stron kontaktowych i funkcji klubowych.
        </span>
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="show_phone_publicly" value="0">
        <input type="checkbox" name="show_phone_publicly" value="1" @checked(old('show_phone_publicly', $editedUser->show_phone_publicly ?? false))>
        <span>
            <strong>Pokazuj telefon publicznie</strong><br>
            Dotyczy trenerów i osób funkcyjnych.
        </span>
    </label>

    <label class="span-full">
        Opis trenera
        <textarea name="trainer_bio">{{ old('trainer_bio', $editedUser->trainer_bio ?? '') }}</textarea>
        <span class="form-help">Pole wykorzystywane wyłącznie przy zaznaczonej opcji „Trener”.</span>
        @error('trainer_bio') <span class="form-error">{{ $message }}</span> @enderror
    </label>
</div>

<div class="admin-actions">
    <button type="submit" class="btn btn-primary">Zapisz użytkownika</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
