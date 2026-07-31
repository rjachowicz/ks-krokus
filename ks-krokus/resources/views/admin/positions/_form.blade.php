@php
    $selectedUsers = old(
        'user_ids',
        isset($position) ? $position->users->pluck('id')->all() : [],
    );
    $selectedUsers = is_array($selectedUsers)
        ? array_map('intval', $selectedUsers)
        : [];

    $userSortOrders = old(
        'user_sort_orders',
        isset($position)
            ? $position->users->mapWithKeys(
                fn ($user) => [$user->id => $user->pivot->sort_order],
            )->all()
            : [],
    );
    $userSortOrders = is_array($userSortOrders) ? $userSortOrders : [];

    $userSelectionErrorIds = implode(' ', array_filter([
        $errors->has('user_ids') ? 'position-users-error' : null,
        $errors->has('user_ids.*') ? 'position-user-items-error' : null,
    ]));
@endphp

<div class="admin-form-grid">
    <div class="span-full"><h3 class="admin-section-title">Dane funkcji</h3></div>
    <label>
        Nazwa funkcji
        <input id="position-name" type="text" name="name" value="{{ old('name', $position->name ?? '') }}" autocomplete="off" required autofocus
            @error('name') aria-invalid="true" aria-describedby="position-name-error" @enderror>
        @error('name') <span id="position-name-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Identyfikator URL
        <input id="position-slug" type="text" name="slug" value="{{ old('slug', $position->slug ?? '') }}" placeholder="np. prezes" autocomplete="off"
            aria-describedby="position-slug-help @error('slug') position-slug-error @enderror"
            @error('slug') aria-invalid="true" @enderror>
        <span id="position-slug-help" class="form-help">Możesz pozostawić puste — system utworzy slug z nazwy.</span>
        @error('slug') <span id="position-slug-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Kolejność
        <input id="position-order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $position->sort_order ?? 0) }}" required
            @error('sort_order') aria-invalid="true" aria-describedby="position-order-error" @enderror>
        @error('sort_order') <span id="position-order-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="is_active" value="0">
        <input id="position-active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $position->is_active ?? true))
            @error('is_active') aria-invalid="true" aria-describedby="position-active-error" @enderror>
        <span>
            <strong>Funkcja aktywna</strong><br>
            Widoczna na publicznej stronie kontaktowej.
        </span>
        @error('is_active') <span id="position-active-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="span-full">
        Opis
        <textarea id="position-description" name="description" autocomplete="off"
            @error('description') aria-invalid="true" aria-describedby="position-description-error" @enderror>{{ old('description', $position->description ?? '') }}</textarea>
        @error('description') <span id="position-description-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <div class="span-full">
        <h3 class="admin-section-title">Przypisani użytkownicy</h3>
        <p class="form-help">
            Zaznacz osoby i ustaw ich kolejność. Niższa liczba oznacza wcześniejsze miejsce.
            Dane kontaktowe są pokazywane zgodnie z ustawieniami profilu użytkownika.
        </p>
        @error('user_ids') <span id="position-users-error" class="form-error" role="alert">{{ $message }}</span> @enderror
        @error('user_ids.*') <span id="position-user-items-error" class="form-error" role="alert">{{ $message }}</span> @enderror
        @error('user_sort_orders.*') <span id="position-user-orders-error" class="form-error" role="alert">{{ $message }}</span> @enderror
    </div>

    <div class="admin-check-grid span-full">
        @foreach ($users as $user)
            <div class="admin-check-option">
                <input
                    id="position-user-{{ $user->id }}"
                    type="checkbox"
                    name="user_ids[]"
                    value="{{ $user->id }}"
                    @checked(in_array($user->id, $selectedUsers, true))
                    @if ($userSelectionErrorIds !== '')
                        aria-invalid="true" aria-describedby="{{ $userSelectionErrorIds }}"
                    @endif
                >
                <div class="admin-check-option__body">
                    <label for="position-user-{{ $user->id }}">
                        <strong>{{ $user->name }}</strong><br>
                        {{ $user->email }}
                    </label>

                    <label class="admin-inline-order">
                        Kolejność:
                        <input
                            id="position-user-{{ $user->id }}-order"
                            type="number"
                            name="user_sort_orders[{{ $user->id }}]"
                            min="0"
                            max="9999"
                            value="{{ $userSortOrders[$user->id] ?? $loop->iteration }}"
                            @error("user_sort_orders.{$user->id}") aria-invalid="true" aria-describedby="position-user-{{ $user->id }}-order-error" @enderror
                        >
                        @error("user_sort_orders.{$user->id}")
                            <span id="position-user-{{ $user->id }}-order-error" class="form-error">{{ $message }}</span>
                        @enderror
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="admin-form-actions">
    <button type="submit" class="btn btn-primary">Zapisz funkcję</button>
    <a href="{{ route('admin.positions.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
