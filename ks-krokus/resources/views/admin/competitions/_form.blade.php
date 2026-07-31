<div class="admin-form-grid">
    <div class="span-full"><h3 class="admin-section-title">Dane konkurencji</h3></div>
    <label>
        Kod
        <input type="text" name="code" value="{{ old('code', $definition->code ?? '') }}" required>
        @error('code') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Nazwa
        <input type="text" name="name" value="{{ old('name', $definition->name ?? '') }}" required>
        @error('name') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        System
        <select name="competition_system" required>
            @foreach ($systems as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('competition_system', isset($definition) ? $definition->competition_system->value : '') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('competition_system') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Dyscyplina
        <select name="discipline" required>
            @foreach ($disciplines as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('discipline', isset($definition) ? $definition->discipline->value : '') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('discipline') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Kolejność
        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $definition->sort_order ?? 0) }}" required>
        @error('sort_order') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $definition->is_active ?? true))>
        <span>
            <strong>Konkurencja aktywna</strong><br>
            Dostępna na listach wyboru.
        </span>
    </label>

    <label class="span-full">
        Opis
        <textarea name="description">{{ old('description', $definition->description ?? '') }}</textarea>
        @error('description') <span class="form-error">{{ $message }}</span> @enderror
    </label>
</div>

<div class="admin-actions">
    <button type="submit" class="btn btn-primary">Zapisz konkurencję</button>
    <a href="{{ route('admin.competitions.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
