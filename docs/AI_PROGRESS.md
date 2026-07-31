# AI_PROGRESS.md

## Branch bazowy

`main`

## Bieżąca sesja — 2026-07-31

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
