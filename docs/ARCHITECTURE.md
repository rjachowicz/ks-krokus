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
  adresu e-mail i hasła pod `/moje-konto` oraz `/panel/moje-konto`; oba zestawy
  tras używają tych samych metod i Form Requestów
- `NotificationController` — paginowana lista bazodanowych powiadomień oraz
  właścicielskie oznaczanie jako przeczytane i usuwanie pojedynczego, zaznaczonych
  lub wszystkich powiadomień
- `EventReminderController` — idempotentne ustawienie i anulowanie przypomnienia
  dla wydarzenia, zawsze w zakresie użytkownika z sesji

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

`/moje-konto*` i `/panel/moje-konto*` korzystają z `auth` i `active`, nie przyjmują
identyfikatora użytkownika i zawsze działają na koncie z sesji. Wariant panelowy
renderuje ten sam widok konta w `layouts.admin`, a formularze wysyła do nazwanych
tras `admin.account.*`, dzięki czemu po operacji użytkownik pozostaje w panelu.
Osobne Form Requesty ograniczają edycję profilu, e-maila i hasła. Zwykła
aktualizacja profilu nie przyjmuje roli, aktywności, funkcji klubowych ani danych
weryfikacyjnych. Administracyjne dane członkowskie chronią jednocześnie middleware
roli administratora i `MemberProfilePolicy`; moderator nie ma dostępu do profili
innych osób. Policy zezwala każdemu aktywnemu użytkownikowi na podgląd własnego
profilu, ale jego edycja pozostaje wyłącznie administracyjna.

## Enumy

Projekt używa m.in.:
- `AccountRequestStatus`
- `UserRole`
- `EventType`
- `Discipline`
- `CompetitionSystem`
- `MemberAgeCategory`
- `IpscDivision`

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

Odrzucone wnioski podlegają konfigurowalnej retencji liczonej od `reviewed_at`.
Komenda `account-requests:apply-retention` domyślnie anonimizuje dane osobowe po
`ACCOUNT_REQUEST_RETENTION_MONTHS`, zachowując status, datę decyzji i administratora
do minimalnego audytu. Opcjonalna akcja `delete` usuwa kwalifikujący rekord. Znacznik
`anonymized_at`, ponowna selekcja pod blokadą i ograniczenie do statusu `rejected`
zapewniają idempotencję; `pending` i `approved` nie są przetwarzane.

## Powiadomienia

Kanał bazodanowy przechowuje wyłącznie krótki tytuł, komunikat, wewnętrzny URL i
techniczny identyfikator rekordu. Publiczny header i topbar panelu pobierają tylko
licznik nieprzeczytanych. Pełna lista działa pod `/powiadomienia`, używa paginacji
i jest chroniona przez `auth` oraz `active`. Operacje pojedyncze rozpoczynają
zapytanie od relacji użytkownika, dlatego sam UUID cudzego powiadomienia nie daje
dostępu. Usuwanie zaznaczonych waliduje tablice UUID przez
`DeleteSelectedNotificationsRequest`, wybiera rekordy wyłącznie przez
`user->notifications()` i działa w transakcji. Identyfikator cudzego wpisu w
tablicy jest neutralnie ignorowany i nigdy nie rozszerza scopu. Usunięcie wszystkich
również działa przez relację bieżącego użytkownika i w transakcji. Paginacja wraca
na bieżącą lub ostatnią istniejącą stronę po usunięciu rekordów.

`notifications:prune` usuwa globalnie tylko identyfikatory wpisów z
`created_at < now() - NOTIFICATION_RETENTION_DAYS`, partiami po 500, bez odczytu
i logowania prywatnego pola `data`. Indeks `notifications.created_at` wspiera
selekcję retencyjną. `0` oznacza bezpieczne wyłączenie procesu; wartość ujemna lub
niecałkowita zwraca kod błędu bez modyfikacji danych. Komenda jest idempotentna i
zaplanowana na 02:45 `Europe/Warsaw` z blokadą `withoutOverlapping()`.

### E-mailowe przypomnienia o wydarzeniach

`EventReminderSubscription` łączy `User` i `SportEvent`, przechowuje czas zapisu
oraz `reminder_sent_at` i ma unikalny indeks `(user_id, sport_event_id)`. Klucze
obce używają `cascadeOnDelete`, ponieważ subskrypcja nie ma samodzielnej wartości
audytowej po trwałym usunięciu konta lub wydarzenia. Soft delete nadal zachowuje
rekord, ale wszystkie zapytania wysyłkowe wymagają istniejących, nieusuniętych
relacji. Relacje nie są wykorzystywane do publicznego renderowania listy osób.

`EventReminderService` jest wspólnym miejscem transakcyjnego włączenia zgody,
utworzenia subskrypcji i anulowania. Zapis najpierw sprawdza warunki domenowe,
a następnie blokuje użytkownika i wydarzenie oraz powtarza kontrolę w transakcji.
Warunki to: aktywne konto, publiczne i opublikowane wydarzenie, włączone
`email_reminders_enabled` oraz `start_at > now() + 24 godziny`. Unikalny indeks i
stała kolejność blokad domykają idempotencję przy równoległych żądaniach.

Włączenie zgody w „Moim koncie” wymaga `current_password:web` i zapisuje
`event_email_notifications_confirmed_at`. Wyłączenie zeruje potwierdzenie i usuwa
wszystkie subskrypcje. Tę samą strategię stosuje wyłączenie przypomnień przez
administratora wydarzenia. Usunięcie rekordu zatrzymuje też Notification, ponieważ
przed wysyłką ponownie sprawdza ona istnienie właścicielskiej subskrypcji, aktywność
użytkownika, zgodę i bieżącą publiczność wydarzenia.

`events:send-reminders` działa w oknie od 23 h 45 min do 24 h 15 min przed
`start_at`. Wybiera tylko rekordy z pustym `reminder_sent_at`, ponownie blokuje i
weryfikuje użytkownika, wydarzenie i subskrypcję, a następnie ustawia znacznik
przed dispatch. `EventReminderNotification::beforeCommit()` świadomie nadpisuje
globalne `after_commit=true`: przy kolejce `database` znacznik i szyfrowany rekord
zadania `jobs` powstają w tej samej transakcji. Rollback usuwa oba, równoległy
proces widzi znacznik, a awaria workera pozostawia zadanie do standardowego retry.
Operator po wyczerpaniu prób ponawia istniejący wpis przez `queue:retry`, zamiast
uruchamiać drugi dispatch z komendy.

Notification implementuje `ShouldQueue` i `ShouldBeEncrypted`, korzysta z kanału
`mail` oraz istniejącej konfiguracji Resend. Zawiera wyłącznie publiczne dane
wydarzenia i wyjaśnienie źródła wiadomości. Jak w każdym transporcie e-mail typu
at-least-once, awaria dokładnie po zaakceptowaniu wiadomości przez dostawcę, lecz
przed potwierdzeniem zadania przez worker, pozostaje granicznym ryzykiem duplikatu
po retry; aplikacja gwarantuje pojedynczy rekord zadania, nie transakcję rozproszoną
z zewnętrznym dostawcą.

## Bezpieczeństwo i logi

`AddSecurityHeaders` ustawia CSP z nonce, HSTS w produkcji, `nosniff`, politykę
referrera i uprawnień oraz ochronę izolacji. Prywatne formularze, konto, panel i
powiadomienia otrzymują `no-store` i `noindex`. CSP dopuszcza aktualnie własne
zasoby/storage, Google Fonts, osadzone Google Maps, obrazy Unsplash oraz dokładny
origin aktywnego Vite w trybie lokalnym.

`RedactSensitiveLogContext` jest podpięty do kanałów Monolog i rekursywnie maskuje
znane klucze danych osobowych, haseł oraz tokenów. Kontrolery raportują klasę
wyjątku i techniczny identyfikator, nie pełne żądanie. Zaufane proxy są wyłączone
bez jawnego `TRUSTED_PROXIES`; ma to znaczenie dla poprawnego HTTPS, IP i limiterów.

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

Publiczna trasa `account.show` zachowuje `layouts.app`, natomiast
`admin.account.show` pozostaje w `layouts.admin` z sidebarem i topbarem. Wspólny
route-aware widok wybiera layout i nazwy endpointów formularzy bez powielania
operacji biznesowych. Panelowy dropdown konta prowadzi do tego wariantu, centrum
powiadomień, strony głównej w tej samej karcie oraz wylogowania POST z CSRF.

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

Pola `event_notifications_current_password` i
`event_reminder_current_password` są dodane do globalnej listy `dontFlash`, więc
nie trafiają do `_old_input` sesji. Procesor logów nadal maskuje wszystkie klucze
zawierające `password`.

## Kalendarz

`CalendarController@index`:
- waliduje miesiąc i rok,
- tworzy datę wyświetlanego miesiąca,
- pobiera wyłącznie wydarzenia ze scope `publiclyVisible()`,
- zachowuje filtry,
- wyznacza zakres tygodni od poniedziałku do niedzieli,
- mapuje jeden rekord wydarzenia wielodniowego na wszystkie widoczne dni bez
  duplikowania danych w bazie,
- przekazuje dni i nawigację do widoku.

Pełny widok `GET /kalendarz/{slug}` oraz fragment HTML modala
`GET /kalendarz/{slug}/podglad` korzystają z jednej prywatnej metody kontrolera.
Zapytanie zawsze zaczyna się od `SportEvent::publiclyVisible()`, eager-loaduje
powiązane definicje konkurencji i zwraca 404 dla szkicu, wydarzenia niepublicznego,
usuniętego lub nieistniejącego. Fragment zawiera wyłącznie dane przeznaczone do
publikacji i nie serializuje modelu ani pól audytowych.

`calendar.partials.event-details` jest wspólnym źródłem HTML opisu, terminów,
miejsca, adresu, dyscypliny, systemu, konkurencji, rejestracji i miejsca na przyszłe
przypomnienie. Używają go pełny widok oraz odpowiedź modala. Link wydarzenia w
miesięcznej siatce zachowuje zwykły `href` do pełnego widoku, a osobny atrybut
wskazuje endpoint fragmentu. Moduł `event-dialog.js` jest importowany dynamicznie
wyłącznie przy obecności dialogu, przechwytuje tylko zwykłą aktywację linku i
obsługuje loading, błąd, retry, anulowanie poprzedniego requestu oraz odrzucenie
nieaktualnej odpowiedzi. Bez JavaScriptu i przy zmodyfikowanym kliknięciu pozostaje
standardowa nawigacja HTTP.

Filtry enumów powinny być walidowane przez `Rule::enum(...)` lub `Rule::in(...)`, nie jako dowolny string.

## Wyniki zawodów

`ResultsController@show` waliduje GET `q` i filtruje relację
`eventCompetitions.results` po snapshotcie `participant_name` oraz opcjonalnie po
nazwie powiązanego konta. Obie gałęzie używają PostgreSQL `ILIKE`. To samo
ograniczenie jest stosowane do eager loadingu konkurencji, dlatego grupowanie
pozostaje relacyjne, konkurencje bez dopasowania nie są ładowane, a licznik wynika
z już pobranych kolekcji bez dodatkowych zapytań.

Administracyjna lista używa `EventResultFilterRequest`, eager-loaduje wydarzenie
i definicję konkurencji oraz filtruje po `sport_event_id`, konkretnym rekordzie
`event_competitions`, `participant_name`/`users.name` i `ResultStatus`. Paginacja
zachowuje query string. Dropdown wszystkich użytkowników nie jest renderowany.

`GET /panel/wyniki/zawodnicy` (`admin.results.participants`) działa wyłącznie dla
aktywnego moderatora lub administratora i ma limiter `result-participants` 30/min
na konto. `EventResultParticipantSearchRequest` wymaga 2–100 znaków. Zapytanie
wybiera tylko aktywne, nieusunięte konta, eager-loaduje minimalny fragment
`MemberProfile`, ogranicza odpowiedź do 10 pozycji i nie zwraca e-maila, telefonu,
adresu ani dokumentów członkowskich.

`EventResultController` jest źródłem snapshotu powiązanego konta. Przy pierwszym
wyborze zapisuje bieżące `users.name`, `club.short_name` i
`member_profiles.age_category`; przy późniejszej edycji tego samego powiązania
zachowuje istniejący snapshot. Dla zawodnika zewnętrznego pola pozostają ręczne.

Nowe docelowe `EventCompetition` musi należeć do nieusuniętych zawodów typu
`competition` ze statusem `published`. Walidacja Form Requestu jest powtarzana
po `lockForUpdate`, aby zmiana statusu między żądaniem i zapisem nie ominęła
reguły. `EventResultPolicy` blokuje update i delete wyniku archiwalnego, a kontroler
ponawia kontrolę statusu pod blokadą transakcyjną. Edycja archiwalna renderuje
osobny widok tylko do odczytu; status `published` ponownie zezwala na mutacje.

`MemberAgeCategory` jest castem nullable wyłącznie w `MemberProfile`. Wynik
przechowuje surowy string snapshotu, a `categoryLabel()` rozpoznaje wartości enumu
i zachowuje fallback historyczny. Analogicznie `IpscDivision` kontroluje nowe
wybory, zaś `classificationLabel()` i Form Request bezpiecznie obsługują starsze,
nieznane stringi bez castu, który mógłby rzucić wyjątek przy odczycie.

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

Formularz właściciela rozróżnia `intent=draft` i `intent=pending` wartością
klikniętego submittera. Wspólny skrypt stanu wysyłania zachowuje nazwę oraz wartość
submittera w ukrytym polu, zanim wyłączy przyciski, dlatego blokada podwójnego
submitu nie może usunąć intencji z payloadu multipart. `SaleListingPersistence`
zawsze tworzy rekord jako `draft`, zapisuje pliki i historię utworzenia w jednej
transakcji, a następnie dla `pending` wywołuje istniejący `SaleListingWorkflow`.
Workflow ustawia `submitted_at`, czyści dane odrzucenia i zapisuje przejście
`draft|rejected -> pending`.

Właściciel edytuje tylko `draft`, `rejected` i `approved`, usuwa każdy własny
status poza `pending`, wysyła `draft` lub `rejected` i oznacza jako sprzedane tylko
`approved`. Edycja zatwierdzonej oferty najpierw wycofuje publikację, a potem
automatycznie kieruje rekord ponownie do moderacji niezależnie od intencji
formularza. `pending`, `sold`, `expired` i `archived` nie są edytowalne przez
właściciela. Kopia dostępnego własnego rekordu zawsze powstaje jako nowy `draft`;
nie dziedziczy dat ani wyniku moderacji.

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
