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
- `/ogloszenia`
- `/ogloszenia/{saleListing}`
- `/klub`
- `/kontakt`
- `/regulamin`
- `/rodo`
- `/logowanie`
- `/nie-pamietam-hasla` — neutralne żądanie linku resetu dla aktywnego konta
- `/wniosek-o-konto`
- `/ustaw-haslo/{token}` — standardowy formularz brokera haseł dla resetu i pierwszego hasła

`/informacje-klubowe` przekierowuje do `/klub`.

## Panel

Panel działa pod `/panel`.

Zasoby:
- aktualności,
- wydarzenia,
- wyniki,
- użytkownicy,
- funkcje klubowe,
- konkurencje,
- moje ogłoszenia,
- moderacja ogłoszeń i zgłoszeń,
- wnioski o konto istniejących członków (wyłącznie administrator).

## Wnioski o konto

Publiczny formularz nie rejestruje użytkownika automatycznie. Zapisuje osobny
`AccountRequest`, powiadamia aktywnych administratorów i zawsze zwraca neutralne
potwierdzenie, także przy duplikacie e-maila lub numeru licencji. Administrator
weryfikuje dane, zapisuje notatki wewnętrzne i odrzuca wniosek z powodem albo
zatwierdza go, tworząc aktywne konto z rolą `user`.

Zatwierdzenie używa transakcji, blokady rekordu oraz brokera resetu haseł Laravel.
Użytkownik nie otrzymuje hasła — dostaje jednorazowy link ważny 60 minut i ustawia
własne hasło. Dane licencyjne i członkowskie pozostają w audytowalnym wniosku;
do obecnego profilu użytkownika kopiowane są imię i nazwisko, e-mail oraz telefon.
Administrator może wysłać nowy link dla zatwierdzonego wniosku lub aktywnego
użytkownika. Operacja nie tworzy konta, unieważnia poprzedni token i zapisuje
ostatniego administratora oraz czas wysyłki.

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
