<div class="panel-card ui-stack">
    <div class="form-help result-archive-notice" role="status">
        <strong>Wynik archiwalny — tylko do odczytu.</strong>
        Wydarzenie ma status „Archiwum”, dlatego wyniku nie można edytować, przenosić ani usuwać.
        Po ponownym opublikowaniu wydarzenia edycja będzie znów dostępna.
    </div>

    <dl class="account-data-grid">
        <div><dt>Wydarzenie</dt><dd>{{ $result->eventCompetition->event->title }}</dd></div>
        <div><dt>Konkurencja</dt><dd>{{ $result->eventCompetition->competition->name }}</dd></div>
        <div><dt>Zawodnik</dt><dd>{{ $result->displayName() }}</dd></div>
        <div><dt>Klub</dt><dd>{{ $result->club_name ?: '—' }}</dd></div>
        <div><dt>Kategoria wiekowa</dt><dd>{{ $result->categoryLabel() ?: '—' }}</dd></div>
        <div><dt>Dywizja IPSC</dt><dd>{{ $result->classificationLabel() ?: '—' }}</dd></div>
        <div><dt>Wynik</dt><dd>{{ $result->score }}</dd></div>
        <div><dt>Miejsce</dt><dd>{{ $result->place ?? '—' }}</dd></div>
        <div><dt>Status</dt><dd>{{ $result->status->label() }}</dd></div>
        <div class="account-data-grid__full"><dt>Uwagi</dt><dd>{{ $result->notes ?: '—' }}</dd></div>
    </dl>

    <div class="form-actions">
        <a href="{{ route('admin.results.index') }}" class="btn btn-secondary">Wróć do wyników</a>
    </div>
</div>
