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
- `POST/DELETE /kalendarz/{sportEvent}/przypomnienie` — prywatne ustawienie lub anulowanie przypomnienia
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
- `/moje-konto` — własny profil, bezpieczeństwo i podgląd danych członkowskich po zalogowaniu

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
- wnioski o konto istniejących członków (wyłącznie administrator),
- dane członkowskie powiązane 1:1 z użytkownikiem (wyłącznie administrator).

## Wnioski o konto

Publiczny formularz nie rejestruje użytkownika automatycznie. Zapisuje osobny
`AccountRequest`, powiadamia aktywnych administratorów i zawsze zwraca neutralne
potwierdzenie, także przy duplikacie e-maila lub numeru licencji. Administrator
weryfikuje dane, zapisuje notatki wewnętrzne i odrzuca wniosek z powodem albo
zatwierdza go, tworząc aktywne konto z rolą `user`.

Zatwierdzenie używa transakcji, blokady rekordu oraz brokera resetu haseł Laravel.
Użytkownik nie otrzymuje hasła — dostaje jednorazowy link ważny 60 minut i ustawia
własne hasło. Dane źródłowe pozostają w audytowalnym wniosku. Do użytkownika
kopiowane są imię i nazwisko, e-mail oraz telefon, a numery dokumentów, ważność
licencji, rok wstąpienia i dyscypliny trafiają do osobnego `MemberProfile`. Profil
powstaje w tej samej transakcji, jest oznaczany jako zweryfikowany i zapisuje
administratora oraz czas decyzji. Migracja tabeli uzupełnia profile także dla
wcześniej zatwierdzonych wniosków.
Administrator może wysłać nowy link dla zatwierdzonego wniosku lub aktywnego
użytkownika. Operacja nie tworzy konta, unieważnia poprzedni token i zapisuje
ostatniego administratora oraz czas wysyłki.

Dostęp kontrolują `auth`, `active` i role.

## Moje konto

Każdy aktywny zalogowany użytkownik ma własną sekcję `/moje-konto`, niezależną od
panelu administracyjnego. Może zmienić imię i nazwisko, telefon oraz istniejące
zgody publicznej prezentacji kontaktu. E-mail i hasło mają osobne formularze
wymagające aktualnego hasła. Rola, aktywność, funkcje klubowe, uprawnienia i dane
członkowskie nie są przyjmowane przez te endpointy.

Administrator edytuje `MemberProfile` z poziomu użytkownika. Moderator nie uzyskuje
tego uprawnienia automatycznie. Notatki wewnętrzne nadal są prowadzone w źródłowym
wniosku o konto. Zakres nie obejmuje pełnej kartoteki członków ani rozliczeń składek.

Osobny formularz pozwala włączyć zgodę „Chcę otrzymywać e-mailowe przypomnienia
o wybranych wydarzeniach”. Włączenie zawsze wymaga aktualnego hasła i zapisuje
czas potwierdzenia. Wyłączenie nie wymaga hasła, zeruje czas potwierdzenia oraz
usuwa wszystkie subskrypcje użytkownika, dzięki czemu zatrzymuje również
zakolejkowane, ale jeszcze niewysłane przypomnienie.

## Centrum powiadomień

Aktywny zalogowany użytkownik ma pod `/powiadomienia` paginowaną listę własnych
powiadomień. Może oznaczać je jako przeczytane, usuwać pojedynczo, usuwać wybrane
albo usunąć wszystkie. Zaznaczenie pojedynczych wpisów i „Zaznacz wszystkie
widoczne” działa również bez JavaScriptu; skrypt dodaje licznik i wygodną
synchronizację checkboxów. Wszystkie operacje są ograniczone relacją
`user->notifications()`, a grupowe usuwanie działa w transakcji.

Retencję bazodanowych powiadomień konfiguruje:

```env
NOTIFICATION_RETENTION_DAYS=7
```

Komenda `php artisan notifications:prune` usuwa partiami wpisy starsze niż próg
wyliczony z `created_at` i wypisuje wyłącznie statystyki. Wartość `0` świadomie
wyłącza retencję. Wartość ujemna lub niecałkowita kończy komendę błędem bez
usuwania danych. Scheduler uruchamia komendę codziennie o 02:45 w strefie
`Europe/Warsaw` z `withoutOverlapping()`.

## E-mailowe przypomnienia o wydarzeniach

Przypomnienie jest prywatnym ustawieniem konta, a nie zgłoszeniem uczestnictwa.
Aplikacja nie pokazuje publicznie użytkowników ani liczby subskrypcji. Zapis jest
dostępny tylko dla aktywnego użytkownika oraz wydarzenia publicznego,
opublikowanego, przyszłego, z włączonymi przypomnieniami i rozpoczynającego się
ściśle później niż za 24 godziny. Dokładnie przy `start_at - 24 godziny` zapis
jest już zamknięty.

Komenda `php artisan events:send-reminders` wybiera niewysłane subskrypcje w
oknie ±15 minut wokół 24 godzin do rozpoczęcia. Scheduler uruchamia ją co 5 minut
w `Europe/Warsaw` z `withoutOverlapping()`. E-mail jest kolejkowany przez istniejącą
konfigurację poczty i Resend. Wyłączenie zgody użytkownika albo przypomnień na
wydarzeniu usuwa subskrypcje; ponowne włączenie wymaga ponownego wyboru wydarzeń.

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
