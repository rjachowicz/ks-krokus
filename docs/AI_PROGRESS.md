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

Usunąć obsługę SQLite i pozostałych silników bazodanowych, tak aby aplikacja
udostępniała wyłącznie połączenie PostgreSQL.

### Wykonane

- [x] Ograniczono `config/database.php` do połączenia `pgsql`.
- [x] Odfiltrowano domyślne połączenia Laravel podczas rejestracji providerów.
- [x] Potwierdzono pojedyncze połączenie `pgsql` także po `config:cache`.
- [x] Usunięto regułę `database/.gitignore` przeznaczoną dla plików SQLite.
- [x] Usunięto lokalny plik `database/database.sqlite` po weryfikacji danych
  w PostgreSQL.
- [x] Dodano test regresyjny konfiguracji bazy danych.
- [x] Potwierdzono brak odwołań do SQLite w plikach aplikacji.

### Zmienione pliki

- `ks-krokus/app/Providers/AppServiceProvider.php`
- `ks-krokus/config/database.php`
- `ks-krokus/database/.gitignore` — usunięty
- `ks-krokus/tests/Feature/DatabaseConfigurationTest.php`
- `docs/AI_PROGRESS.md`
- `docs/TODO.md`
- lokalny `ks-krokus/database/database.sqlite` — usunięty

### Testy

- [x] `composer test`
- [x] `vendor/bin/pint --test`
- [x] `npm run build`

### Wyniki

- PHPUnit: 27 testów, 103 asercje, wszystkie poprawne na PostgreSQL.
- Pint: bez błędów formatowania.
- Vite: build produkcyjny zakończony poprawnie.
- Konfiguracja zwykła i cache: dostępne wyłącznie połączenie `pgsql`.

### Migracje

Nie dodano ani nie uruchamiano nowych migracji. PostgreSQL nadal zawiera
komplet 9 wykonanych migracji.

### Zmienne środowiskowe

Bez zmian. Wskazana przez użytkownika wartość `ADMIN_PASSWORD` w lokalnym
`.env` nie została zmodyfikowana.

### Ograniczenia

Aplikacja nie ma zapasowego połączenia bazodanowego: do uruchomienia wymaga
dostępnego PostgreSQL. Usunięty plik SQLite nie był śledzony przez Git; jego
dane pozostają dostępne w lokalnej bazie PostgreSQL.

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

### 2026-07-31 — wyłącznie PostgreSQL

- Cel: usunąć konfigurację i lokalne dane SQLite oraz inne nieużywane
  połączenia.
- Wykonano: pojedyncze połączenie runtime `pgsql`, usunięcie pliku SQLite
  i test regresyjny konfiguracji.
- Testy: 27 testów / 103 asercje, Pint i build zakończone poprawnie.
- Commit: wyłącznie konfiguracja PostgreSQL (bieżący commit).
- Pozostało: brak w zakresie konfiguracji bazy danych.
