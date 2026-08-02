# Wdrożenie produkcyjne — Railway

## Usługa aplikacji

1. Ustaw katalog główny usługi na `/ks-krokus`.
2. Użyj automatycznego buildera Railpack dla Laravel/PHP 8.4. Railpack
   rozpoznaje Laravel i uruchamia aplikację przez FrankenPHP z katalogiem
   dokumentów `/app/public`. Composer jawnie wymaga rozszerzeń `fileinfo`, `PDO`
   i `pdo_pgsql`, dlatego Railpack dołącza obsługę PostgreSQL do obrazu.
3. Jako build command ustaw:

   ```bash
   npm ci && npm run build && php artisan storage:link && php artisan optimize
   ```

4. Jako pre-deploy command ustaw:

   ```bash
   chmod +x ./railway/init-app.sh && ./railway/init-app.sh
   ```

5. Healthcheck kieruj na `/up`.
6. Ustaw `RAILPACK_SKIP_MIGRATIONS=true`. W przeciwnym razie wygenerowany przez
   Railpack start command ponownie uruchomi migracje i seeder po wykonaniu
   własnego pre-deploy command.

Skrypt pre-deploy wykonuje wyłącznie migracje. Link `public/storage` i cache
konfiguracji, zdarzeń, tras oraz widoków powstają wcześniej w obrazie aplikacji;
startowy skrypt Railpack odświeża optymalizacje także w uruchamianym kontenerze.
Wolumen nie jest dostępny podczas buildu ani pre-deploy — zostanie zamontowany
dopiero w uruchomionej usłudze.

## Migracje modułu ogłoszeń

Wdrożenie wykonuje dwie nowe migracje przez istniejące `php artisan migrate --force`:

- `2026_08_02_000900_create_sale_listings_tables.php` tworzy oferty, zdjęcia,
  historię moderacji i zgłoszenia wraz z kluczami obcymi oraz indeksami,
- `2026_08_02_001000_create_notifications_table.php` tworzy magazyn powiadomień
  bazodanowych.

Migracje nie modyfikują historycznych tabel. Przed produkcyjnym wdrożeniem należy
wykonać standardowy backup PostgreSQL.

## Trwałe zdjęcia

Lokalny system plików wdrożenia Railway jest efemeryczny. Do usługi aplikacji
trzeba podłączyć Railway Volume z mount path:

```text
/app/storage/app/public
```

Bez wolumenu upload zadziała, ale pliki znikną przy kolejnym wdrożeniu. Po
podłączeniu wolumenu warto włączyć jego automatyczne backupy. Aplikacja używa
dysku `public`, a `storage:link` udostępnia pliki pod `/storage/...`.

## Limity uploadu

Repozytorium zawiera dwa warianty konfiguracji serwera:

- główny `php.ini` — używany przez Railpack/FrankenPHP na Railway,
- `public/.user.ini` — awaryjny wariant dla hostingów CGI/FastCGI.

Oba ustawiają:

- `upload_max_filesize=8M`,
- `post_max_size=85M`,
- `max_file_uploads=20`.

Laravel przyjmuje maksymalnie 6 MB na zdjęcie, do 12 zdjęć galerii aktualności
oraz do 10 zdjęć ogłoszenia. Wyższy limit PHP jest celowy: pozwala Laravelowi zwrócić
naturalny polski błąd walidacji zamiast odrzucić plik przed uruchomieniem
aplikacji. `.user.ini` nie jest odczytywany przez FrankenPHP, dlatego sam plik
w katalogu `public` nie wystarczał do zagwarantowania limitów na Railway.
Lokalny `composer dev` przekazuje te same limity bezpośrednio do procesu PHP.

## Minimalne zmienne środowiskowe

```env
APP_NAME="KS Krokus"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://twoja-domena.example
APP_KEY=base64:...
APP_TIMEZONE=Europe/Warsaw
APP_LOCALE=pl
APP_FALLBACK_LOCALE=pl
APP_FAKER_LOCALE=pl_PL

DB_CONNECTION=pgsql
DB_URL=${{Postgres.DATABASE_URL}}

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=deferred
FILESYSTEM_DISK=local
MEDIA_DISK=public
RAILPACK_SKIP_MIGRATIONS=true

LOG_CHANNEL=stderr
LOG_LEVEL=warning

CONTACT_RECIPIENT_EMAIL=zarzad@ks-krokus.pl
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME="KS Krokus"
```

`ext-gd` jest wymaganym rozszerzeniem PHP. Moduł ogłoszeń wykorzystuje je do
korekty orientacji JPEG, skalowania, optymalizacji i generowania miniatur bez
powiększania małych zdjęć.

## Powiadomienia i kolejka

Powiadomienia ogłoszeń są zapisywane w tabeli `notifications` i implementują
`ShouldQueue`. Zalecane `QUEUE_CONNECTION=deferred` wykonuje je po wysłaniu
odpowiedzi HTTP i nie wymaga osobnego workera. Jeżeli środowisko zostanie
przełączone na `QUEUE_CONNECTION=database`, trzeba dodać stale działającą usługę
z komendą:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90
```

## Cron wygasania ogłoszeń

Dodaj na Railway osobną krótkotrwałą usługę z tego samego repozytorium i katalogu
`/ks-krokus`. Ustaw jej start command na:

```bash
php artisan schedule:run
```

W polu Cron Schedule ustaw:

```text
*/5 * * * *
```

Railway interpretuje harmonogram cron w UTC i dopuszcza minimalny odstęp 5 minut.
Uruchamianie `schedule:run` co 5 minut pozwala Laravelowi samodzielnie respektować
`Europe/Warsaw` oraz zmianę czasu; właściwa komenda `listings:expire` jest należna
codziennie o 01:15. Usługa cron musi mieć te same zmienne aplikacji i dostęp do
PostgreSQL, a proces ma zakończyć się po wykonaniu komendy. Alternatywnie można
uruchamiać bezpośrednio `php artisan listings:expire` raz dziennie w UTC, ale wtedy
trzeba ręcznie uwzględniać zmianę czasu w Polsce.

Dla starszej konfiguracji `CONTACT_TO_ADDRESS` nadal działa jako wartość
awaryjna, ale nowe środowiska powinny używać `CONTACT_RECIPIENT_EMAIL`.
Nie zapisuj sekretów ani prawdziwego `APP_KEY` w repozytorium.

Jeżeli automatyczne wykrywanie aplikacji Laravel nie ustawi katalogu publicznego,
ustaw dodatkowo:

```env
RAILPACK_PHP_ROOT_DIR=/app/public
```

## Kontrola po wdrożeniu

1. Otwórz `/up`, stronę główną i panel.
2. Dodaj aktualność ze zdjęciem większym niż 2 MB i mniejszym niż 6 MB.
3. Sprawdź publiczny adres obrazu pod `/storage/...`.
4. Podmień zdjęcie i potwierdź usunięcie poprzedniego pliku z wolumenu.
5. Wykonaj redeploy i potwierdź, że zdjęcie nadal jest dostępne.
6. Wyślij formularz kontaktowy i sprawdź logi `stderr`.
7. Sprawdź stan migracji: `php artisan migrate:status`.
8. Sprawdź cache: `php artisan about --only=cache`; konfiguracja, zdarzenia,
   trasy i widoki powinny być oznaczone jako zapisane w cache.
9. Dodaj ogłoszenie z 10 zdjęciami, zatwierdź je i sprawdź miniatury po redeployu.
10. Uruchom ręcznie `php artisan listings:expire` i sprawdź log usługi cron.
