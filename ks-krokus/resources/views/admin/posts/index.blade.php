@extends('layouts.admin')

@section('title', 'Aktualności — panel KS Krokus')
@section('admin_title', 'Aktualności')

@section('content')
    <x-admin-page-header
        title="Aktualności"
        description="Wpisy tekstowe, zdjęcia główne i galerie publikowane na stronie klubu."
    >
        <x-slot:actions>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">Dodaj aktualność</a>
        </x-slot:actions>
    </x-admin-page-header>

    <form method="GET" class="admin-filter panel-card">
        <label>
            Szukaj
            <input id="post-filter-query" type="search" name="q" value="{{ request('q') }}" placeholder="Tytuł lub opis" autocomplete="off"
                @error('q') aria-invalid="true" aria-describedby="post-filter-query-error" @enderror>
            @error('q') <span id="post-filter-query-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <label>
            Status
            <select id="post-filter-status" name="status" @error('status') aria-invalid="true" aria-describedby="post-filter-status-error" @enderror>
                <option value="">Wszystkie</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <span id="post-filter-status-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <button type="submit" class="btn btn-primary">Filtruj</button>
        @if (request()->hasAny(['q', 'status']))
            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Wyczyść</a>
        @endif
    </form>

    <div class="admin-table-wrap" role="region" aria-label="Lista aktualności" tabindex="0">
        <table class="admin-table">
            <caption class="sr-only">Lista aktualności</caption>
            <thead>
                <tr>
                    <th scope="col">Tytuł</th>
                    <th scope="col">Autor</th>
                    <th scope="col">Status</th>
                    <th scope="col">Publikacja</th>
                    <th scope="col">Operacje</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <td data-label="Tytuł">
                            <strong>{{ $post->title }}</strong><br>
                            <code>{{ $post->slug }}</code>
                        </td>
                        <td data-label="Autor">{{ $post->author?->name ?? '—' }}</td>
                        <td data-label="Status">
                            <span class="admin-badge {{ $post->status === \App\Enums\PublicationStatus::Published ? 'admin-badge--success' : 'admin-badge--warning' }}">
                                {{ $post->status->label() }}
                            </span>
                        </td>
                        <td data-label="Publikacja">{{ $post->published_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td data-label="Operacje">
                            <div class="admin-table__actions">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-secondary"
                                    aria-label="Edytuj aktualność: {{ $post->title }}">Edytuj</a>

                                @if ($post->isPubliclyVisible())
                                    <a
                                        href="{{ route('news.show', $post) }}"
                                        class="btn btn-secondary"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label="Podgląd aktualności: {{ $post->title }} — otwiera w nowej karcie"
                                    >
                                        Podgląd
                                    </a>
                                @endif

                                <form
                                    method="POST"
                                    action="{{ route('admin.posts.destroy', $post) }}"
                                    data-confirm="Przenieść aktualność „{{ $post->title }}” do kosza? Zniknie ze strony publicznej."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-outline"
                                        aria-label="Usuń aktualność: {{ $post->title }}">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">
                        <div class="empty-state">
                            {{ request()->hasAny(['q', 'status'])
                                ? 'Brak aktualności spełniających wybrane kryteria.'
                                : 'Nie dodano jeszcze żadnej aktualności.' }}
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $posts->links() }}
@endsection
