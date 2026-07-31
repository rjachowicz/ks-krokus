<div class="admin-form-grid">
    <h2 class="admin-section-title span-full">Dane konkurencji</h2>
    <label>
        Kod
        <input id="competition-code" type="text" name="code" value="{{ old('code', $definition->code ?? '') }}" autocomplete="off" required autofocus
            @error('code') aria-invalid="true" aria-describedby="competition-code-error" @enderror>
        @error('code') <span id="competition-code-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Nazwa
        <input id="competition-name" type="text" name="name" value="{{ old('name', $definition->name ?? '') }}" autocomplete="off" required
            @error('name') aria-invalid="true" aria-describedby="competition-name-error" @enderror>
        @error('name') <span id="competition-name-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        System
        <select id="competition-system" name="competition_system" required
            @error('competition_system') aria-invalid="true" aria-describedby="competition-system-error" @enderror>
            @foreach ($systems as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('competition_system', isset($definition) ? $definition->competition_system->value : '') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('competition_system') <span id="competition-system-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Dyscyplina
        <select id="competition-discipline" name="discipline" required
            @error('discipline') aria-invalid="true" aria-describedby="competition-discipline-error" @enderror>
            @foreach ($disciplines as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('discipline', isset($definition) ? $definition->discipline->value : '') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('discipline') <span id="competition-discipline-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Kolejność
        <input id="competition-order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $definition->sort_order ?? 0) }}" required
            @error('sort_order') aria-invalid="true" aria-describedby="competition-order-error" @enderror>
        @error('sort_order') <span id="competition-order-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="is_active" value="0">
        <input id="competition-active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $definition->is_active ?? true))
            @error('is_active') aria-invalid="true" aria-describedby="competition-active-error" @enderror>
        <span>
            <strong>Konkurencja aktywna</strong><br>
            Dostępna na listach wyboru.
        </span>
        @error('is_active') <span id="competition-active-error" class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="span-full">
        Opis
        <textarea id="competition-description" name="description" autocomplete="off"
            @error('description') aria-invalid="true" aria-describedby="competition-description-error" @enderror>{{ old('description', $definition->description ?? '') }}</textarea>
        @error('description') <span id="competition-description-error" class="form-error">{{ $message }}</span> @enderror
    </label>
</div>

<div class="admin-form-actions">
    <button type="submit" class="btn btn-primary">Zapisz konkurencję</button>
    <a href="{{ route('admin.competitions.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
