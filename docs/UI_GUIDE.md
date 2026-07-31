# UI_GUIDE.md

## Kierunek wizualny

Interfejs ma być nowoczesny, techniczny, sportowy, czytelny i spójny.

## Formularze

Każde pole:
- etykieta,
- opis, gdy potrzebny,
- spójna wysokość,
- hover,
- focus-visible,
- disabled,
- readonly,
- invalid,
- komunikat błędu.

Błędne pole:
- czerwone obramowanie,
- ring,
- `aria-invalid="true"`,
- `aria-describedby`,
- tekst błędu.

## Checkboxy i switche

Muszą:
- pasować do light/dark,
- mieć czytelny stan zaznaczenia,
- mieć widoczny fokus,
- mieć duży obszar kliknięcia,
- działać klawiaturą,
- mieć prawidłową etykietę.

## Selecty

Preferuj ostylowany natywny `select`. Własny komponent tylko przy zachowaniu pełnej dostępności.

## Data i czas

Pola `date`, `datetime-local`, `time` muszą być spójne z motywem. Sprawdź ikonę kalendarza, kontrast, format i walidację relacji dat.

## Upload

Komponent powinien pokazywać:
- formaty,
- limit rozmiaru,
- nazwę pliku,
- rozmiar,
- podgląd obrazu,
- możliwość usunięcia,
- stan błędu.

Drag and drop jest dodatkiem. Zwykły wybór pliku musi działać.

## Light/dark

Przełącznik w topbarze panelu.

Preferowany mechanizm:

```html
<html data-theme="dark">
```

Kolejność:
1. `localStorage`,
2. `prefers-color-scheme`,
3. domyślny motyw.

Ogranicz migotanie przy ładowaniu.

## Toasty

Toast może informować o zapisie, aktualizacji, usunięciu, publikacji i błędzie ogólnym. Nie zastępuje błędów pod polami.

## Responsywność

Testuj:
- 320,
- 375,
- 768,
- 1024,
- 1366,
- 1920 px.

Na telefonie:
- formularze jedna kolumna,
- grupy przycisków pionowo,
- tabele ze scrollem lub kartami,
- sidebar nie nachodzi,
- brak poziomego scrolla strony.
