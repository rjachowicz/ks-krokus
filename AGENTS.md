# AGENTS.md — KS Krokus

## Kontekst

Pracujesz nad repozytorium `rjachowicz/ks-krokus`.

Branch bazowy: `v.0.4-refactor-calendar-and-admin-panel`.

Aplikacja Laravel znajduje się w podkatalogu `ks-krokus`.

Przed rozpoczęciem każdej pracy przeczytaj:

1. `AGENTS.md`
2. `docs/PROJECT_GUIDE.md`
3. `docs/ARCHITECTURE.md`
4. `docs/UI_GUIDE.md`
5. `docs/VALIDATION_GUIDE.md`
6. `docs/AI_PROGRESS.md`
7. `docs/TODO.md`

Następnie sprawdź aktualny branch i `git status`, przeanalizuj pliki powiązane z zadaniem, przedstaw krótki plan, wykonaj zmiany, uruchom testy i build, a na końcu zaktualizuj `AI_PROGRESS.md` oraz `TODO.md`.

## Projekt

- Laravel 13
- PHP 8.4
- PostgreSQL
- Blade
- własny CSS i JavaScript
- Vite
- Railway
- interfejs po polsku
- strefa czasowa `Europe/Warsaw`

Projekt zawiera część publiczną i panel administracyjny.

Część publiczna:
- strona główna,
- aktualności,
- kalendarz,
- wyniki,
- klub,
- kontakt,
- regulamin,
- RODO,
- logowanie.

Panel:
- dashboard,
- aktualności,
- wydarzenia,
- wyniki,
- użytkownicy,
- funkcje klubowe,
- konkurencje.

## Zasady techniczne

### Laravel i PHP

- Zachowuj Laravel 13 i PHP 8.4.
- Stosuj `declare(strict_types=1);`, gdy projekt używa tej konwencji.
- Zachowuj PSR-12.
- Używaj Form Requestów do walidacji.
- Korzystaj z istniejących enumów.
- Zachowuj named routes i route model binding.
- Nie twórz zbędnych warstw typu repository.
- Nie zmieniaj logiki biznesowej bez uzasadnienia.
- Dla zmian schematu twórz nowe migracje.
- Nie modyfikuj starych migracji uruchomionych produkcyjnie.
- Zachowuj kompatybilność z PostgreSQL.
- Nie zapisuj sekretów w repozytorium.

### Blade

- Każde pole musi mieć etykietę.
- Formularze muszą zachowywać dane przez `old()`.
- Błędy pokazuj pod konkretnym polem.
- Dodawaj `aria-invalid="true"` i `aria-describedby`.
- Podsumowanie błędów powinno mieć `role="alert"`.
- Nie renderuj niesanitowanego HTML.
- Nie duplikuj dużych fragmentów formularzy.

### CSS

- Nie dodawaj Bootstrap.
- Nie dodawaj Tailwind bez jednoznacznego polecenia.
- Korzystaj z istniejących zmiennych CSS.
- Zachowuj obecny podział arkuszy.
- Zapewnij `hover`, `focus-visible`, `disabled` i `invalid`.
- Projekt ma działać od 320 px.
- Nie dopuszczaj do poziomego scrolla całej strony.
- Tabele muszą mieć bezpieczny scroll na telefonie.

### JavaScript

- Używaj JavaScriptu tylko tam, gdzie poprawia UX.
- Nie dodawaj ciężkich bibliotek bez uzasadnienia.
- Nie zapisuj danych wrażliwych w `localStorage`.
- Kod musi przechodzić przez `npm run build`.

## Język interfejsu

Cały interfejs ma być po polsku.

Preferowane nazwy:
- „aktualność” zamiast „post”,
- „wydarzenie” zamiast „event”,
- „użytkownik” zamiast „user”,
- „konkurencja” zamiast „competition”.

Niedopuszczalne komunikaty:
- `validation.required`,
- `validation.uploaded`,
- `validation.max.file`,
- `validation.custom`,
- angielskie komunikaty Laravela,
- techniczne nazwy pól.

## Stan brancha v0.4

Branch zawiera już:
- przebudowany kalendarz miesięczny,
- zakres tygodni od poniedziałku do niedzieli,
- parametry `month` i `year`,
- filtry rodzaju wydarzenia, dyscypliny i systemu,
- panel administracyjny oparty o zasoby Laravel,
- middleware `auth`, `active` oraz role,
- polskie publiczne adresy tras.

Nie cofaj tych zmian.

Kalendarz grupuje obecnie wydarzenia po dacie rozpoczęcia. Wydarzenia wielodniowe mogą wymagać dodatkowej obsługi bez duplikowania rekordów w bazie.

## Bieżące priorytety

1. Audyt formularzy panelu.
2. Naprawa uploadów i `validation.uploaded`.
3. Pełne polskie komunikaty.
4. Ujednolicenie checkboxów, selectów i pól daty.
5. Motyw light/dark w panelu.
6. Formularz kontaktowy do `zarzad@ks-krokus.pl`.
7. Dostępność i responsywność.
8. Testy regresyjne.

## Kończenie zadania

Po każdym zadaniu:

1. Uruchom `composer test`.
2. Uruchom `vendor/bin/pint --test`.
3. Uruchom `npm run build`.
4. Napraw błędy.
5. Sprawdź `git diff`.
6. Zaktualizuj `docs/AI_PROGRESS.md`.
7. Zaktualizuj `docs/TODO.md`.
8. Zrób logiczny commit.
9. Podaj zmienione pliki, testy, migracje, nowe zmienne środowiskowe i znane ograniczenia.
