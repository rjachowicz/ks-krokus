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
- `AccountRequestController` — publiczne złożenie wniosku bez automatycznej rejestracji

Kontrolery administracyjne:
- `DashboardController`
- `PostController`
- `SportEventController`
- `EventResultController`
- `UserController`
- `ClubPositionController`
- `CompetitionDefinitionController`
- `AccountRequestController` — kolejka, szczegóły, notatki i decyzje administratora

## Autoryzacja

Panel korzysta z:
- `auth`,
- `active`,
- middleware roli.

Nie wystarczy ukryć przycisk w Blade. Operacja musi być zabezpieczona po stronie serwera.

## Enumy

Projekt używa m.in.:
- `AccountRequestStatus`
- `UserRole`
- `EventType`
- `Discipline`
- `CompetitionSystem`

Korzystaj z istniejących metod enumów zamiast duplikować tablice.

## Wnioski o konto

`AccountRequest` przechowuje dane weryfikacyjne, status, audyt decyzji i relację
do utworzonego konta. Publiczny POST jest chroniony przez CSRF, limit 3 prób na
godzinę, honeypot, normalizację e-maila i numeru licencji oraz unikalne indeksy.
Duplikaty nie ujawniają istnienia konta — odpowiedź publiczna jest zawsze taka sama.

`AccountRequestWorkflow` jest jedynym miejscem zatwierdzania i odrzucania.
Zatwierdzenie blokuje wniosek i w jednej transakcji ponownie sprawdza duplikaty,
tworzy aktywnego użytkownika z rolą `user`, zapisuje audyt i generuje token brokera
haseł Laravel. Wiadomość z tokenem jest kolejkowana po zatwierdzeniu transakcji.
Ponowne zatwierdzenie zwraca już utworzone konto i nie tworzy duplikatu.

Odrzucenie wymaga powodu, zachowuje rekord i wysyła neutralną wiadomość bez powodu
oraz bez notatek wewnętrznych. Trasy `/panel/wnioski-o-konto*` są objęte
middleware `auth`, `active` i rolą `admin`.

Ustawienie hasła działa przez `/ustaw-haslo/{token}` i `POST /ustaw-haslo`.
Kontroler korzysta z `Password::reset`, rotuje `remember_token` i emituje standardowe
zdarzenie `PasswordReset`.

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

## Ogłoszenia sprzedaży

Moduł jest moderowaną tablicą informacyjną bez płatności, koszyka, rezerwacji ani
pośrednictwa klubu. Publiczne trasy to `/ogloszenia` i
`/ogloszenia/{saleListing}`, a strefa właściciela działa pod
`/panel/moje-ogloszenia`. Kolejka moderacji jest dostępna pod
`/panel/ogloszenia` dla moderatora i administratora.

Modele:

- `SaleListing` — oferta, autor, status, prywatność kontaktu i daty audytowe,
- `SaleListingImage` — zdjęcia, miniatury, kolejność i zdjęcie główne,
- `SaleListingModeration` — historia przejść i działań moderatorów,
- `SaleListingReport` — publiczne zgłoszenia z deduplikacją.

Statusy są wartościami `SaleListingStatus`. Wszystkie przejścia wykonuje
`SaleListingWorkflow`, który zapisuje historię i wysyła powiadomienia. Policy
`SaleListingPolicy` chroni zarówno operacje właściciela, jak i moderację. Tylko
administrator zatwierdza i odrzuca; moderator może edytować, ukrywać i oznaczać
rekord do uwagi administratora.

Scope `SaleListing::publiclyVisible()` wymaga równocześnie statusu `approved`,
daty publikacji nie późniejszej niż teraz, braku ukrycia, braku soft delete oraz
ważnego terminu. Widok kontaktu dodatkowo respektuje `show_phone` i `show_email`.

Upload używa tego samego `MEDIA_DISK` co aktualności. `SaleListingImageStorage`
zapisuje najpierw bezpieczną kopię, a przy dostępnym GD koryguje orientację JPEG,
skaluje bez powiększania, optymalizuje obraz i generuje miniaturę. Przy błędzie
przetwarzania zachowuje poprawnie zapisany oryginał. Soft delete nie usuwa plików,
ponieważ rekord można przywrócić; usunięcie pojedynczego zdjęcia sprząta obraz i
miniaturę po zatwierdzeniu transakcji bazy.

Powiadomienia o wysłaniu, zatwierdzeniu, odrzuceniu i zbliżającym się wygaśnięciu
używają kanału bazodanowego i kolejki `deferred`, więc nie wymagają workera i są
wykonywane po odpowiedzi HTTP. Zmiana `QUEUE_CONNECTION` na `database` wymaga
osobnej usługi `php artisan queue:work`.

Komenda `php artisan listings:expire` wysyła jednokrotne przypomnienie 7 dni
przed terminem i zmienia przeterminowane zatwierdzone oferty na `expired`.
Laravel planuje ją codziennie o 01:15 w strefie `Europe/Warsaw`.
