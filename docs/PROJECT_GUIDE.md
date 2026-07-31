# PROJECT_GUIDE.md

## Cel

KS Krokus to aplikacja klubu strzeleckiego z częścią publiczną i panelem administracyjnym. Ma wyglądać jak spójny produkt, a nie surowy panel CRUD.

## Stos

- Laravel 13.8+
- PHP 8.4
- PostgreSQL
- Blade
- własny CSS
- JavaScript
- Vite
- Railway
- PHPUnit 12
- Laravel Pint

## Repozytorium

- `rjachowicz/ks-krokus`
- branch bazowy: `v.0.4-refactor-calendar-and-admin-panel`
- katalog aplikacji: `../ks-krokus`

## Główne trasy publiczne

- `/`
- `/aktualnosci`
- `/aktualnosci/{post}`
- `/kalendarz`
- `/kalendarz/{sportEvent}`
- `/wyniki`
- `/wyniki/{sportEvent}`
- `/klub`
- `/kontakt`
- `/regulamin`
- `/rodo`
- `/logowanie`

`/informacje-klubowe` przekierowuje do `/klub`.

## Panel

Panel działa pod `/panel`.

Zasoby:
- aktualności,
- wydarzenia,
- wyniki,
- użytkownicy,
- funkcje klubowe,
- konkurencje.

Dostęp kontrolują `auth`, `active` i role.

## Wdrożenie

- Railway
- PostgreSQL
- migracje: `php artisan migrate --force`
- build: `npm run build`

## Formularz kontaktowy

Adres odbiorcy powinien być konfigurowalny:

```env
CONTACT_RECIPIENT_EMAIL=zarzad@ks-krokus.pl
```

Nie hardcoduj konfiguracji SMTP.

## Wymagania jakościowe

Każda zmiana musi uwzględniać:
- bezpieczeństwo,
- poprawność danych,
- responsywność,
- dostępność,
- UX,
- polskie komunikaty,
- zachowanie danych po błędzie,
- zgodność z PostgreSQL,
- wdrożenie na Railway,
- brak regresji.
