# AI_PROGRESS.md

## Branch bazowy

`v.0.4-refactor-calendar-and-admin-panel`

## Stan początkowy

### Zrobione

- [x] Laravel 13 / PHP 8.4.
- [x] Polskie publiczne trasy.
- [x] Panel `/panel`.
- [x] Middleware `auth`, `active` i role.
- [x] CRUD aktualności.
- [x] CRUD wydarzeń.
- [x] CRUD wyników.
- [x] CRUD użytkowników.
- [x] CRUD funkcji klubowych.
- [x] CRUD konkurencji.
- [x] Kalendarz miesięczny.
- [x] Nawigacja miesiącami.
- [x] Parametry miesiąca i roku.
- [x] Filtry kalendarza.
- [x] Tydzień poniedziałek–niedziela.

### Do sprawdzenia

- [ ] `validation.uploaded`.
- [ ] Polska walidacja.
- [ ] Uploady.
- [ ] Checkboxy.
- [ ] Selecty.
- [ ] Daty i czas.
- [ ] Light/dark.
- [ ] Formularz kontaktowy.
- [ ] Dostępność.
- [ ] Responsywność.
- [ ] Wydarzenia wielodniowe.
- [ ] Walidacja filtrów enumami.

## Bieżąca sesja

### Cel

Przełączyć środowisko lokalne i testowe z SQLite na PostgreSQL bez utraty
istniejących danych aplikacyjnych.

### Wykonane

- [x] Ustawiono PostgreSQL jako domyślne połączenie aplikacji.
- [x] Zmieniono przykładową konfigurację środowiska, kolejki i PHPUnit.
- [x] Usunięto tworzenie pliku SQLite z instalacyjnego skryptu Composera.
- [x] Utworzono lokalną rolę `ks_krokus` oraz bazy `ks_krokus`
  i `ks_krokus_testing`.
- [x] Uruchomiono wszystkie istniejące migracje na PostgreSQL 18.4.
- [x] Skopiowano dane aplikacyjne z SQLite i zsynchronizowano sekwencje.
- [x] Zweryfikowano liczebność danych i integralność kluczy obcych.

### Zmienione pliki

- `ks-krokus/.env.example`
- `ks-krokus/composer.json`
- `ks-krokus/config/database.php`
- `ks-krokus/config/queue.php`
- `ks-krokus/phpunit.xml`
- `docs/AI_PROGRESS.md`
- `docs/TODO.md`
- lokalnie także ignorowany przez Git plik `ks-krokus/.env`

### Testy

- [x] `composer test`
- [x] `vendor/bin/pint --test`
- [x] `npm run build`

### Wyniki

- PHPUnit: 26 testów, 100 asercji, wszystkie poprawne na PostgreSQL.
- Pint: bez błędów formatowania.
- Vite: build produkcyjny zakończony poprawnie.
- `composer validate --no-check-publish`: poprawny `composer.json`.

### Migracje

Nie dodano nowej migracji. W bazie `ks_krokus` wykonano komplet 9
istniejących migracji, do
`2026_07_31_000600_add_content_format_to_posts_table.php` włącznie.

### Zmienne środowiskowe

- `DB_CONNECTION=pgsql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=5432`
- `DB_DATABASE=ks_krokus`
- `DB_USERNAME=ks_krokus`
- `DB_PASSWORD` — unikalne hasło zapisane wyłącznie w lokalnym `.env`
- `DB_SSLMODE=prefer` — udokumentowane w `.env.example`

### Ograniczenia

Plik `database/database.sqlite` pozostaje lokalnie jako kopia bezpieczeństwa
i nie jest już używany przez aplikację. Na innym stanowisku trzeba utworzyć
rolę oraz bazy PostgreSQL i uzupełnić lokalne hasło. Testy oczekują osobnej
bazy `ks_krokus_testing`.

## Historia

Dodawaj:

```md
### YYYY-MM-DD — etap

- Cel:
- Wykonano:
- Testy:
- Commit:
- Pozostało:
```

### 2026-07-31 — lokalny PostgreSQL

- Cel: zastąpić SQLite lokalnym PostgreSQL bez utraty danych.
- Wykonano: konfigurację aplikacji i testów, dwie bazy, migracje oraz
  przeniesienie danych.
- Testy: 26 testów / 100 asercji, Pint i build zakończone poprawnie.
- Commit: konfiguracja lokalnego PostgreSQL (bieżący commit).
- Pozostało: usunąć kopię SQLite dopiero po ręcznym potwierdzeniu działania.
