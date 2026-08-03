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

## Blade

Dodawaj `aria-invalid`, `aria-describedby` i komunikat pod polem. Na górze może być podsumowanie, ale nie zamiast komunikatów przy polach.

Pamiętaj, że `old()` może zawierać złośliwie przesłaną tablicę także dla pola
tekstowego. Błędne dane należy bezpiecznie przygotować do ponownego wyświetlenia,
aby formularz zwrócił błąd walidacji zamiast błędu renderowania 500.

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
