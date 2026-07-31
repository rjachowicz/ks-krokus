# CONTACT_FORM_SPEC.md

## Cel

Na `/kontakt` ma być formularz wysyłający do adresu konfiguracyjnego, domyślnie `zarzad@ks-krokus.pl`.

## Pola

- imię i nazwisko,
- e-mail,
- telefon opcjonalny,
- temat,
- wiadomość,
- honeypot.

## Walidacja

- imię i nazwisko: required|string|max:150,
- e-mail: required|email|max:254,
- telefon: nullable|string|max:30,
- temat: required|string|max:200,
- wiadomość: required|string|min:10|max:5000,
- honeypot: ma być pusty.

## Bezpieczeństwo

- CSRF,
- rate limiting,
- honeypot,
- brak ujawniania wyjątków SMTP,
- brak przyjmowania HTML jako treści,
- logowanie błędów po stronie serwera.

## UX

Sukces:

`Dziękujemy. Wiadomość została wysłana.`

Błąd:

`Nie udało się wysłać wiadomości. Spróbuj ponownie później.`

## Implementacja

- `ContactMessageRequest`,
- POST `/kontakt`,
- `ContactMessageMail`,
- widok maila,
- konfiguracja odbiorcy,
- testy `Mail::fake()`.

## Testy

- poprawne wysłanie,
- wymagane pola,
- błędny e-mail,
- długości,
- honeypot,
- rate limit,
- odbiorca z konfiguracji,
- brak wysłania przy błędach.
