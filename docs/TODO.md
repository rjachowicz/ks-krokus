# TODO.md

## P0 — etap 7/9: ogłoszenia, moderacja i układ widoków

- [x] Zachować `intent` aktywnego submittera podczas ustawiania stanu wysyłania,
  aby `pending` docierało także w formularzu multipart z wieloma zdjęciami.
- [x] Potwierdzić transakcyjne przejścia `draft|rejected -> pending`, ustawienie
  `submitted_at`, wpis historii, redirect i polski komunikat sukcesu.
- [x] Zweryfikować macierz właścicielskich operacji dla `draft`, `pending`,
  `rejected`, `approved`, `sold`, `expired` i `archived` oraz automatyczną ponowną
  moderację zatwierdzonego ogłoszenia po edycji.
- [x] Usunąć menu „Więcej działań”, duplikaty i osobną akcję wysyłania z listy;
  pokazywać bezpośrednio wyłącznie Edytuj, Sprzedane, Podgląd, Kopiuj i Usuń.
- [x] Otwierać podgląd publiczny w tej samej karcie oraz zachować hierarchię
  primary, secondary i destructive w responsywnym układzie akcji.
- [x] Przenieść kartę zgłoszenia do głównego stosu szczegółów i ujednolicić jej
  szerokość, geometrię oraz odstępy z pozostałymi kartami.
- [x] Usunąć przycinanie sticky aside i zabezpieczyć długie telefony, adresy e-mail
  oraz tekst „Bezpiecznej transakcji” przez elastyczne szerokości i łamanie treści.
- [x] Pokryć regresjami intencje formularza, historię, statusy, blokady endpointów,
  akcje listy, geometrię szczegółów i działanie zgłoszenia naruszenia.
- [ ] Powtórzyć wizualny audyt szczegółów i listy przy 320–1920 px w light/dark,
  gdy sesja udostępni instancję Browser.

## P0 — etap 6/9: publiczne i administracyjne wyniki zawodów

- [x] Dodać serwerowe wyszukiwanie GET `q` po zawodniku z PostgreSQL `ILIKE`,
  licznikiem, czyszczeniem, zachowaniem grupowania i ukrywaniem pustych konkurencji.
- [x] Dodać nullable kategorię wiekową do `MemberProfile`, centralny enum, migrację,
  casty, factory, walidację, formularz administratora, konto i testy.
- [x] Zastąpić dowolną klasyfikację kontrolowanym enumem dywizji IPSC i zachować
  bezpieczny fallback dla starszych stringów.
- [x] Dodać panelowe filtry wydarzenia, rzeczywistego `event_competitions`, tekstu
  zawodnika i statusu bez dropdownu wszystkich użytkowników oraz bez N+1.
- [x] Dodać chroniony, limitowany autocomplete aktywnych użytkowników z minimalną
  odpowiedzią, dostępnym comboboxem i bez prywatnych danych w HTML lub JSON.
- [x] Zapisywać snapshot imienia, centralnej nazwy klubu i kategorii, zachowując
  ręczne dane zawodnika zewnętrznego i niezmienność historii po edycji profilu.
- [x] Ograniczyć nowe wyniki do opublikowanych zawodów oraz zablokować Policy i
  transakcją update, przenoszenie i delete archiwum; dodać widok tylko do odczytu.
- [x] Pokryć moduł testami publicznymi, administracyjnymi, prywatności, snapshotów,
  klasyfikacji, historii, statusów, archiwum, filtrów i zapytań.
- [ ] Po wdrożeniu wykonać fizyczny test autocomplete z NVDA/VoiceOver, klawiaturą
  i rzeczywistym zbiorem użytkowników oraz sprawdzić layout od 320 px.

## P0 — etap 5/9: e-mailowe przypomnienia o wydarzeniach

- [x] Dodać domyślnie wyłączoną zgodę użytkownika, czas potwierdzenia, casty,
  fillable i fabrykę oraz osobny formularz wymagający hasła przy włączaniu.
- [x] Przy wyłączeniu zgody zerować potwierdzenie i usuwać wszystkie subskrypcje,
  aby natychmiast zatrzymać oczekujące oraz zakolejkowane przypomnienia.
- [x] Dodać `email_reminders_enabled` do wydarzenia, formularza administratora,
  walidacji, modelu i testów; wyłączenie usuwa subskrypcje wydarzenia.
- [x] Dodać `event_reminder_subscriptions`, relacje, fabryki, klucze obce,
  unikalność użytkownik + wydarzenie oraz brak publicznej listy osób.
- [x] Dodać właścicielskie, idempotentne endpointy subscribe/unsubscribe z
  `auth`, `active`, limiterem, ochroną IDOR i polskimi komunikatami.
- [x] W modalu i pełnym widoku dodać dostępny dialog aktualnego hasła, jawną
  informację o potwierdzeniu i przyciski ustawienia oraz anulowania.
- [x] Egzekwować ścisłe `start_at > now() + 24h` w UI, serwisie i ponownie pod
  blokadą transakcyjną; odrzucać draft, archived, niepubliczne i wyłączone.
- [x] Dodać szyfrowaną, kolejkowaną Notification z nazwą, terminem, miejscem,
  linkiem i wyjaśnieniem źródła wiadomości oraz ponowną kontrolą stanu.
- [x] Dodać idempotentne `events:send-reminders`, atomowy znacznik + rekord
  kolejki, okno ±15 minut i scheduler co 5 minut w `Europe/Warsaw` z blokadą.
- [x] Pokryć zgodę, hasło, atomowość, termin, widoczność, duplikaty, anulowanie,
  IDOR, pomijanie, pojedynczy dispatch, kolejkę i scheduler regresjami.
- [ ] Po wdrożeniu wykonać kontrolowany smoke test Resend, workera i crona oraz
  fizyczny test dialogu z NVDA/VoiceOver i na urządzeniu 320 px.

## P0 — etap 4/9: modal wydarzenia w kalendarzu

- [x] Zachować pełny `href` wydarzenia i bezskryptowy fallback do istniejącego
  widoku szczegółów, przechwytując wyłącznie zwykłą aktywację z JavaScriptem.
- [x] Dodać publiczny endpoint fragmentu HTML oparty na `publiclyVisible()`, z 404
  dla szkicu, wydarzenia niepublicznego i brakującego rekordu.
- [x] Wydzielić wspólny partial szczegółów dla pełnej strony i odpowiedzi modala,
  bez serializowania pól administracyjnych.
- [x] Dodać natywny `<dialog>` z nazwą i opisem ARIA, Enter/Space, Escape,
  przyciskiem zamknięcia, izolacją i przywracaniem fokusu.
- [x] Dodać ładowanie, błąd, retry, anulowanie poprzedniego requestu, ochronę przed
  wielokrotnym otwarciem oraz ignorowanie nieaktualnej odpowiedzi.
- [x] Ładować moduł JS dynamicznie tylko na miesięcznym kalendarzu i zapewnić
  responsywność 320 px oraz `prefers-reduced-motion`.
- [x] Pokryć pełny widok, endpoint, widoczność publiczną, podstawowe dane fragmentu,
  fallback link i filtry kalendarza testami regresyjnymi.
- [ ] Powtórzyć fizyczną kontrolę fokusu, Escape, klawiatury i layoutu 320–1920 px,
  gdy instancja Browser będzie dostępna.

## P0 — etap 3/9: usuwanie i retencja powiadomień

- [x] Dodać dostępne checkboxy przy wpisach, „Zaznacz wszystkie widoczne” oraz
  działający bez JavaScriptu backendowy fallback wyboru bieżącej strony.
- [x] Dodać potwierdzane usuwanie pojedynczego, zaznaczonych i wszystkich
  powiadomień z czytelnymi komunikatami, pustym stanem i zachowaniem paginacji.
- [x] Ograniczyć każde usuwanie do relacji zalogowanego użytkownika, walidować UUID,
  ignorować cudze identyfikatory i wykonywać operacje grupowe w transakcjach.
- [x] Dodać `NOTIFICATION_RETENTION_DAYS=7`, indeks czasu utworzenia oraz
  idempotentne, partiami wykonywane `notifications:prune`.
- [x] Udokumentować zachowanie wartości `0` i błędnej konfiguracji oraz zaplanować
  komendę na 02:45 `Europe/Warsaw` z `withoutOverlapping()`.
- [x] Pokryć endpointy, brak logowania, własność danych, walidację, retencję,
  idempotencję i scheduler testami regresyjnymi.

## P0 — etap 2/9: header, ikony i konto w panelu

- [x] Wzmocnić półprzezroczyste tło i blur publicznego headera, dodać
  `isolation`, prefiks WebKit, obramowanie po scrollu i fallback `@supports not`.
- [x] Wydzielić wspólny komponent SVG oraz usunąć duplikaty ikon i znaki
  tekstowe `●`/`◐` z publicznego headera i panelu.
- [x] Dodać `/panel/moje-konto*` z `admin.account.*`, wspólnym kontrolerem,
  Form Requestami i logiką aktualizacji bez duplikowania implementacji.
- [x] Zastąpić rozproszone akcje topbara dostępnym, responsywnym dropdownem
  konta z tożsamością, rolą, kontem, powiadomieniami, stroną i wylogowaniem.
- [x] Otwierać stronę publiczną w tej samej karcie, bez `target`, `rel` i
  komunikatu o nowej karcie.
- [x] Pokryć trasy, autoryzację, layout, menu, ikony, link oraz zachowanie
  istniejących aktualizacji konta testami regresyjnymi.
- [ ] Powtórzyć rzeczywisty `getComputedStyle`, wizualny blur i interakcje
  dropdownu przy 320–1920 px w light/dark, gdy instancja Browser będzie dostępna.

## P0 — etap 1/9: stopka, logowanie i geometria filtrów

- [x] Uprościć warunki `@guest`/`@auth` w stopce i zapewnić jeden logiczny rząd
  linków na typowym desktopie bez wiązania szerokości z liczbą odnośników.
- [x] Dodać na stronie logowania widoczną, klawiaturową akcję powrotu do strony
  głównej niezależną od linku w logo.
- [x] Zastąpić ręczne przesunięcia przycisków wspólnym układem filtrów dla
  części publicznej, panelu, ogłoszeń i sekcji „Moje ogłoszenia”.
- [x] Dodać regresje struktury stopki, logowania i wspólnego systemu filtrów.
- [ ] Powtórzyć wizualny audyt 320–1920 px po udostępnieniu instancji
  przeglądarki; bieżąca sesja nie udostępniła żadnego backendu Browser.

## P0 — końcowa stabilizacja UI, powiadomień, retencji i bezpieczeństwa

- [x] Potwierdzić wspólne prymitywy geometrii, brak ujemnych marginesów oraz
  spójne kontrolki 48 px, radio/checkbox 20 px i stany dostępności.
- [x] Dodać centrum powiadomień z licznikiem, ostatnimi wpisami, paginacją,
  oznaczeniem pojedynczego i wszystkich oraz autoryzacją właściciela.
- [x] Dodać konfigurowalną, idempotentną retencję odrzuconych wniosków z domyślną
  anonimizacją, statystykami, harmonogramem i testami.
- [x] Uzupełnić prywatne `no-store`/`noindex`, nazwane rate limitery, jawne trusted
  proxies, ustawienia sesji/asset URL i test HSTS/CSP.
- [x] Dodać rekurencyjne maskowanie kontekstu logów i opisać kanały oraz rotację
  na Railway i VPS.
- [ ] Uzyskać formalne zatwierdzenie klubu dla okresu
  `ACCOUNT_REQUEST_RETENTION_MONTHS` i akcji retencji przed produkcyjnym cronem.
- [ ] Po wdrożeniu wykonać fizyczny audyt NVDA/VoiceOver, test urządzeń i kontrolę
  końcowych nagłówków/cookies/CSP za reverse proxy.

## P0 — gotowość produkcyjna poczty, kolejki i storage

- [x] Ujednolicić `.env.example` i konfigurację SMTP z poprawnym nadawcą,
  `Reply-To`, polskimi treściami i absolutnymi linkami opartymi o `APP_URL`.
- [x] Dodać neutralne odzyskiwanie hasła dla aktywnych kont przez standardowy
  broker Laravel, token 60 minut, throttling i pełne polskie komunikaty.
- [x] Dodać administratorowi ponowne wysłanie linku ustawienia hasła dla
  zatwierdzonego wniosku i aktywnego użytkownika wraz z audytem nadawcy i czasu.
- [x] Ustawić produkcyjną kolejkę `database`, worker z trzema próbami,
  `timeout=90`, `retry_after=120` oraz kolejkę dla SMTP i Notifications.
- [x] Potwierdzić idempotencję `listings:expire`, pojedyncze przypomnienie,
  pojedyncze wygaszenie i harmonogram `Europe/Warsaw`.
- [x] Potwierdzić jeden `MEDIA_DISK`, formaty JPG/PNG/WebP, limity 6 MB,
  maksymalnie 10 zdjęć ogłoszenia, placeholdery i zachowanie plików po soft delete.
- [x] Ujednolicić `AdminUserSeeder` z `ADMIN_USER_*`, `Hash::make`,
  `updateOrCreate`, aktywną rolą admin i zachowaniem idempotentnym.
- [x] Udokumentować worker Railway/VPS, restart, błędy i monitoring kolejki,
  cron, Volume, seedery oraz pierwsze uruchomienie produkcji.
- [ ] Skonfigurować produkcyjne SMTP i wykonać smoke test dostarczenia wszystkich
  typów wiadomości bez ujawniania tokenów w logach.
- [ ] Uruchomić osobną usługę workera na Railway i podłączyć alert zaległych oraz
  nieudanych zadań.

## P0 — wnioski o konto członków

- [x] Dodać publiczne `GET/POST /wniosek-o-konto` bez pola hasła i bez
  automatycznej rejestracji.
- [x] Dodać model, enum statusów, migrację, indeksy, relacje audytowe i fabrykę
  `AccountRequest`.
- [x] Dodać CSRF, throttling, honeypot, normalizację i neutralną deduplikację
  e-maila oraz numeru licencji.
- [x] Powiadamiać aktywnych administratorów bez przesyłania pełnych danych we
  wiadomości.
- [x] Dodać wyłącznie administracyjną listę, licznik oczekujących, filtry,
  szczegóły, notatki wewnętrzne, odrzucenie i audyt decyzji.
- [x] Dodać transakcyjne i idempotentne zatwierdzenie tworzące aktywne konto
  `user` oraz bezpieczny token ustawienia hasła Laravel.
- [x] Dodać polski ekran ustawienia hasła oraz neutralne powiadomienie o odrzuceniu
  bez powodu i notatki wewnętrznej.
- [x] Pokryć formularz, uprawnienia, widoki, duplikaty, rate limiter, zatwierdzenie,
  reset hasła, idempotencję i rollback testami regresyjnymi.
- [ ] Po wdrożeniu wykonać smoke test dostarczenia wiadomości przez rzeczywiste
  SMTP i potwierdzić działanie linku na publicznym adresie HTTPS.

## P0 — moje konto i minimalne dane członkowskie

- [x] Dodać `/moje-konto` dla aktywnego zalogowanego użytkownika bez identyfikatora
  innego konta i bez kierowania zwykłego użytkownika do panelu administratora.
- [x] Rozdzielić edycję imienia, telefonu i zgód kontaktowych od zmiany e-maila i hasła.
- [x] Wymagać aktualnego hasła przy zmianie e-maila i hasła, zachować wspólną politykę
  hasła, neutralny konflikt e-maila oraz rotację trwałego logowania.
- [x] Dodać minimalny `MemberProfile` 1:1, enum weryfikacji, casty, relacje, fabrykę,
  unikalność i audyt administratora.
- [x] Kopiować i oznaczać jako zweryfikowane dane z zatwierdzonego wniosku w tej samej
  transakcji oraz uzupełnić istniejące zatwierdzone wnioski migracją.
- [x] Dodać administracyjną edycję danych członkowskich z `MemberProfilePolicy` i
  odmową dostępu moderatorowi; notatki pozostawić w źródłowym wniosku.
- [x] Pokryć podgląd, dozwolone i zabronione aktualizacje, e-mail, hasło, workflow,
  idempotencję, factory, role i administracyjną weryfikację testami regresyjnymi.
- [ ] Przed wykorzystaniem `email_verified_at` zaprojektować kompletną weryfikację
  nowego adresu (`MustVerifyEmail`, powiadomienie i trasy); obecnie aplikacja jej nie używa.
- [ ] Rozważyć bezpieczne wylogowanie pozostałych aktywnych sesji po zmianie hasła.
- [ ] Pełną kartotekę członków i rozliczenia składek zaprojektować jako osobny etap.

## P0 — ogłoszenia sprzedaży

- [x] Dodać modele ofert, zdjęć, historii moderacji i zgłoszeń wraz z indeksami,
  enumami, fabrykami i soft delete.
- [x] Dodać kontrolowany workflow statusów oraz Policy dla właściciela,
  moderatora i administratora.
- [x] Dodać publiczną listę i szczegóły z wyszukiwaniem, filtrami, sortowaniem i
  ochroną danych kontaktowych.
- [x] Dodać strefę „Moje ogłoszenia”, kopiowanie, ponowną moderację po edycji i
  oznaczanie sprzedaży.
- [x] Dodać kolejkę moderacji, historię operacji, odrzucenie z powodem, ukrywanie,
  archiwizację, kosz i przywracanie.
- [x] Dodać galerię 1–10 zdjęć, drag and drop, podgląd, kolejność, zdjęcie główne,
  miniatury i bezpieczne sprzątanie plików.
- [x] Dodać zgłoszenia publiczne z CSRF, throttlingiem, honeypotem i deduplikacją.
- [x] Dodać bazodanowe powiadomienia wykonywane po odpowiedzi HTTP.
- [x] Dodać idempotentne wygasanie, przypomnienia oraz harmonogram aplikacji.
- [x] Dodać testy Feature i Unit modułu.
- [x] Przebudować publiczną listę i szczegóły, formularz właściciela, „Moje
  ogłoszenia” oraz moderację w spójny, responsywny i dostępny system UI bez zmiany
  workflow, tras i zasad prywatności.
- [ ] Skonfigurować osobną usługę Cron na Railway i wykonać produkcyjny smoke test.

## P0 — baza danych

- [x] Przełączyć środowisko lokalne z SQLite na PostgreSQL.
- [x] Skonfigurować osobną bazę PostgreSQL dla testów.
- [x] Uruchomić migracje i ograniczyć runtime wyłącznie do PostgreSQL.
- [x] Dodać indeks zakresowy dla wydarzeń wielodniowych.
- [x] Dodać indeks dashboardu wyników po użytkowniku, statusie usunięcia i dacie.
- [x] Odtworzyć lokalne dane startowe bez wpisów wyników.

## P0 — uploady i wdrożenie

- [x] Usunąć przyczynę `validation.uploaded`.
- [x] Uzgodnić limity Laravela i PHP.
- [x] Zweryfikować `multipart/form-data`, nazwy pól i dysk publiczny.
- [x] Przetestować zapis, podmianę, usuwanie i rollback plików.
- [x] Zachować poprzednie zdjęcie przy błędzie uploadu.
- [x] Dodać polskie komunikaty i testy regresyjne.
- [x] Udokumentować wdrożenie Railway.
- [x] Dodać główny `php.ini` dla Railway Railpack/FrankenPHP, niezależny od
  `public/.user.ini`.
- [x] Powtórzyć rzeczywisty lokalny upload HTTP pliku większego niż 2 MB.
- [x] Zweryfikować lokalnie produkcyjny cache, migracje, logi, zapis do storage,
  link publiczny, manifest Vite i wymagania platformy PHP.
- [ ] Podpiąć na Railway wolumen do `/app/storage/app/public`.
- [ ] Ustawić na Railway `RAILPACK_SKIP_MIGRATIONS=true` przy własnym
  pre-deploy command.
- [ ] Wykonać produkcyjny smoke test zdjęcia 1 MB, 3 MB, 6 MB i ponad 6 MB.

## P1 — formularze i panel

- [x] Przeprowadzić audyt wszystkich zasobów panelu.
- [x] Ujednolicić błędy, checkboxy, selecty, daty i przyciski.
- [x] Uzupełnić `old()`, `attributes()`, `messages()` i autocomplete.
- [x] Dodać serwerowe ARIA i podsumowanie błędów bez zależności od JavaScriptu.
- [x] Dodać wymagania uploadu, podgląd i usuwanie wybranych plików.
- [x] Zweryfikować autoryzację i ochronę ostatniego administratora.
- [x] Naprawić relację wyników z definicjami konkurencji i pokryć publiczne wyniki,
  CRUD panelu oraz dashboard testami regresyjnymi.
- [x] Uzależnić opis trenera od statusu trenera w interfejsie, walidacji i zapisie.
- [x] Naprawić błąd 500 edycji wydarzenia z przypisaną konkurencją.
- [x] Ograniczyć wyniki do istniejących zawodów i zachować historyczne relacje
  usuniętych wydarzeń oraz użytkowników podczas edycji.
- [x] Walidować własność zdjęć galerii i aktywność nowych konkurencji wydarzenia.
- [x] Zabezpieczyć wieloetapowe zapisy CRUD transakcjami i blokadami rekordów.
- [x] Obsłużyć złośliwe tablice w polach skalarnych bez błędu renderowania 500.
- [x] Pokryć pełne CREATE/READ/UPDATE/DELETE sześciu zasobów testami regresyjnymi.
- [x] Dodać dostępne stany wysyłania, blokadę podwójnego submitu i komunikaty
  ładowania wszystkich formularzy.
- [x] Ujednoznacznić potwierdzenia usuwania, nazwy operacji i puste stany zasobów.
- [x] Uwzględnić zapisane zdjęcia w limicie galerii i zsynchronizować wymianę oraz
  usuwanie okładki.
- [x] Ujednolicić natywny wygląd radio buttonów i checkboxów wraz ze stanami hover,
  focus, disabled i error oraz semantycznymi grupami `fieldset`/`legend`.
- [x] Umieścić oznaczenie wymagania w jednym elemencie labela we wszystkich
  formularzach i ujednolicić typografię oraz stany selectów i pól daty/czasu.
- [ ] Na istniejących środowiskach uruchomić jednorazowo
  `ClubDirectorySeeder`, jeśli profile katalogowe utworzono jako nieaktywne.

## P1 — bezpieczeństwo

- [x] Ponownie zweryfikować CSRF, XSS, uploady, autoryzację, middleware,
  Form Requesty, masowe przypisanie, `$fillable` i obsługę wyjątków.
- [x] Dodać wymuszane CSP z nonce, produkcyjne HSTS, nagłówki izolacji oraz
  `no-store`/`noindex` dla formularzy konta i panelu.
- [x] Domyślnie zabezpieczyć ciasteczko sesji na produkcji i wyłączyć ujawnianie
  wersji PHP oraz błędów przez PHP runtime.
- [x] Ograniczyć zewnętrzny adres zapisów wydarzenia do HTTP/HTTPS.
- [x] Sprawdzić zależności Composer/npm — 0 znanych podatności.
- [ ] Po wdrożeniu potwierdzić CSP, HSTS, flagi ciasteczek i usunięcie nagłówka
  `Server` na końcowej odpowiedzi reverse proxy Railway.

## P1 — motyw i kontakt

- [x] Przełącznik light/dark, `prefers-color-scheme` i brak migotania.
- [x] Bezpieczna obsługa niedostępnego `localStorage`.
- [x] POST `/kontakt`, Form Request, Mailable, rate limiting i honeypot.
- [x] Polskie komunikaty i testy `Mail::fake()`.
- [ ] Sprawdzić rzeczywiste SMTP w środowisku produkcyjnym.
- [x] Sprawdzić wszystkie ekrany w obu motywach: automatyczny render 22 ekranów
  i ręczna inspekcja reprezentatywnych zrzutów.

## P2 — kalendarz, UX i dostępność

- [x] Walidować filtry enumami i zakres miesiąca/roku.
- [x] Obsłużyć wydarzenia wielodniowe bez duplikowania danych.
- [x] Uwzględnić trwające wydarzenia wielodniowe w listach nadchodzących i
  przetestować filtry po metadanych wydarzenia oraz podpiętej konkurencji.
- [x] Sprawdzić N+1, mobile, puste stany, sukcesy, błędy i potwierdzenia.
- [x] Zapewnić focus-visible, etykiety, ARIA, skip link i obsługę klawiatury.
- [x] Połączyć błędy filtrów publicznych z kontrolkami i dodać kompletne stany
  puste, loading oraz jednoznaczną nawigację powrotną.
- [x] Naprawić dostępność menu, dialogu potwierdzenia i edytora Tiptap, w tym
  zarządzanie fokusem oraz obsługę toolbara strzałkami.
- [x] Przeprowadzić automatyczny audyt axe-core 22 głównych tras w obu motywach —
  88 przebiegów bez naruszeń WCAG/best-practice.
- [x] Dodać polskie strony błędów HTTP.
- [ ] Przeprowadzić końcowy fizyczny test z NVDA/VoiceOver na środowisku
  wdrożeniowym.

## P2 — responsywność

- [x] Sprawdzić 320, 375, 768, 1024, 1366 i 1920 px w light/dark
  (266 wariantów wraz z otwartymi menu).
- [x] Usunąć poziomy scroll części publicznej i panelu.
- [x] Sprawdzić tabele, formularze, sidebar, topbar, galerie i kalendarz.
- [x] Naprawić jednokolumnową agendę kalendarza, mobilne filtry, puste stany
  i dwukolumnowe metryki dashboardu.
- [x] Zweryfikować wszystkie ekrany ogłoszeń przy 320, 375, 768, 1024, 1366 i
  1920 px w light/dark oraz interakcję galerii — 96 wariantów bez overflow i
  błędów konsoli.
- [x] Ujednolicić geometrię wszystkich widoków neutralnymi prymitywami kontenera,
  sekcji, stosu, klastra, siatki, karty, formularza, sidebara i pustego stanu.
- [x] Przeprowadzić pełny audyt 38 tras przy 320, 375, 480, 768, 1024, 1280,
  1366, 1440 i 1920 px w light/dark oraz usunąć konflikt mobilnej siatki formularza.
- [x] Dodać wspólny placeholder dla obrazów, których plik nie może zostać załadowany.
- [x] Przenieść logowanie, wspólny panel roli i wylogowanie POST+CSRF do głównego
  headera oraz zsynchronizować dostępne menu desktopowe i mobilne.

## P3 — dalszy rozwój

- [x] Usunąć wykryty martwy CSS, przykładowy kod i puste zasoby; ponowny audyt
  potwierdza użycie wszystkich literalnych klas CSS.
- [x] Zweryfikować importy JS, komponenty Blade i zależności.
- [x] Przeprowadzić końcowy audyt zapytań, eager loadingu, operacji galerii,
  renderowania Blade oraz włączyć wykrywanie lazy loadingu poza produkcją.
- [x] Usunąć nieużywane relacje modeli, kod szkieletowy, puste stacki i trasę
  konsolową oraz lokalne pliki `.idea` z repozytorium.
- [x] Rozdzielić obsługę uploadu do dynamicznego pakietu JavaScript i potwierdzić
  użycie wszystkich pozostałych klas CSS, komponentów oraz selektorów JS.
- [x] Uprościć semantykę wszystkich widoków Blade i ujednolicić formularze,
  karty, tabele, kontrolki oraz nagłówki panelu.
- [x] Dodać testy regresyjne i dokumentację wdrożenia.
- [ ] Dodać przywracanie kosza lub cykliczne trwałe czyszczenie zdjęć
  miękko usuniętych aktualności.
- [ ] Dostarczyć brakujący `public/files/Uchwala_kolekcjonerska.pdf`.
- [ ] Rozważyć `pg_trgm` i indeksy wyszukiwania po wzroście liczby rekordów.
- [ ] Dodać automatyczne skalowanie/kompresję zdjęć i generowanie miniatur.
- [ ] Skonfigurować monitoring błędów, alerty, backup PostgreSQL i test odtwarzania.
