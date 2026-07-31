# ARCHITECTURE.md

## Routing

Routing znajduje się w `routes/web.php`.

Kontrolery publiczne:
- `HomeController`
- `NewsController`
- `CalendarController`
- `ResultsController`
- `ClubController`
- `ContactController`

Kontrolery administracyjne:
- `DashboardController`
- `PostController`
- `SportEventController`
- `EventResultController`
- `UserController`
- `ClubPositionController`
- `CompetitionDefinitionController`

## Autoryzacja

Panel korzysta z:
- `auth`,
- `active`,
- middleware roli.

Nie wystarczy ukryć przycisk w Blade. Operacja musi być zabezpieczona po stronie serwera.

## Enumy

Projekt używa m.in.:
- `UserRole`
- `EventType`
- `Discipline`
- `CompetitionSystem`

Korzystaj z istniejących metod enumów zamiast duplikować tablice.

## Kalendarz

`CalendarController@index`:
- waliduje miesiąc i rok,
- tworzy datę wyświetlanego miesiąca,
- pobiera publiczne wydarzenia,
- zachowuje filtry,
- wyznacza zakres tygodni od poniedziałku do niedzieli,
- przekazuje dni i nawigację do widoku.

Aktualne ograniczenie: wydarzenia są grupowane po `start_at`. Wydarzenia wielodniowe mogą wymagać mapowania na każdy dzień zakresu bez duplikowania rekordów w bazie.

Filtry enumów powinny być walidowane przez `Rule::enum(...)` lub `Rule::in(...)`, nie jako dowolny string.

## Audyt formularza

Zawsze sprawdź:
1. trasę,
2. kontroler,
3. Form Request,
4. model i casty,
5. migrację,
6. create/edit,
7. partial formularza,
8. upload,
9. zapis pliku,
10. usuwanie starego pliku,
11. komunikat sukcesu,
12. test Feature.

## Uploady

Sprawdź:
- `multipart/form-data`,
- nazwy pól,
- reguły `file`, `image`, `mimes`, `mimetypes`, `max`,
- `post_max_size`,
- `upload_max_filesize`,
- konfigurację dysku,
- `storage:link`,
- podmianę i usuwanie plików,
- działanie na Railway.

## Kontakt

Formularz kontaktowy powinien mieć:
- dedykowany Form Request,
- kontroler/metodę store,
- Mailable,
- widok wiadomości,
- rate limiting,
- honeypot,
- polskie komunikaty,
- testy z `Mail::fake()`.
