@php
    $selectedUsers = old(
        'user_ids',
        isset($position) ? $position->users->pluck('id')->all() : [],
    );
    $userSortOrders = old(
        'user_sort_orders',
        isset($position)
            ? $position->users->mapWithKeys(
                fn ($user) => [$user->id => $user->pivot->sort_order],
            )->all()
            : [],
    );
@endphp

<div class="admin-form-grid">
    <label>
        Nazwa funkcji
        <input type="text" name="name" value="{{ old('name', $position->name ?? '') }}" required>
        @error('name') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Identyfikator URL
        <input type="text" name="slug" value="{{ old('slug', $position->slug ?? '') }}" placeholder="np. prezes">
        <span class="form-help">Możesz pozostawić puste — system utworzy slug z nazwy.</span>
        @error('slug') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Kolejność
        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $position->sort_order ?? 0) }}" required>
        @error('sort_order') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $position->is_active ?? true))>
        <span>
            <strong>Funkcja aktywna</strong><br>
            Widoczna na publicznej stronie kontaktowej.
        </span>
    </label>

    <label class="span-full">
        Opis
        <textarea name="description">{{ old('description', $position->description ?? '') }}</textarea>
        @error('description') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <div class="span-full">
        <h3 class="admin-section-title">Przypisani użytkownicy</h3>
        <p class="form-help">
            Zaznacz osoby i ustaw ich kolejność. Niższa liczba oznacza wcześniejsze miejsce.
            Dane kontaktowe są pokazywane zgodnie z ustawieniami profilu użytkownika.
        </p>
    </div>

    <div class="admin-check-grid span-full">
        @foreach ($users as $user)
            <label class="admin-check-option">
                <input
                    type="checkbox"
                    name="user_ids[]"
                    value="{{ $user->id }}"
                    @checked(in_array($user->id, array_map('intval', $selectedUsers), true))
                >
                <span>
                    <strong>{{ $user->name }}</strong><br>
                    {{ $user->email }}

                    <span class="admin-inline-order">
                        Kolejność:
                        <input
                            type="number"
                            name="user_sort_orders[{{ $user->id }}]"
                            min="0"
                            max="9999"
                            value="{{ $userSortOrders[$user->id] ?? $loop->iteration }}"
                        >
                    </span>
                </span>
            </label>
        @endforeach
    </div>
</div>

<div class="admin-actions">
    <button type="submit" class="btn btn-primary">Zapisz funkcję</button>
    <a href="{{ route('admin.positions.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
