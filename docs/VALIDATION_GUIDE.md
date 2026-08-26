# VALIDATION_GUIDE.md

## Standard

Każdy Form Request powinien zawierać:

```php
public function rules(): array
{
    return [];
}

public function messages(): array
{
    return [];
}

public function attributes(): array
{
    return [];
}
```

## Lokalizacja

Sprawdź:
- `config/app.php`,
- `.env.example`,
- `resources/lang/pl/validation.php`.

Rekomendowane:

```env
APP_LOCALE=pl
APP_FALLBACK_LOCALE=pl
APP_FAKER_LOCALE=pl_PL
```

## Limity żądań

Publiczne operacje zapisujące mają nazwane limitery, aby limit i klucz były jawne:

- logowanie: 6/min na IP,
- żądanie resetu: 5/min na IP,
- ustawienie hasła: 6/min na IP,
- kontakt: 5/min na IP,
- wniosek o konto i zgłoszenie ogłoszenia: 3/h na IP,
- zmiana stanu powiadomień: 30/min na identyfikator zalogowanego użytkownika.

Nie opieraj limitera na nagłówkach proxy bez poprawnej konfiguracji
`TRUSTED_PROXIES`. Odpowiedź formularza resetu i duplikatu wniosku pozostaje
neutralna, aby nie ujawniać istnienia konta lub rekordu.

## Niedopuszczalne

- `validation.uploaded`
- `validation.required`
- `validation.max.file`
- `validation.image`
- komunikaty angielskie
- techniczne nazwy pól

## Błąd uploadu

`validation.uploaded` może wynikać z:
- `upload_max_filesize`,
- `post_max_size`,
- `UPLOAD_ERR_*`,
- złego `multipart/form-data`,
- limitu hostingu,
- problemu dysku.

Sprawdź:
1. `enctype="multipart/form-data"`,
2. limity PHP,
3. Railway,
4. logi,
5. Form Request,
6. `filesystems.php`,
7. dysk public,
8. storage link,
9. prawa zapisu.

Na Railway z Railpack/FrankenPHP limity ustawiaj w głównym `php.ini` katalogu
aplikacji. `public/.user.ini` działa tylko w trybie CGI/FastCGI i nie może być
jedyną konfiguracją produkcyjną. Koperta PHP musi być większa od łącznego
limitu plików Laravela, aby żądanie dotarło do Form Requestu.

Przykład:

```php
'image.uploaded' => 'Nie udało się przesłać zdjęcia. Sprawdź rozmiar pliku i spróbuj ponownie.',
'image.image' => 'Wybrany plik musi być obrazem.',
'image.mimes' => 'Zdjęcie musi być w formacie JPG, JPEG, PNG lub WEBP.',
'image.max' => 'Zdjęcie może mieć maksymalnie :max KB.',
```

## Relacje między polami

Sprawdź:
- koniec nie wcześniej niż początek,
- status publikacji,
- boolean checkboxów,
- nullable,
- `Rule::enum`,
- istnienie relacji,
- zakres liczb,
- MIME i rozszerzenia.

## Operacje na własnym koncie

Zmianę profilu, e-maila i hasła obsługuj osobnymi Form Requestami. Dzięki temu pola
roli, aktywności, uprawnień i danych weryfikacyjnych nie mogą trafić do masowego
przypisania nawet po spreparowaniu żądania.

Zmiana e-maila i hasła wymaga reguły `current_password:web` z jawnym polskim
komunikatem. Adres e-mail normalizuj przed walidacją unikalności, a konflikt opisuj
neutralnie, bez wskazania konta, do którego adres należy. Nowe hasło korzysta z
`Password::min(12)->letters()->mixedCase()->numbers()` i `confirmed`, zgodnie z
formularzem pierwszego hasła.

Checkboxy zgód normalizuj przez `$this->boolean()` w `prepareForValidation()`, a
Form Request powinien przyjmować tylko jawnie dozwolone pola. Danych członkowskich
użytkownik nie przesyła w formularzu własnego profilu; ich administracyjna edycja
ma oddzielny request, enum statusu i serwerowy audyt weryfikującego.

Stan powiadomienia pobieraj wyłącznie przez relację `notifications()` aktualnie
zalogowanego użytkownika. Identyfikator przesłany w URL nie jest samodzielną
podstawą autoryzacji; cudze powiadomienie ma zwrócić 404 i pozostać bez zmian.

## Przypomnienia wydarzeń

Globalną zgodę obsługuje osobny `UpdateEventEmailNotificationsRequest`.
`event_email_notifications_enabled` jest normalizowane przez `$this->boolean()`.
Przy wartości `true` pole `event_notifications_current_password` jest wymagane i
korzysta z `current_password:web`; przy wyłączeniu hasło nie jest wymagane.

Zapis z wydarzenia obsługuje `SubscribeEventReminderRequest`, który zawsze wymaga
`event_reminder_current_password` z `current_password:web`. Oba pola haseł są na
liście `dontFlash`; nie używaj ich w tooltipach, komunikatach sukcesu ani logach.
Po błędzie formularz z modala przekierowuje do pełnego widoku wydarzenia, gdzie
błąd pozostaje powiązany z polem i dostępny dla czytnika ekranu.

Warunków wydarzenia nie wolno opierać wyłącznie na Blade. Serwis domenowy sprawdza
je przed transakcją i ponownie po `lockForUpdate`: `is_public = true`, status
`published`, `email_reminders_enabled = true` oraz
`start_at > now() + 24 godziny`. Równość oznacza zamknięty zapis. Endpoint
anulowania nie przyjmuje identyfikatora subskrypcji i usuwa wyłącznie rekord
wynikający z użytkownika sesji oraz wydarzenia w trasie.

## Wyniki zawodów

- Publiczne `q` ma typ string i maksymalnie 100 znaków; wyszukiwanie odbywa się
  serwerowo przez PostgreSQL `ILIKE`, nie przez filtrowanie DOM.
- Filtry panelu waliduje `EventResultFilterRequest`: identyfikatory wydarzenia i
  `event_competitions` muszą istnieć, a status używa `Rule::enum(ResultStatus::class)`.
- Autocomplete wymaga 2–100 znaków, uprawnienia zarządzania treścią i aktywnego
  konta. Endpoint jest limitowany i nie może zwracać prywatnych pól użytkownika.
- `event_competition_id` nowego wyniku musi wskazywać nieusunięte zawody typu
  `competition` ze statusem `published`. Warunek trzeba powtórzyć po blokadzie
  rekordu; wyjątek dla bieżącej relacji służy tylko edycji istniejącej historii.
- Archiwalny wynik odrzuca update oraz delete po stronie Policy i ponownie pod
  blokadą transakcyjną. Ukrycie przycisku w Blade nie jest zabezpieczeniem.
- Dla wybranego użytkownika backend nadpisuje przesłane imię, klub i kategorię
  bieżącymi wartościami snapshotu. Ponowna edycja tego samego powiązania zachowuje
  zapisany snapshot zamiast pobierać zmieniony profil.
- `age_category` profilu używa `Rule::enum(MemberAgeCategory::class)` i pozostaje
  nullable. Nowa kategoria wyniku korzysta z tego samego słownika.
- `classification` przyjmuje kontrolowane wartości `IpscDivision`. Form Request
  może zachować dokładnie nieznaną wartość istniejącego wyniku, ale nie pozwala
  utworzyć nowego dowolnego stringa. Model nie castuje historycznej kolumny do
  enumu, dzięki czemu starszy rekord nie powoduje błędu odczytu.

## Blade

Dodawaj `aria-invalid`, `aria-describedby` i komunikat pod polem. Na górze może być podsumowanie, ale nie zamiast komunikatów przy polach.

Pamiętaj, że `old()` może zawierać złośliwie przesłaną tablicę także dla pola
tekstowego. Błędne dane należy bezpiecznie przygotować do ponownego wyświetlenia,
aby formularz zwrócił błąd walidacji zamiast błędu renderowania 500.

Hasła, tokeny, daty urodzenia, pełne numery dokumentów, dane wniosków, notatki
wewnętrzne i treści prywatnych formularzy nie mogą trafiać do komunikatu ani
kontekstu logu. Globalny procesor usuwa znane klucze z kontekstu zagnieżdżonego,
ale nie zwalnia to kodu z używania technicznych identyfikatorów i klasy wyjątku
zamiast danych wejściowych.

## Zakres audytu

- aktualności,
- wydarzenia,
- wyniki,
- użytkownicy,
- funkcje klubowe,
- konkurencje,
- logowanie,
- kontakt,
- zmiana hasła, jeśli istnieje.
