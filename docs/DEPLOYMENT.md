# Wdrożenie produkcyjne KS Krokus

## Założenia

- katalog aplikacji w repozytorium: `/ks-krokus`,
- PHP 8.4, Laravel 13 i PostgreSQL,
- publiczny adres HTTPS zapisany w `APP_URL` bez końcowego ukośnika,
- strefa aplikacji i schedulera: `Europe/Warsaw`,
- poczta przez SMTP,
- kolejka produkcyjna `database` z osobnym, stale działającym workerem,
- media na jednym dysku `MEDIA_DISK=public`.

Nie używaj na produkcji `QUEUE_CONNECTION=deferred`. Powiadomienia, wiadomość
kontaktu oraz linki hasła implementują kolejkę i muszą być obsługiwane przez
worker. Zadania zawierające treść kontaktu lub token hasła są szyfrowane kluczem
aplikacji. Tokeny haseł nie są zapisywane w komunikatach interfejsu ani logach.

## Minimalne zmienne środowiskowe

```env
APP_NAME="KS Krokus"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://twoja-domena.example
ASSET_URL=
APP_KEY=base64:...
APP_TIMEZONE=Europe/Warsaw
APP_LOCALE=pl
APP_FALLBACK_LOCALE=pl
APP_FAKER_LOCALE=pl_PL

DB_CONNECTION=pgsql
DB_URL=${{Postgres.DATABASE_URL}}

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
TRUSTED_PROXIES=*
CACHE_STORE=database
QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=120
FILESYSTEM_DISK=local
MEDIA_DISK=public
RAILPACK_SKIP_MIGRATIONS=true

LOG_CHANNEL=stderr
LOG_LEVEL=warning
LOG_DAILY_DAYS=14

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME="KS Krokus"
CONTACT_RECIPIENT_EMAIL=zarzad@ks-krokus.pl
ACCOUNT_REQUEST_RETENTION_MONTHS=12
ACCOUNT_REQUEST_RETENTION_ACTION=anonymize

ADMIN_USER_NAME=...
ADMIN_USER_EMAIL=...
ADMIN_USER_PASSWORD=...
```

`MAIL_FROM_ADDRESS` powinien być adresem zaakceptowanym przez dostawcę SMTP.
Formularz kontaktowy zawsze używa tego nadawcy, a adres osoby piszącej ustawia
jako `Reply-To`. `APP_URL` steruje absolutnymi adresami w e-mailach, dlatego na
produkcji musi wskazywać właściwą domenę HTTPS. Nie zapisuj kluczy, haseł SMTP,
hasła administratora ani prawdziwego `APP_KEY` w repozytorium.

`ASSET_URL` pozostaw puste, gdy zasoby są pod tą samą domeną, albo ustaw pełny
adres zaufanego CDN i odpowiednio rozszerz CSP. `TRUSTED_PROXIES=*` stosuj na
Railway tylko za kontrolowanym ingress platformy, który nadpisuje nagłówki
forwarded; na VPS preferuj konkretne adresy lub zakresy CIDR reverse proxy.
Bez poprawnego proxy aplikacja może błędnie rozpoznać HTTPS i IP używane przez
rate limitery.

Wartość 12 miesięcy nie jest ostateczną decyzją prawną. Zarząd klubu musi
zatwierdzić okres i wybór `anonymize`/`delete` przed uruchomieniem schedulera na
produkcji. Preferowana anonimizacja zachowuje minimalny audyt decyzji.

Laravel 13 korzysta z Symfony Mailer, który dla STARTTLS na porcie 587 oczekuje
wewnętrznego schematu `smtp`, nie literalnego `tls`. `config/mail.php` świadomie
mapuje wymagane środowiskowe `MAIL_SCHEME=tls` na `smtp`; szyfrowanie STARTTLS
pozostaje włączane automatycznie przez transport. Port 465 wymagałby `smtps`.

## Usługa aplikacji na Railway

1. Ustaw Root Directory na `/ks-krokus`.
2. Użyj Railpack dla Laravel/PHP 8.4. Katalog dokumentów powinien wskazywać
   `/app/public`; w razie problemu ustaw `RAILPACK_PHP_ROOT_DIR=/app/public`.
3. Build Command:

   ```bash
   npm ci && npm run build && php artisan storage:link && php artisan optimize
   ```

4. Pre-deploy Command:

   ```bash
   chmod +x ./railway/init-app.sh && ./railway/init-app.sh
   ```

5. Healthcheck: `/up`.
6. Ustaw `RAILPACK_SKIP_MIGRATIONS=true`, ponieważ `railway/init-app.sh`
   wykonuje `php artisan migrate --force`. Skrypt wysyła też sygnał
   `queue:restart`; nie uruchamia seedera ani danych demonstracyjnych.

Wolumen nie jest dostępny podczas builda ani pre-deploy. Link `public/storage`
powstaje w obrazie, a jego cel zostanie przykryty trwałym wolumenem po starcie.

## Worker kolejki

Tabele `jobs`, `job_batches` i `failed_jobs` tworzy istniejąca migracja. Worker ma
timeout 90 sekund, a `DB_QUEUE_RETRY_AFTER=120`, więc zadanie nie zostanie pobrane
ponownie przed zakończeniem poprzedniej próby. Każde zadanie ma maksymalnie trzy
próby.

### Railway

Dodaj osobną stale działającą usługę z tego samego repozytorium, Root Directory
`/ks-krokus`, tymi samymi zmiennymi aplikacji i połączeniem PostgreSQL. Start
Command:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90
```

Po wdrożeniu wykonaj `php artisan queue:restart`. Skrypt pre-deploy aplikacji robi
to automatycznie przez wspólny cache bazodanowy, ale po zmianie konfiguracji
workera lub awaryjnym wdrożeniu można wydać komendę ręcznie w shellu Railway.
Proces workera powinien mieć włączoną politykę automatycznego restartu.

### VPS i systemd

W katalogu `/var/www/ks-krokus` uruchamiaj tę samą komendę pod kontrolą systemd
lub Supervisora:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90
```

Po każdym deployu:

```bash
cd /var/www/ks-krokus
php artisan queue:restart
```

### Diagnostyka i monitoring

```bash
php artisan queue:failed
php artisan queue:retry all
php artisan queue:monitor database:default --max=100
```

`queue:monitor` zwraca błąd, gdy kolejka `default` przekroczy 100 oczekujących
zadań, więc można podpiąć wynik pod monitoring. Dodatkowo monitoruj czas
najstarszego wpisu oraz liczbę rekordów w tabeli `jobs`, stan usługi workera i
przyrost `failed_jobs`. Po naprawieniu przyczyny użyj `queue:retry all`; nie
usuwaj nieprzeanalizowanych błędów tylko po to, aby wyzerować licznik.

## Scheduler, wygasanie ogłoszeń i retencja wniosków

`listings:expire` jest jedynym automatycznym mechanizmem przypomnień i wygaszania.
Komenda jest idempotentna: znacznik `expiration_reminder_sent_at` zapobiega
drugiemu przypomnieniu, a workflow zmienia tylko zatwierdzone, przeterminowane
ogłoszenia. Laravel planuje ją codziennie o 01:15 w `Europe/Warsaw` i stosuje
`withoutOverlapping`.

`account-requests:apply-retention` działa codziennie o 02:15 w tej samej strefie
i również używa `withoutOverlapping`. Przetwarza wyłącznie odrzucone wnioski po
okresie liczonym od decyzji, nie zapisuje danych osobowych do konsoli ani logu i
zwraca statystyki liczby zanonimizowanych oraz usuniętych rekordów. Po zatwierdzeniu
polityki uruchom komendę ręcznie dwa razy na kopii danych i potwierdź, że drugi
przebieg ma zerowe efekty.

Railway: utwórz osobną usługę cron z tym samym repozytorium, katalogiem aplikacji,
zmiennymi i bazą. Command:

```bash
php artisan schedule:run
```

Cron Schedule:

```text
*/5 * * * *
```

Railway uruchamia cron w UTC, ale wywołanie schedulera co 5 minut pozwala
Laravelowi obsłużyć `Europe/Warsaw` i zmianę czasu.

VPS — crontab:

```cron
* * * * * cd /var/www/ks-krokus && php artisan schedule:run >> /dev/null 2>&1
```

Po konfiguracji sprawdź `php artisan schedule:list`, a następnie uruchom ręcznie
`php artisan listings:expire` oraz `php artisan account-requests:apply-retention`
i zweryfikuj statystyki oraz stan kontrolnych rekordów bez kopiowania danych
osobowych do logów.

## Nagłówki, sesja i CSP

Po wdrożeniu sprawdź odpowiedź końcowego reverse proxy, nie tylko odpowiedź PHP:

- `Strict-Transport-Security: max-age=31536000; includeSubDomains`,
- `X-Content-Type-Options: nosniff`,
- `Referrer-Policy: strict-origin-when-cross-origin`,
- `Permissions-Policy: camera=(), geolocation=(), microphone=()`,
- wymuszane `Content-Security-Policy` z nonce,
- ciasteczko sesji z `Secure`, `HttpOnly` i `SameSite=Lax`.

CSP odpowiada obecnym Google Maps, Google Fonts, własnym skryptom Vite i obrazom
ze storage/Unsplash. Przed dodaniem nowej integracji przetestuj rozszerzoną politykę
na stagingu; jeśli skala zmiany jest duża, najpierw obserwuj wariant
`Content-Security-Policy-Report-Only`, a dopiero potem przenieś dyrektywy do polityki
wymuszanej. Reverse proxy Railway może ponownie dodać nagłówek `Server`, którego
aplikacja usuwa — wymaga to osobnej kontroli platformy.

## Logi i rotacja

Produkcja Railway używa `LOG_CHANNEL=stderr` i co najmniej `LOG_LEVEL=warning`.
Rotacją oraz długością retencji stdout/stderr zarządza platforma i może ona zależeć
od planu; aplikacja nie potrafi wymusić czasu przechowywania tych logów. Dla dłuższej
retencji skonfiguruj zewnętrzny log drain z ograniczonym dostępem i własną polityką
usuwania. Nie używaj kanału `single` na efemerycznym dysku Railway.

Na VPS użyj `LOG_CHANNEL=daily`, `LOG_LEVEL=warning` i `LOG_DAILY_DAYS=14`; Laravel
tworzy plik dzienny i usuwa starsze pliki. Jeśli logi trafiają do journald/syslog,
ustaw dodatkowo limit przestrzeni i retencję w konfiguracji systemowej. Katalog
`storage/logs` powinien być dostępny tylko dla użytkownika aplikacji i operatorów.
Procesor Monolog maskuje znane pola haseł, tokenów, dokumentów i wniosków, ale
komunikaty logów nadal muszą zawierać wyłącznie techniczne identyfikatory.

## Trwały storage

W aplikacji wszystkie uploady używają `MEDIA_DISK`:

- okładki i galerie aktualności,
- oryginały i miniatury ogłoszeń.

Nie ma obecnie uploadu dokumentów ani avatarów. Dokumenty klubu są statycznymi
plikami publicznymi. Każdy przyszły upload obrazu powinien również korzystać z
`MEDIA_DISK`, a dane prywatne nie powinny trafiać na dysk publiczny.

Na Railway podłącz Volume do usługi aplikacji pod dokładną ścieżką:

```text
/app/storage/app/public
```

Następnie:

1. potwierdź istnienie linku `public/storage` (`php artisan storage:link` jest
   bezpieczne do ponownego uruchomienia),
2. dodaj obraz i sprawdź adres `/storage/...`,
3. wykonaj redeploy i potwierdź, że obraz oraz miniatura nadal istnieją,
4. włącz backupy wolumenu.

Widoki nie renderują pustych `img`; przy braku relacji pokazują komponent
placeholdera, a wspólny skrypt zastępuje również obraz, którego plik fizycznie
zniknął. Podmiana lub jawne usunięcie pojedynczego zdjęcia sprząta jego pliki.
Soft delete aktualności i ogłoszenia zachowuje media, aby rekord dało się
przywrócić. Brak obecnie automatycznego force-delete starych aktualności.

Obsługiwane formaty to JPG/JPEG, PNG i WebP, do 6 MB na plik, maksymalnie 12 zdjęć
w galerii aktualności i 10 w ogłoszeniu. PHP ma kopertę 8 MB na plik, 85 MB na
żądanie i 20 plików (`php.ini`; `public/.user.ini` jest wariantem CGI/FastCGI).

## Pierwsze uruchomienie produkcji

1. Ustaw wszystkie zmienne, utwórz PostgreSQL i Volume.
2. Wygeneruj `APP_KEY` poza repozytorium.
3. Uruchom migracje:

   ```bash
   php artisan migrate --force
   ```

4. Utwórz administratora po ustawieniu `ADMIN_USER_NAME`, `ADMIN_USER_EMAIL` i
   silnego `ADMIN_USER_PASSWORD`:

   ```bash
   php artisan db:seed --class=AdminUserSeeder --force
   ```

`AdminUserSeeder` nie ma domyślnego hasła, używa `Hash::make`, `updateOrCreate`,
przywraca miękko usunięte konto i zawsze ustawia rolę admin oraz aktywność. Jest
idempotentny, ale ponowne uruchomienie świadomie ustawia hasło na bieżącą wartość
`ADMIN_USER_PASSWORD`; po bootstrapie usuń tę zmienną z usługi, jeśli nie chcesz
używać seedera do rotacji hasła, a następnie wykonaj redeploy lub ponownie zbuduj
cache konfiguracji, aby sekret nie pozostał w aktywnym `config.php`.

Klasyfikacja seederów:

- bezpieczne produkcyjnie: `AdminUserSeeder`, `CompetitionDefinitionSeeder`,
  `ClubPositionSeeder`, `ClubDirectorySeeder`,
- deweloperskie/demo: obecnie brak osobnego seedera danych demo.

`ClubDirectorySeeder` zapisuje rzeczywisty katalog skonfigurowany w `contacts.php`,
więc uruchamiaj go tylko wtedy, gdy te dane mają zostać opublikowane. Nie uruchamiaj
fabryk ani ad-hoc danych testowych na produkcji. `DatabaseSeeder` składa wyłącznie
wymienione seedery produkcyjne, ale do pierwszego konta zalecana jest precyzyjna
komenda `--class=AdminUserSeeder`.

## Kontrola po wdrożeniu

1. Sprawdź `/up`, stronę główną, logowanie i panel.
2. Wykonaj `php artisan migrate:status`, `php artisan route:list` i
   `php artisan schedule:list`.
3. Sprawdź aplikację i workera: wiadomość kontaktowa powinna pojawić się w
   kolejce i dotrzeć z poprawnym `From` oraz `Reply-To`.
4. Wyślij reset hasła aktywnego konta, sprawdź link HTTPS oraz neutralną odpowiedź
   dla nieistniejącego i nieaktywnego adresu.
5. Z panelu wyślij ponownie link ustawienia hasła i sprawdź zapis administratora
   oraz czasu bez wyświetlania tokenu.
6. Dodaj aktualność i ogłoszenie z 10 zdjęciami; sprawdź WebP, miniatury,
   placeholder, usuwanie i trwałość po redeployu.
7. Uruchom `php artisan listings:expire` dwukrotnie i potwierdź pojedyncze efekty.
8. Sprawdź `php artisan queue:failed` i licznik `jobs`.
9. Potwierdź cache konfiguracji, zdarzeń, tras i widoków przez `php artisan about`.

Rzeczywiste dostarczenie SMTP, zachowanie reverse proxy, backup/restore bazy i
wolumenu oraz alerty wymagają końcowego testu w środowisku produkcyjnym.
