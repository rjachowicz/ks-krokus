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

Kontrolery konta zalogowanego użytkownika:
- `AccountController` — podgląd własnego konta oraz rozdzielone aktualizacje profilu,
  adresu e-mail i hasła pod `/moje-konto`

Kontrolery administracyjne:
- `DashboardController`
- `PostController`
- `SportEventController`
- `EventResultController`
- `UserController`
- `ClubPositionController`
- `CompetitionDefinitionController`
- `AccountRequestController` — kolejka, szczegóły, notatki i decyzje administratora
- `MemberProfileController` — administracyjna edycja zweryfikowanych danych członkowskich

## Autoryzacja

Panel korzysta z:
- `auth`,
- `active`,
- middleware roli.

Nie wystarczy ukryć przycisk w Blade. Operacja musi być zabezpieczona po stronie serwera.

`/moje-konto*` korzysta z `auth` i `active`, nie przyjmuje identyfikatora użytkownika
i zawsze działa na koncie z sesji. Osobne Form Requesty ograniczają edycję profilu,
e-maila i hasła. Zwykła aktualizacja profilu nie przyjmuje roli, aktywności, funkcji
klubowych ani danych weryfikacyjnych. Administracyjne dane członkowskie chronią
jednocześnie middleware roli administratora i `MemberProfilePolicy`; moderator nie
ma dostępu do profili innych osób. Policy zezwala każdemu aktywnemu użytkownikowi
na podgląd własnego profilu, ale jego edycja pozostaje wyłącznie administracyjna.

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

Zatwierdzenie tworzy też dokładnie jeden `MemberProfile` w tej samej transakcji co
konto. Kopiowane są numery licencji PZSS, patentu, pozwolenia i członkowski, ważność
licencji, rok wstąpienia oraz dyscypliny. Profil otrzymuje status `verified`, czas
i administratora weryfikacji. Unikalność `user_id` i numeru licencji oraz
`updateOrCreate` chronią przed duplikatem; ponowne zatwierdzenie nie nadpisuje
istniejącego profilu. Migracja tworząca tabelę uzupełnia profile dla zatwierdzonych
wcześniej wniosków z `created_user_id`.

Odrzucenie wymaga powodu, zachowuje rekord i wysyła neutralną wiadomość bez powodu
oraz bez notatek wewnętrznych. Trasy `/panel/wnioski-o-konto*` są objęte
middleware `auth`, `active` i rolą `admin`.

Ustawienie hasła działa przez `/ustaw-haslo/{token}` i `POST /ustaw-haslo`.
Kontroler korzysta z `Password::reset`, rotuje `remember_token` i emituje standardowe
zdarzenie `PasswordReset`.

Publiczne odzyskiwanie hasła działa przez `/nie-pamietam-hasla` i standardowy
broker Laravel. Odpowiedź jest taka sama dla konta aktywnego, nieaktywnego i
nieistniejącego. Broker ogranicza tworzenie tokenów, trasa ma dodatkowy throttling,
a reset ponownie sprawdza `is_active`. `User::sendPasswordResetNotification()`
wysyła polskie, kolejkowane powiadomienie z absolutnym adresem opartym o `APP_URL`.

`PasswordSetupLinkService` obsługuje administracyjne ponowne wysłanie linku przez
broker. Działa wyłącznie dla aktywnego użytkownika, nie tworzy konta i zapisuje w
`users.password_link_sent_by/password_link_sent_at` ostatniego administratora
oraz czas. Kolejkowane zadania z tokenem są szyfrowane; token nigdy nie trafia do
interfejsu ani jawnych wpisów logu.

## Moje konto i dane członkowskie

`MemberProfile` jest relacją 1:1 z `User`. Przechowuje minimalny zestaw danych
członkowskich, JSON dyscyplin oraz audyt weryfikacji. Użytkownik widzi te pola tylko
do odczytu. Administrator edytuje je na osobnym ekranie powiązanym z użytkownikiem;
notatki wewnętrzne pozostają w źródłowym `AccountRequest`, do którego ekran profilu
prowadzi odnośnikiem.

Zmiana e-maila wymaga reguły `current_password:web`, normalizuje adres przed regułą
unikalności i zwraca neutralny błąd konfliktu. Kolumna `email_verified_at` istnieje,
ale aplikacja nie implementuje `MustVerifyEmail` ani tras weryfikacji, dlatego zmiana
adresu nie zeruje tej wartości i nie uruchamia połowicznego procesu weryfikacji.

Zmiana hasła używa tej samej polityki co ustawienie pierwszego hasła: minimum 12
znaków, litery, mała i wielka litera oraz cyfra. Hasło jest zapisywane przez
`Hash::make`, a rotacja `remember_token` unieważnia trwałe logowania. Aplikacja nie
usuwa obecnie aktywnych sesji bazodanowych na innych urządzeniach.

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
używają kanału bazodanowego i kolejki `database`. Wiadomości SMTP — kontakt,
odrzucenie wniosku, ustawienie i reset hasła — także są kolejkowane, aby nie
blokować żądania HTTP. Produkcja wymaga osobnej, stale działającej usługi
`php artisan queue:work --sleep=3 --tries=3 --timeout=90`; `retry_after` wynosi
120 sekund, a zadania są publikowane po zatwierdzeniu transakcji.

Komenda `php artisan listings:expire` wysyła jednokrotne przypomnienie 7 dni
przed terminem i zmienia przeterminowane zatwierdzone oferty na `expired`.
Laravel planuje ją codziennie o 01:15 w strefie `Europe/Warsaw`.
