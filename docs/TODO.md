# TODO.md

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
