# Wdrożenie produkcyjne — Railway

## Usługa aplikacji

1. Ustaw katalog główny usługi na `/ks-krokus`.
2. Użyj automatycznego buildera Railway dla Laravel/PHP 8.4.
3. Jako build command ustaw:

   ```bash
   npm ci && npm run build && php artisan storage:link && php artisan event:cache && php artisan route:cache && php artisan view:cache
   ```

4. Jako pre-deploy command ustaw:

   ```bash
   chmod +x ./railway/init-app.sh && ./railway/init-app.sh
   ```

5. Healthcheck kieruj na `/up`.

Skrypt pre-deploy wykonuje wyłącznie migracje. Link `public/storage` i cache
niezależne od danych powstają wcześniej w obrazie aplikacji. Wolumen nie jest
dostępny podczas buildu ani pre-deploy — zostanie zamontowany dopiero w
uruchomionej usłudze.

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

`public/.user.ini` ustawia dla PHP-FPM:

- `upload_max_filesize=8M`,
- `post_max_size=85M`,
- `max_file_uploads=20`.

Laravel przyjmuje maksymalnie 6 MB na zdjęcie, do 12 zdjęć galerii i jedno
zdjęcie główne. Wyższy limit PHP jest celowy: pozwala Laravelowi zwrócić
naturalny polski błąd walidacji zamiast odrzucić plik przed uruchomieniem
aplikacji.

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
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
MEDIA_DISK=public

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

Dla starszej konfiguracji `CONTACT_TO_ADDRESS` nadal działa jako wartość
awaryjna, ale nowe środowiska powinny używać `CONTACT_RECIPIENT_EMAIL`.
Nie zapisuj sekretów ani prawdziwego `APP_KEY` w repozytorium.

Jeżeli usługa nadal korzysta ze starszego Nixpacks i nie rozpoznaje katalogu
publicznego Laravel, ustaw dodatkowo:

```env
NIXPACKS_PHP_ROOT_DIR=/app/public
```

## Kontrola po wdrożeniu

1. Otwórz `/up`, stronę główną i panel.
2. Dodaj aktualność ze zdjęciem większym niż 2 MB i mniejszym niż 6 MB.
3. Sprawdź publiczny adres obrazu pod `/storage/...`.
4. Podmień zdjęcie i potwierdź usunięcie poprzedniego pliku z wolumenu.
5. Wykonaj redeploy i potwierdź, że zdjęcie nadal jest dostępne.
6. Wyślij formularz kontaktowy i sprawdź logi `stderr`.
7. Sprawdź stan migracji: `php artisan migrate:status`.
