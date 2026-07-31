# AI_PROGRESS.md

## Branch bazowy

`main`

## Bieżąca sesja — 2026-07-31 — ponowny pełny audyt backendu i panelu

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
