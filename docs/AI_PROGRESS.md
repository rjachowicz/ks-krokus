# AI_PROGRESS.md

## Branch bazowy

`main`

## Bieżąca sesja — 2026-08-02 — spójny interfejs ogłoszeń sprzedaży

### Cel

Przebudować wyłącznie warstwę wizualną publicznych ogłoszeń, strefy właściciela
i moderacji tak, aby moduł korzystał ze wspólnego języka KS Krokus, zachowując
workflow, uprawnienia, prywatność kontaktu, uploady, zgłoszenia i istniejące trasy.

### Najważniejsze potwierdzone problemy

- Publiczne filtry pokazywały wszystkie pola i sortowanie w jednym ciężkim
  formularzu, nie prezentowały liczby wyników, a karty powtarzały zbędny status
  „Aktualne” i miały niestabilny footer.
- Brak zdjęcia tworzył pusty link, natomiast galeria szczegółów układała wszystkie
  fotografie jako nierówne kafle z podpisami i otwierała je w nowych kartach.
- Formularz właściciela zależał od klas `admin-*`, siedem sekcji rozdzielały
  głównie linie, a końcowe akcje korygował ujemny margines.
- „Moje ogłoszenia” i kolejka moderacji nie miały miniatur ani wyraźnej hierarchii
  akcji. Formularze decyzji, historia i zgłoszenia były wizualnie zbite.
- Ogólne style uploadu pozostawały w arkuszu panelu, a moduł duplikował własne
  badge, powierzchnie i wzorce formularzy.

### Wykonane

- [x] Publiczna lista używa wspólnego hero i kontenera sekcji. Podstawowe filtry
  obejmują wyszukiwarkę, kategorię i akcję, a rodzaj, kaliber i ceny znajdują się
  w dostępnej sekcji „Więcej filtrów”. Dodano licznik, aktywne filtry, czyszczenie
  oraz osobne sortowanie zachowujące parametry GET.
- [x] Karty mają stałą wysokość, proporcjonalne zdjęcie, neutralny placeholder KS,
  kategorię, tytuł ograniczony do dwóch linii, producenta/model, maksymalnie dwa
  parametry, krótki opis oraz uporządkowany blok ceny, lokalizacji i daty.
- [x] Szczegóły otrzymały główne zdjęcie, przewijany rząd miniatur, dostępny dialog
  lightbox z bezskryptowym fallbackiem, zwartą semantyczną siatkę `dl`, wyraźną
  cenę i kartę kontaktową z akcjami „Zadzwoń” oraz „Napisz wiadomość”. Formularz
  zgłoszenia przeniesiono do osobnej sekcji pod treścią.
- [x] Dodano neutralne `form-layout`, `form-grid`, `form-grid--full`,
  `form-section`, `form-section__header` i `form-actions`. Wszystkie siedem sekcji
  ma numer, nagłówek i opis; checkbox negocjacji jest switchem, a akcje nie używają
  ujemnych marginesów ani nie nakładają się na treść.
- [x] Ogólny dropzone i podglądy plików przeniesiono z `admin.css` do wspólnych
  stylów formularzy. Ujednolicono nowe i zapisane zdjęcia, ograniczono podglądy,
  dodano badge zdjęcia głównego oraz czytelne, opisane akcje kolejności i usuwania.
- [x] „Moje ogłoszenia” korzystają z responsywnych kart z miniaturą, statusem,
  aktualizacją, wygaśnięciem, powodem odrzucenia, następnym krokiem oraz rozdziałem
  akcji głównej, dodatkowych i destrukcyjnej.
- [x] Kolejka, edycja, zgłoszenia i historia moderacji zachowują layout panelu, ale
  mają lżejsze filtry, status tabs, stabilne kolumny, miniatury, grupy akcji,
  osobny formularz odrzucenia, timeline historii i puste stany. Sticky sidebar
  wyłącza się na mniejszych ekranach.
- [x] Dodano wspólny neutralny komponent placeholdera obrazu i neutralne badge
  statusów, usunięto nieużywane warianty klas ogłoszeń oraz pozostawiono w
  `listings.css` wyłącznie selektory specyficzne dla modułu.
- [x] Dodano test regresyjny neutralnej struktury siedmiu sekcji formularza oraz
  dostępnych kontrolek galerii i lightboxa.

### Testy i kontrole

- [x] `composer test` — 89 testów, 713 asercji, wszystkie poprawne.
- [x] `vendor/bin/pint --test` — bez błędów.
- [x] `npm run build` — poprawny build Vite; moduł galerii 5,86 kB.
- [x] `php artisan view:cache` i `git diff --check` — poprawne.
- [x] Edge headless — 8 ekranów modułu, szerokości 320, 375, 768, 1024, 1366
  i 1920 px, oba motywy: 96 wariantów bez poziomego overflow i błędów konsoli.
  Sprawdzono także efektywne cele dotykowe, zmianę miniatur i otwarcie lightboxa.
- [x] Ręcznie oceniono reprezentatywne zrzuty listy, szczegółów, formularza,
  „Moich ogłoszeń” i moderacji w widokach desktopowych i mobilnych light/dark.

### Migracje, konfiguracja i ograniczenia

- Nie dodano migracji, nie zmieniono schematu bazy ani zmiennych środowiskowych.
- Nie zmieniono logiki biznesowej, zapytań publicznej widoczności, workflow ani
  zasad ujawniania telefonu i adresu e-mail.
- Tymczasowe dane, konto, serwer, pakiet audytowy i zrzuty zostały usunięte po
  kontroli; lokalny rekord użytkownika istniejący przed audytem pozostał bez zmian.
- Nadal potrzebny jest fizyczny test NVDA/VoiceOver i kontrola na rzeczywistych
  urządzeniach po wdrożeniu. Obrazy demonstracyjne nie zastępują testu fotografii
  o skrajnych proporcjach i rozmiarach z produkcyjnego storage.

## Bieżąca sesja — 2026-08-02 — moderowane ogłoszenia sprzedaży

### Cel

Dodać kompletną, moderowaną tablicę ogłoszeń członków klubu: część publiczną,
strefę właściciela, kolejkę moderatora i administratora, galerię zdjęć,
powiadomienia, zgłoszenia nadużyć oraz automatyczne wygasanie bez płatności i
pośrednictwa w transakcji.

### Wykonane

- [x] Dodano `SaleListing`, `SaleListingImage`, `SaleListingModeration` i
  `SaleListingReport`, fabryki, relacje, klucze obce, indeksy, unikalny slug,
  pola audytowe i soft delete.
- [x] Dodano enumy statusu, kategorii, rodzaju, stanu, przyczyny zgłoszenia i
  działania moderacyjnego z polskimi etykietami.
- [x] `SaleListingWorkflow` kontroluje wysłanie, zatwierdzenie, odrzucenie,
  sprzedaż, ukrycie, archiwizację, wygaśnięcie i historię operacji. Zwykły
  użytkownik nie może zatwierdzić ogłoszenia ani edytować cudzej oferty, a edycja
  zatwierdzonej oferty ponownie uruchamia moderację.
- [x] Dodano Policy dla publicznego podglądu, właściciela, moderatora i
  administratora oraz serwerową ochronę każdej akcji.
- [x] Publiczne `/ogloszenia` ma wyszukiwanie, filtry kategorii, rodzaju, kalibru
  i ceny, cztery sposoby sortowania, karty ofert oraz szczegóły z galerią,
  parametrami, terminem, disclaimerem i kontaktem respektującym osobne zgody.
- [x] Strefa `/panel/moje-ogloszenia` obsługuje szkice, ponowne wysłanie,
  sprzedaż, kopiowanie, usuwanie i publiczny podgląd. Formularz ma siedem sekcji,
  pełne polskie błędy, `old()`, ARIA i tekstową treść bez dowolnego HTML.
- [x] Kolejka `/panel/ogloszenia` ma widoki statusów, filtry autora, kategorii i
  dat, pełny podgląd zdjęć, odrzucenie z obowiązkowym powodem, ukrywanie,
  sprzedaż, archiwizację, kosz, przywracanie i historię moderacji. Moderator może
  edytować, ukrywać i zgłaszać administratorowi, ale nie zatwierdza ani nie
  odrzuca.
- [x] Galeria przyjmuje 1–10 zdjęć przy wysłaniu do moderacji (szkic może być
  niekompletny), obsługuje drag and drop, podgląd, nazwę, usunięcie, kolejność,
  zdjęcie główne, alt i podpis. Wspólny `MEDIA_DISK` zapisuje oryginał przed
  optymalizacją; GD koryguje EXIF, skaluje bez powiększania i tworzy miniaturę.
- [x] Publiczne zgłoszenia mają CSRF, limit 3/h, honeypot, walidację i deduplikację
  po skrócie zgłaszającego, ogłoszenia oraz przyczynie; trafiają do panelu admina.
- [x] Przywrócono Laravel Notifications i tabelę `notifications`. Administrator
  dostaje informację o kolejce, autor o zatwierdzeniu, odrzuceniu z powodem oraz
  terminie. Kolejka `deferred` wykonuje zapis po odpowiedzi bez workera.
- [x] Idempotentna komenda `listings:expire` wysyła jednokrotne przypomnienie 7
  dni przed końcem i wygasza ofertę po 365 dniach. Harmonogram uruchamia ją o
  01:15 `Europe/Warsaw`; opisano osobną usługę Railway Cron.
- [x] Dodano modułowe style light/dark, breakpointy od 320 px, bezpieczne tabele,
  mobilne filtry, karty, galerię, formularz i kolejkę oraz pozycje nawigacji.

### Testy i kontrole

- [x] `composer test` — 88 testów, 697 asercji, wszystkie poprawne.
- [x] Testy modułu — 19 testów Feature/Unit, 102 asercje: role, workflow,
  publiczność, walidacja, obrazy, prywatność, filtry, zgłoszenia, powiadomienia,
  wygasanie i soft delete.
- [x] `vendor/bin/pint --test` — bez błędów.
- [x] `npm run build` — poprawny build Vite; osobny moduł galerii 4,34 kB.
- [x] `php artisan route:list` — 82 trasy, w tym komplet tras ogłoszeń.
- [x] `php artisan schedule:list` — `listings:expire` codziennie o 01:15.
- [x] `php artisan migrate:fresh --seed --force` wykonano wyłącznie na
  `ks_krokus_testing`; 13 migracji i wszystkie seedery zakończone poprawnie.
- [x] `composer validate --strict`, `php artisan view:cache` i `git diff --check`
  — poprawne.

### Migracje i konfiguracja

- `2026_08_02_000900_create_sale_listings_tables.php` — oferty, zdjęcia, historia
  moderacji i zgłoszenia.
- `2026_08_02_001000_create_notifications_table.php` — powiadomienia bazodanowe.
- Nie dodano nowych wymaganych zmiennych środowiskowych. Zalecane
  `QUEUE_CONNECTION` zmieniono z `sync` na `deferred`; `MEDIA_DISK=public`
  pozostaje wspólne dla wszystkich uploadów.
- `ext-gd` przeniesiono do wymaganych rozszerzeń produkcyjnych Composera.

### Znane ograniczenia i czynności wdrożeniowe

- Railway wymaga osobnej usługi Cron uruchamiającej `php artisan schedule:run`
  co 5 minut oraz istniejącego wolumenu `/app/storage/app/public`.
- Nie wykonano produkcyjnego smoke testu crona, powiadomień, uploadu 10 zdjęć ani
  trwałości miniatur po redeployu.
- Soft delete celowo zachowuje pliki ogłoszenia do przywrócenia. Brak interfejsu
  force delete; politykę trwałego czyszczenia starych rekordów należy ustalić po
  określeniu okresu retencji.
- Automatyczne testy i responsywne breakpointy nie zastępują fizycznego audytu
  nowych ekranów z NVDA/VoiceOver oraz na urządzeniach produkcyjnych.

## Bieżąca sesja — 2026-07-31 — końcowa refaktoryzacja i gotowość produkcyjna

### Cel

Przejrzeć kompletny backend, Blade, CSS i JavaScript, usunąć potwierdzony martwy
kod oraz zbędne operacje, zweryfikować zapytania i kalendarz, a następnie
sprawdzić konfigurację wdrożenia, cache, logi, storage, migracje i build.

### Najważniejsze potwierdzone problemy

- Publiczne strony szczegółów najpierw wykonywały route model binding, a potem
  dodatkowe zapytanie `exists()` sprawdzające widoczność tego samego rekordu.
- Kalendarz, strona główna i wyniki eager-loadowały relacje, których widoki nie
  używały. Edycja wydarzenia wykonywała ponadto dwa zapytania bezpośrednio z
  szablonu Blade.
- Walidacja i aktualizacja galerii wielokrotnie pobierały te same zdjęcia, a ich
  metadane były aktualizowane po dodatkowym zapytaniu dla każdego rekordu.
- Scope nadchodzących wydarzeń pomijał trwające wydarzenie wielodniowe, jeżeli
  zaczęło się przed bieżącym dniem.
- Zapytanie dashboardu użytkownika po `event_results.user_id`, `deleted_at` i
  `created_at` nie miało dopasowanego indeksu PostgreSQL.
- Obsługa uploadu była częścią głównego pakietu JavaScript na każdej stronie,
  mimo że jest potrzebna wyłącznie w wybranych formularzach.
- Repozytorium nadal śledziło lokalne pliki projektu PhpStorm, pusty plik tras
  konsolowych, nieużywane relacje modeli, trait powiadomień, stacki Blade i kod
  fabryki wygenerowany przez szkielet Laravela.

### Wykonane

- [x] Publiczne aktualności, wydarzenia i wyniki są pobierane jednym zapytaniem,
  które równocześnie egzekwuje status publikacji i ładuje tylko używane relacje.
- [x] Usunięto nieużywane eager loadingi z kalendarza, strony głównej i wyników,
  zapytania z Blade oraz relację użytkownika nieużywaną przy renderowaniu wyników.
- [x] Połączono dwa zapytania zliczające aktualności na dashboardzie w jeden
  agregat PostgreSQL.
- [x] Połączono filtry dyscypliny i systemu konkurencji we wspólne scope'y modelu,
  używane przez kalendarz i wyniki. Pokryto zarówno metadane wydarzenia, jak i
  podpięte definicje konkurencji.
- [x] Scope `upcoming()` uwzględnia wydarzenia wielodniowe trwające dzisiaj;
  zachowano zakres miesiąca i mapowanie jednego rekordu na wszystkie widoczne dni
  bez duplikowania danych w bazie.
- [x] Galeria aktualności używa jednej zablokowanej kolekcji do limitu, edycji i
  usuwania, a wybrane rekordy usuwa jednym zapytaniem.
- [x] Dodano indeks `event_results (user_id, deleted_at, created_at)` dla zapytania
  dashboardu i regresję sprawdzającą jego obecność.
- [x] Włączono wykrywanie lazy loadingu poza produkcją, aby kolejne N+1 kończyły
  się błędem podczas testów i pracy lokalnej.
- [x] Moduł uploadu jest ładowany dynamicznie tylko na stronach z odpowiednim
  formularzem. Główny pakiet JS zmalał z około 12,3 kB do 9,4 kB.
- [x] Usunięto nieużywane relacje i metody modeli, trait `Notifiable`, kolejkowe
  traity synchronicznego maila, nieużywaną fabrykę `unverified()`, puste stacki
  Blade, pustą trasę konsolową i zbędne wpisy szkieletu Composer/PHPUnit.
- [x] Usunięto z repozytorium dziewięć plików `.idea` i dodano główny `.gitignore`.
  Ponowny audyt potwierdził użycie wszystkich literalnych klas CSS, komponentów
  Blade, selektorów JavaScript i bezpośrednich zależności npm, więc nie usuwano
  działających zasobów na podstawie niepewnej heurystyki.
- [x] Uporządkowano metadane i wymagane rozszerzenia w Composerze, przeniesiono
  Tinker do zależności deweloperskich i ustawiono synchroniczną kolejkę, ponieważ
  aplikacja nie ma obecnie żadnych zadań asynchronicznych.
- [x] Zaktualizowano instrukcję Railway o `php artisan optimize`, cache startowy,
  wymagania PHP i brak workera kolejki.

### Testy i kontrole

- [x] `composer test` — 69 testów, 588 asercji, wszystkie poprawne.
- [x] `vendor/bin/pint --test` — bez błędów.
- [x] `npm run build` — produkcyjny build Vite zakończony poprawnie; główny JS
  9,37 kB, osobny moduł uploadu 3,14 kB.
- [x] `composer validate --strict`, `composer check-platform-reqs`,
  `composer audit --locked` i `npm audit --audit-level=moderate` — poprawne,
  0 znanych podatności.
- [x] `php artisan optimize` — konfiguracja, zdarzenia, trasy i widoki zapisane w
  cache; po kontroli cache wyczyszczono do lokalnego stanu deweloperskiego.
- [x] Wszystkie 11 migracji ma status `Ran`, nowy indeks potwierdzono także przez
  introspekcję schematu PostgreSQL.
- [x] `public/storage` wskazuje na `storage/app/public`; katalogi `storage` i
  `bootstrap/cache` są zapisywalne, manifest Vite istnieje, a konfiguracja PHP z
  repozytorium ustawia limity uploadu 8 MB / 85 MB / 20 plików.
- [x] Audyt logu nie wykazał nowych błędów po zmianach; 40 wpisów `ERROR` w
  lokalnym logu pochodzi z wcześniejszych, już naprawionych etapów prac.

### Migracje i zmienne środowiskowe

- Dodano migrację
  `2026_07_31_000800_add_user_dashboard_index_to_event_results_table.php`.
- Nie dodano nowych wymaganych zmiennych środowiskowych.
- Domyślne i przykładowe `QUEUE_CONNECTION` zmieniono z `database` na `sync`;
  na istniejącym środowisku należy ustawić tę wartość jawnie albo usunąć stare
  nadpisanie, jeżeli nadal wskazuje na bazę.
- Composer wymaga teraz jawnie `fileinfo`, `PDO` i `pdo_pgsql`; `gd` pozostaje
  wymaganiem deweloperskim potrzebnym do testów obrazów.

### Znane ograniczenia i kontrole wdrożeniowe

- Nie wykonano deployu ani operacji na produkcyjnym Railway. Nadal trzeba podpiąć
  wolumen, ustawić zmienne usługi, uruchomić migrację i wykonać smoke test po HTTPS.
- Rzeczywiste SMTP, trwałość uploadów po redeployu, backup/restore PostgreSQL,
  alerty i zachowanie nagłówków reverse proxy wymagają środowiska produkcyjnego.
- Fizyczny test NVDA/VoiceOver, zewnętrzny test penetracyjny, brakujący dokument
  PDF oraz polityka trwałego czyszczenia miękko usuniętych zdjęć pozostają poza
  zakresem kontroli lokalnej.

## Poprzednia sesja — 2026-07-31 — audyt UX, dostępności i bezpieczeństwa

### Cel

Przejść cały przepływ publiczny, logowanie i panel, zweryfikować zachowanie
formularzy, komunikatów, nawigacji, stanów pustych i operacji destrukcyjnych,
a następnie sprawdzić dostępność, responsywność oraz zabezpieczenia aplikacji i
naprawić wszystkie potwierdzone problemy bez obniżania istniejącej ochrony.

### Najważniejsze potwierdzone problemy

- Jasny motyw używał złotego tekstu i tekstu pomocniczego o zbyt niskim lub
  granicznym kontraście na jasnych powierzchniach. Przygaszenie całych dni spoza
  miesiąca obniżało również kontrast treści kalendarza.
- Publiczne filtry zwracały poprawne polskie błędy, ale nie renderowały ich przy
  odpowiednich kontrolkach ani nie ustawiały `aria-invalid` i `aria-describedby`.
- Mobilne menu publiczne przenosiło użytkownika klawiatury poza właśnie otwartą
  nawigację, a panelowy drawer nie izolował tła i nie zarządzał fokusem.
- Dialog potwierdzenia nie miał dostępnej nazwy i opisu, a komunikaty usuwania nie
  wskazywały jednoznacznie rekordu oraz skutków operacji.
- Edytor treści usuwał widoczny fokus, nie obsługiwał wzorca klawiaturowego
  toolbara i po ukryciu wymaganego `textarea` nie mógł poprawnie obsłużyć natywnej
  walidacji. Domyślne style Tiptap były ponadto blokowane przez ścisłe CSP.
- Formularze nie sygnalizowały wysyłania i pozwalały na ponowne kliknięcie, toast
  znikał także podczas interakcji, a limit galerii w przeglądarce nie uwzględniał
  już zapisanych zdjęć.
- Selektor roku kalendarza nie pokazywał poprawnej wartości spoza okna ±5 lat,
  a wydarzenie wielodniowe powtarzało godzinę rozpoczęcia w każdym dniu.
- Aplikacja miała podstawowe nagłówki HTTP, lecz bez wymuszanego CSP, HSTS,
  ochrony przed cache'owaniem formularzy i panelu oraz bez domyślnego bezpiecznego
  ciasteczka sesji na produkcji.
- Adres zapisów wydarzenia dopuszczał schematy inne niż HTTP/HTTPS.

### Wykonane

- [x] Ujednolicono dostępne tokeny kolorów light/dark, rozdzielono kolor tekstu
  od złotego wypełnienia oraz poprawiono kontrast stanów kalendarza, statusów,
  zaznaczenia, checkboxów i numerów kroków.
- [x] Połączono błędy wszystkich filtrów publicznych z polami, dodano podsumowania
  bez JavaScriptu, limity długości oraz konkretne puste stany dla wyników
  filtrowanych i całkowitego braku danych.
- [x] Dodano stan `aria-busy`, polskie komunikaty ładowania, blokadę podwójnego
  wysłania i spinner respektujący `prefers-reduced-motion`. Toast pozostaje
  widoczny podczas najechania lub fokusu.
- [x] Potwierdzenia usuwania wskazują konkretny rekord i konsekwencje. Natywny
  `dialog` ma `aria-labelledby`/`aria-describedby`, zachowuje submitter i posiada
  bezpieczny fallback `window.confirm`.
- [x] Menu publiczne oraz panelowe zarządzają fokusem, `aria-expanded`, `inert`,
  `aria-hidden`, klawiszem Escape i powrotem fokusu. Dodano w pełni użyteczne
  warianty bez JavaScriptu.
- [x] Edytor Tiptap ma jawną etykietę, rolę textbox, `aria-required`, dostępną
  walidację pustej treści, widoczny fokus także bez `:has`, roving tabindex i
  strzałki/Home/End w toolbarze. Wstrzykiwany styl otrzymuje nonce CSP.
- [x] Tabele mają `scope="col"`, operacje list mają nazwy zawierające rekord, a
  wszystkie linki do nowej karty komunikują ten fakt i używają
  `rel="noopener noreferrer"`.
- [x] Poprawiono zależności pól dat i wyniku, blokadę niedostępnego formularza,
  puste słowniki, konflikt wymiany/usunięcia okładki, łączny limit galerii,
  walidację plików bez MIME oraz zwalnianie URL-i podglądu.
- [x] Kalendarz zachowuje każdy poprawny rok, rozróżnia pojedynczą godzinę,
  zakres jednodniowy oraz początek, kontynuację i koniec wydarzenia wielodniowego.
- [x] Formularz kontaktowy normalizuje dane i odzwierciedla limity backendu w
  HTML; logowanie ogranicza rozmiar e-maila i hasła.
- [x] Dodano wymuszane CSP z nonce i ścisłą listą źródeł, HSTS na produkcji,
  dodatkowe nagłówki izolacji i ochrony, `no-store` dla formularzy i panelu,
  `noindex` dla logowania/panelu oraz produkcyjnie bezpieczne ciasteczko sesji.
  Lokalny Vite otrzymuje tylko dokładny origin zapisany w aktywnym pliku `hot`.
- [x] Utwardzono PHP (`expose_php`, błędy ekranowe, strict session), ograniczono
  adres zapisów do HTTP/HTTPS i zweryfikowano CSRF wszystkich formularzy POST,
  jawne `$fillable`, middleware ról/aktywności, autoryzację Form Requestów,
  sanitację HTML, uploady i relacyjne kontrole własności.
- [x] Dodano regresje nagłówków i nonce CSP, cache/noindex, schematów URL,
  normalizacji kontaktu, dostępności filtrów i tabel, linków zewnętrznych,
  kalendarza wielodniowego, zakresów godzin i selektora roku.

### Testy i kontrole

- [x] `composer test` — 65 testów, 579 asercji.
- [x] `vendor/bin/pint --test` — bez błędów.
- [x] `npm run build` — produkcyjny build Vite zakończony poprawnie.
- [x] `composer validate --strict`, `composer audit --locked` i
  `npm audit --audit-level=moderate` — poprawne, 0 znanych podatności.
- [x] Edge headless + axe-core — 22 główne trasy, 220 kombinacji 320, 375, 768,
  1024 i 1440 px w obu motywach oraz 88 pełnych przebiegów WCAG/best-practice:
  0 naruszeń axe i 0 poziomych przepełnień.
- [x] Interakcje przeglądarkowe — fokus obu menu i Escape, izolacja drawera,
  dialog usuwania, wymagana treść Tiptap, pojedynczy tabstop toolbara oraz loading
  formularza: wszystkie scenariusze poprawne, bez naruszeń CSP i blokad zasobów.
- [x] `php artisan view:cache`, statyczna kontrola CSRF/`$fillable`,
  `git diff --check` i kontrola wszystkich zmienionych widoków — poprawne.

### Migracje i zmienne środowiskowe

- Nie dodano migracji ani nie zmieniono schematu bazy.
- Nie dodano nowych wymaganych zmiennych środowiskowych.
- `SESSION_SECURE_COOKIE` pozostaje obsługiwane; bez jawnej wartości domyślnie
  włącza się teraz na `APP_ENV=production`.
- Tymczasowe konta, sesje, profil Edge i zależność axe-core usunięto po audycie.

### Znane ograniczenia i kontrole wdrożeniowe

- Fizyczny test NVDA/VoiceOver oraz produkcyjny smoke test CSP, HSTS, ciasteczek,
  SMTP, uploadu i nagłówków ustawianych przez proxy Railway nadal wymagają
  działającego środowiska wdrożeniowego.
- Warstwa aplikacji usuwa `Server` i `X-Powered-By`, ale końcowy reverse proxy może
  dodać własny nagłówek `Server`; należy to potwierdzić i wyłączyć po wdrożeniu.
- Lista CSP obejmuje obecne Google Fonts, Google Maps i obrazy Unsplash. Dodanie
  kolejnej zewnętrznej integracji wymaga świadomego rozszerzenia tej listy.
- Automatyczny audyt nie zastępuje testu z rzeczywistym czytnikiem ekranu ani
  zewnętrznego testu penetracyjnego.

## Poprzednia sesja — 2026-07-31 — pełny audyt interfejsu użytkownika

### Cel

Przejść wszystkie widoki publiczne, logowanie i panel administracyjny, uprościć
strukturę Blade, usunąć martwy lub zbędny CSS oraz doprowadzić układ, formularze,
karty, tabele i kontrolki do spójnego standardu bez zmiany identyfikacji wizualnej.

### Najważniejsze potwierdzone problemy

- Mobilny kalendarz poniżej 680 px nadal dziedziczył siedem kolumn z widoku
  desktopowego. Karty agendy były przez to układane w siedmiokolumnowej siatce.
- Etykiety komórek mobilnych tabel panelu powstawały dopiero w JavaScript.
  Bez skryptów układ kart tracił kontekst kolumn.
- Pola filtrów miały pionowy `flex-basis: 180px` po zmianie kierunku toolbara na
  kolumnowy, co powodowało bardzo duże puste odstępy na telefonie.
- Puste miesiące kalendarza renderowały 35–42 puste dni, które na telefonie były
  ukrywane bez jakiegokolwiek komunikatu dla użytkownika.
- Style formularzy, checkboxów, selectów, błędów i tekstów pomocniczych były
  powielone w arkuszach logowania, kontaktu i panelu.
- Widoki zawierały powtarzalne nagłówki panelu, techniczne wrappery nagłówków
  formularzy, niesemantyczne separatory dokumentów i klasy bez efektu.

### Wykonane

- [x] Przeanalizowano wszystkie 60 widoków Blade, 21 arkuszy CSS, 8 modułów
  JavaScript i wszystkie komponenty; każda z 248 klas CSS ma potwierdzone użycie
  (dwa warianty kalendarza są generowane dynamicznie).
- [x] Dodano wspólny komponent nagłówka panelu i zastąpiono nim 19 duplikatów.
  Liczba znaczników `div` w widokach spadła z 329 do 262.
- [x] Nagłówki sekcji formularzy są bezpośrednimi `h2`, a grupy checkboxów
  wydarzeń i funkcji klubowych używają semantycznych `fieldset` oraz `legend`.
- [x] Uproszczono regulamin i RODO: wspólny hero, semantyczne sekcje, usunięte
  wrappery treści i elementy `hr`, prawidłowe stopki dokumentów.
- [x] Ujednolicono wysokość i stany pól, selecty, checkboxy, teksty pomocnicze,
  błędy, przyciski, promienie kart, odstępy sekcji i siatki treści.
- [x] Dodano kompletne stany `hover`, `focus-visible`, `disabled`, `readonly`
  i `invalid`, większe cele dotykowe oraz dostępny fokus regionów tabel.
- [x] Mobilne tabele mają etykiety `data-label` renderowane po stronie serwera,
  podpisy `caption` i zachowują kontekst bez JavaScriptu. Usunięto martwy kod JS,
  który przepisywał nagłówki tabel.
- [x] Naprawiono jednokolumnową agendę kalendarza, kompaktowe filtry, czytelny
  pusty stan, responsywne metryki dashboardu i dostępność zamkniętego menu.
- [x] Usunięto martwe klasy CSS, klasy Blade bez stylów, zbędne klasy wysokości,
  style inline i elementy resetowania filtrów, gdy żaden filtr nie jest aktywny.
- [x] Dodano testy renderowania dziewięciu głównych ekranów publicznych oraz
  regresję semantyki mobilnych tabel bez JavaScriptu.

### Testy i kontrole

- [x] `composer test` — 57 testów, 474 asercje.
- [x] `vendor/bin/pint --test` — bez błędów.
- [x] `npm run build` — build Vite zakończony poprawnie.
- [x] Edge headless — 22 ekrany, szerokości 320, 375, 768, 1024, 1366 i 1920 px,
  oba motywy oraz otwarte menu publiczne i panelowe: 266 wariantów bez poziomego
  overflow.
- [x] Ręcznie oceniono 11 reprezentatywnych zrzutów: home, kalendarz, kontakt,
  logowanie, regulamin, RODO, dashboard, menu panelu, formularz i tabelę mobilną.
- [x] `php artisan view:cache` i `git diff --check` — poprawne.

### Migracje i zmienne środowiskowe

- Nie dodano migracji ani nie zmieniono schematu bazy.
- Nie dodano zmiennych środowiskowych.
- Tymczasowe konto, sesję, profile przeglądarki i zrzuty audytowe usunięto po
  zakończeniu kontroli.

### Znane ograniczenia

- Fizyczny test NVDA/VoiceOver i audyt kontrastu narzędziem na środowisku
  wdrożeniowym nadal wymagają zewnętrznego środowiska i odpowiedniego sprzętu.
- Zewnętrzne obrazy oraz mapy zachowano bez zmian; ich dostępność zależy od usług
  zewnętrznych i połączenia sieciowego.

## Poprzednia sesja — 2026-07-31 — ponowny pełny audyt backendu i panelu

### Cel

Ponownie przejść cały CRUD aktualności, wydarzeń, wyników, użytkowników,
funkcji klubowych i konkurencji, sprawdzić logi oraz rzeczywisty przepływ
uploadu, a następnie naprawić wszystkie potwierdzone problemy funkcjonalne.

### Najważniejsze potwierdzone przyczyny

- Logi wykazały błąd 500 przy edycji wydarzenia z konkurencją:
  `Builder::orWhereKey()` nie istnieje. Dotychczasowe testy nie otwierały tego
  wariantu formularza.
- Lokalne PHP CLI nadal ma `upload_max_filesize=2M`. `public/.user.ini` działa
  wyłącznie w CGI/FastCGI, więc nie steruje FrankenPHP używanym obecnie przez
  Railway Railpack. Laravel dopuszcza 6 MB, a całe żądanie galerii może mieć
  około 78 MB.
- Wynik można było spreparowanym żądaniem przypisać do treningu, usuniętego
  wydarzenia albo usuniętego użytkownika. Formularz tworzenia pokazywał również
  konkurencje miękko usuniętych wydarzeń.
- Kod konkurencji był zamieniany na wielkie litery dopiero po walidacji
  unikalności. `abc` mogło przejść walidację przy istniejącym `ABC`, po czym
  zakończyć się wyjątkiem unikalności bazy.
- Przy jednoczesnym zaznaczeniu usunięcia starej okładki i dodaniu nowej
  zapisywał się nowy plik, ale ginął jego tekst alternatywny. Identyfikatory
  zdjęć innej aktualności przechodziły regułę `exists`.
- Tablica przesłana celowo w zwykłym polu tekstowym dawała poprawny błąd
  walidacji, lecz po przekierowaniu `old()` powodowało błąd 500 w Blade.
- Profile utworzone przez `ClubDirectorySeeder` były nieaktywne, a publiczne
  zapytania celowo pokazują tylko aktywne osoby; świeżo zasilony katalog był
  przez to pusty.

### Wykonane

- [x] Naprawiono edycję wydarzeń i dodano test regresyjny otwierający formularz
  z przypisaną konkurencją.
- [x] Ograniczono konkurencje wydarzeń do aktywnych definicji, zachowując już
  przypisane definicje nieaktywne także podczas aktualizacji.
- [x] Wyniki przyjmują wyłącznie konkurencje istniejących zawodów. Edycja
  historycznego wyniku nadal zachowuje jego usunięte wydarzenie lub konto,
  natomiast nowy wynik nie może się do nich odwołać.
- [x] Ujednolicono kolejność blokad i transakcje dla wydarzeń, wyników,
  aktualności, funkcji klubowych i usuwania definicji konkurencji. Ponowne
  równoległe zapisy nie gubią okładek ani nie przekraczają limitu galerii.
- [x] Kod konkurencji i adresy e-mail są normalizowane przed walidacją
  unikalności. Komenda administratora i seedery używają tej samej postaci.
- [x] Naprawiono wymianę/usunięcie okładki, czyszczenie osieroconego opisu,
  kontrolę własności zdjęć galerii i zachowanie checkboxów przez `old()`.
- [x] Ukryto niedziałający podgląd publiczny aktualności zaplanowanej na
  przyszłość.
- [x] Dodano bezpieczne flashowanie błędnych danych wejściowych, dzięki czemu
  tablice w polach skalarnych kończą się błędem formularza, a nie błędem 500.
- [x] Uzupełniono polskie komunikaty i nazwy pól, `old()`, autocomplete,
  `aria-invalid`, `aria-describedby` oraz dostępne błędy filtrów wszystkich
  zasobów.
- [x] Rozszerzono macierz autoryzacji o wszystkie operacje CRUD dostępne
  wyłącznie administratorowi.
- [x] Dodano główny `php.ini` dla Railpack/FrankenPHP: 8 MB na plik, 85 MB na
  żądanie i 20 plików. Zachowano `.user.ini` dla CGI/FastCGI oraz opcje
  bezpośredniego serwera PHP w `composer dev`.
- [x] Zaktualizowano wdrożenie Railway o trwały wolumen, katalog publiczny i
  `RAILPACK_SKIP_MIGRATIONS=true`, aby własny pre-deploy nie był dublowany
  automatycznym uruchomieniem migracji i seedera.
- [x] Profile katalogu klubowego są tworzone i synchronizowane jako aktywne;
  publiczny kontakt i lista trenerów zostały objęte testem seedera.

### Testy i kontrole

- [x] `composer test` — 55 testów, 413 asercji.
- [x] `vendor/bin/pint --test` — bez błędów.
- [x] `npm run build` — build Vite zakończony poprawnie.
- [x] Rzeczywisty HTTP multipart — JPEG 3 146 483 B, zapis HTTP 200 po
  przekierowaniu i publiczny plik HTTP 200; dane oraz plik testowy usunięto.
- [x] `php -c php.ini` potwierdza `8M|85M|20`.
- [x] `composer validate --strict`, `composer audit --locked` i
  `npm audit --audit-level=moderate` — poprawne, 0 znanych podatności.
- [x] Cache konfiguracji, zdarzeń, tras i widoków — poprawny; po kontroli cache
  wyczyszczono dla lokalnego trybu deweloperskiego.
- [x] Lokalny PostgreSQL — wszystkie 10 migracji ma status `Ran`.

### Migracje i zmienne środowiskowe

- Nie dodano migracji ani nie zmieniono schematu bazy.
- Nowa zmienna wdrożeniowa: `RAILPACK_SKIP_MIGRATIONS=true` na Railway, ponieważ
  migracje wykonuje już `railway/init-app.sh` w pre-deploy.
- `RAILPACK_PHP_ROOT_DIR=/app/public` pozostaje opcją awaryjną, gdy automatyczne
  wykrywanie Laravel nie ustawi katalogu dokumentów.

### Pozostałe czynności zależne od środowiska

- Na Railway nadal trzeba podpiąć trwały wolumen do
  `/app/storage/app/public`, ustawić `RAILPACK_SKIP_MIGRATIONS=true` i wykonać
  produkcyjny smoke test po wdrożeniu.
- Bazy zasilone starszą wersją `ClubDirectorySeeder` wymagają jednorazowego
  ponownego uruchomienia tego seedera, aby uaktywnić profile katalogowe.
- Usunięcie aktualności pozostaje miękkie, więc jej pliki są przechowywane do
  czasu wdrożenia przywracania lub trwałego czyszczenia kosza.
- Rzeczywiste SMTP, wolumen Railway oraz test NVDA/VoiceOver wymagają dostępu do
  środowiska produkcyjnego.

## Poprzednia sesja — 2026-07-31

### Cel

Pełny audyt aplikacji jako produktu produkcyjnego: backend, uploady, formularze,
panel, część publiczna, responsywność, dostępność, bezpieczeństwo, wydajność,
testy i dokumentacja wdrożeniowa.

### Najważniejsza diagnoza

Formularz dopuszczał obrazy do 6 MB, ale aktywne PHP przyjmowało pojedynczy plik
tylko do 2 MB (`upload_max_filesize=2M`). PHP odrzucało plik przed walidacją
Laravela, dlatego użytkownik otrzymywał surowe `validation.uploaded` i kontroler
nie miał dostępu do poprawnego pliku. Dodatkowo opcje `php -d` przekazane do
`artisan serve` nie docierały do uruchamianego przez Artisan procesu potomnego.
Dysk publiczny, katalogi i lokalne połączenie `public/storage` były sprawne.

### Wykonane

- [x] Ujednolicono limit aplikacji (6 MB) z kopertą PHP-FPM (8 MB na plik,
  85 MB na żądanie i 20 plików).
- [x] Dodano pełne polskie komunikaty uploadu, typów, wymiarów, liczby i rozmiaru
  plików oraz walidację po stronie przeglądarki.
- [x] Utwardzono zapis, podmianę i usuwanie zdjęć: transakcja bazodanowa,
  sprzątanie nowych plików po błędzie, zachowanie starej okładki i raportowanie
  błędów dysku.
- [x] Dodano test faktycznego zapisu i usunięcia na skonfigurowanym dysku
  publicznym oraz test `UPLOAD_ERR_INI_SIZE`.
- [x] Zastąpiono nieskuteczne lokalne `artisan serve` bezpośrednim serwerem PHP
  z kontrolowanymi limitami i routerem `server.php`.
- [x] Wykonano rzeczywisty test HTTP multipart: obraz 5 890 980 B został zapisany
  i publicznie zwrócony z HTTP 200, a obraz 6 754 830 B został odrzucony przez
  Laravel z polskim komunikatem. Dane i pliki testowe usunięto.
- [x] Uzupełniono serwerowe podsumowanie błędów i powiązania
  `aria-invalid`/`aria-describedby` we wszystkich formularzach panelu.
- [x] Ujednolicono `old()`, etykiety, autocomplete, pola dat, selecty i checkboxy.
- [x] Znormalizowano adresy e-mail do małych liter w requestach i modelu.
- [x] Dodano walidowane enumami filtry publiczne i panelowe oraz ograniczenia
  długości wyszukiwanych fraz.
- [x] Zmieniono wyszukiwanie na niewrażliwe na wielkość liter `ILIKE` zgodne
  z PostgreSQL.
- [x] Kalendarz pokazuje wydarzenie wielodniowe w każdym dniu zakresu bez
  duplikowania rekordu w bazie; dodano indeks `sport_events.end_at`.
- [x] Zabezpieczono ostatnie aktywne konto administratora blokadą transakcyjną.
- [x] Zachowano nieaktywne konkurencje już przypisane do edytowanego wydarzenia.
- [x] Naprawiono relację wyniku z definicją konkurencji przez jawne wskazanie klucza
  `event_competitions.competition_definition_id`; publiczne wyniki, CRUD panelu
  i dashboard zawodnika ponownie renderują pełne dane konkurencji.
- [x] Opis trenera jest dostępny w formularzu tylko przy zaznaczonej opcji
  „Trener”; backend odrzuca spreparowany opis dla osoby bez tego statusu i usuwa
  istniejący opis po odznaczeniu opcji.
- [x] Ograniczono dane dashboardu zależnie od roli i dodano podstawowe nagłówki
  bezpieczeństwa HTTP.
- [x] Dodano polskie strony błędów 403, 404, 413, 419, 429, 500 i 503.
- [x] Naprawiono overflow przy 320 px i mobilny selektor miesiąca kalendarza.
- [x] Sprawdzono 9 stron publicznych i 13 ekranów panelu przy szerokościach
  320, 375, 768, 1024, 1366 i 1920 px — 132 warianty bez poziomego scrolla.
- [x] Usunięto puste arkusze CSS, przykładowy test i komendę Laravel oraz pusty
  favicon; dodano właściwy favicon SVG.
- [x] Ukryto odnośnik do brakującego pliku uchwały zamiast prowadzić do 404.
- [x] Dodano instrukcję wdrożenia Railway i produkcyjny skrypt inicjalizacyjny.
- [x] Zaktualizowano `.env.example` i README.

### Testy i kontrole

- [x] `composer test` — 40 testów, 192 asercje.
- [x] `vendor/bin/pint --test` — bez błędów.
- [x] `npm run build` — build Vite zakończony poprawnie.
- [x] `composer validate --strict` — poprawny plik `composer.json`.
- [x] `composer audit --locked` — 0 znanych podatności.
- [x] `npm audit --audit-level=moderate` — 0 znanych podatności.
- [x] Cache widoków, tras, konfiguracji i zdarzeń — poprawny.

### Migracje

Dodano i uruchomiono lokalnie:

- `2026_07_31_000700_add_end_at_index_to_sport_events_table.php` — indeks pola
  `sport_events.end_at` używanego przez zapytania kalendarza wielodniowego.

Lokalny PostgreSQL zawiera 10 wykonanych migracji.

### Zmienne środowiskowe

- `MEDIA_DISK=public` — jawny dysk mediów.
- `CONTACT_RECIPIENT_EMAIL=zarzad@ks-krokus.pl` — preferowana nazwa odbiorcy
  formularza; stara `CONTACT_TO_ADDRESS` pozostaje zgodna wstecznie.
- Produkcja powinna dodatkowo ustawić wartości wymienione w
  `docs/DEPLOYMENT.md`, w szczególności `APP_KEY`, PostgreSQL, SMTP,
  `LOG_CHANNEL=stderr` i `SESSION_SECURE_COOKIE=true`.

### Ograniczenia i czynności wdrożeniowe

- Na Railway trzeba ręcznie podpiąć trwały wolumen do
  `/app/storage/app/public`; bez niego upload działa, ale pliki nie przetrwają
  ponownego wdrożenia.
- Należy wykonać produkcyjny smoke test uploadu po podpięciu wolumenu oraz test
  wysyłki przez rzeczywiste SMTP.
- W repozytorium nadal brakuje pliku `public/files/Uchwala_kolekcjonerska.pdf`;
  interfejs nie pokazuje niedziałającego odnośnika.
- Usunięcie aktualności jest miękkie, więc jej zdjęcia pozostają na dysku do
  czasu wdrożenia procesu przywracania lub trwałego czyszczenia kosza.
- Końcowy audyt z fizycznym czytnikiem ekranu oraz monitoring produkcyjny
  wymagają działającego środowiska wdrożeniowego.

## Historia

### 2026-07-31 — odtworzenie lokalnych danych startowych

- Cel: wyczyścić lokalną bazę PostgreSQL i odtworzyć dane startowe bez wyników.
- Wykonano: `php artisan migrate:fresh --seed --force`; dodano 23 użytkowników,
  5 funkcji klubowych, 10 przypisań funkcji i 14 definicji konkurencji.
- Stan końcowy treści: 0 aktualności, 0 wydarzeń i 0 wyników.
- Testy: 40 testów / 192 asercje, Pint i build Vite poprawne.

### 2026-07-31 — lokalny PostgreSQL

- Cel: zastąpić SQLite lokalnym PostgreSQL bez utraty danych.
- Wykonano: konfigurację aplikacji i testów, dwie bazy, migracje oraz
  przeniesienie danych.
- Testy: 26 testów / 100 asercji, Pint i build poprawne.

### 2026-07-31 — wyłącznie PostgreSQL

- Cel: usunąć konfigurację i lokalne dane SQLite oraz inne nieużywane
  połączenia.
- Wykonano: pojedyncze połączenie runtime `pgsql`, usunięcie pliku SQLite
  i test regresyjny konfiguracji.
- Testy: 27 testów / 103 asercje, Pint i build poprawne.

### 2026-07-31 — audyt produkcyjny i uploady

- Cel: doprowadzić aplikację do stanu gotowego do wdrożenia.
- Wykonano: naprawę uploadów, formularzy, kalendarza, responsywności,
  dostępności, bezpieczeństwa, wydajności i dokumentacji wdrożeniowej.
- Testy: 35 testów / 152 asercje, Pint, Vite, audyty zależności i cache poprawne.
- Pozostało: czynności zależne od infrastruktury opisane powyżej.

### 2026-07-31 — wyniki i profil trenera

- Cel: przywrócić działanie modułu wyników i zabezpieczyć zależność opisu trenera
  od statusu trenera.
- Wykonano: poprawkę klucza obcego relacji konkurencji, testy publicznych wyników,
  CRUD i dashboardu oraz warunkowy interfejs, walidację i czyszczenie opisu trenera.
- Testy: 40 testów / 192 asercje, Pint i build Vite poprawne.
