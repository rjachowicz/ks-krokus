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

Wspólny wymiar kontrolek interaktywnych wynosi 48 px. Pole, opis i błąd są częścią
jednej komórki siatki; dłuższy tekst pomocy nie może przesuwać początku sąsiedniego
labela. Formularze korzystają z `form-layout`, `form-grid`, `form-section` i
`form-actions`, bez ujemnych marginesów oraz lokalnych korekt wysokości.

## Checkboxy i switche

Muszą:
- pasować do light/dark,
- mieć czytelny stan zaznaczenia,
- mieć widoczny fokus,
- mieć duży obszar kliknięcia,
- działać klawiaturą,
- mieć prawidłową etykietę.

Checkbox i radio pozostają natywnymi inputami w DOM, ale używają
`appearance: none`, wymiaru 20 px i całego klikalnego labela. Checkbox jest
kwadratem z lekkim promieniem i czytelnym znakiem, radio równym okręgiem z kropką.
Oba warianty mają złote zaznaczenie oraz jawne stany `hover`, `focus-visible`,
`disabled` i `aria-invalid`. Kontrolkę wyrównuj do pierwszej linii długiej etykiety.

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

## Powiadomienia konta

- desktopowy header pokazuje jedną ikonę z licznikiem nieprzeczytanych;
- na mobile odnośnik i licznik są w menu konta;
- panel administracyjny używa tej samej zwartej kontrolki w topbarze;
- lista rozróżnia nowe i przeczytane kolorem, obramowaniem oraz tekstem statusu;
- pojedyncze i zbiorcze oznaczanie jako przeczytane jest dostępne bez JavaScriptu;
- pusta lista używa zwartego, neutralnego `empty-state`, a nie komunikatu błędu.

## Responsywność

Testuj:
- 320,
- 375,
- 480,
- 768,
- 1024,
- 1280,
- 1366,
- 1440,
- 1920 px.

Na telefonie:
- formularze jedna kolumna,
- grupy przycisków pionowo,
- tabele ze scrollem lub kartami,
- sidebar nie nachodzi,
- brak poziomego scrolla strony.

Audyt obejmuje oba motywy, otwarte menu, tabele, galerie, sidebary, sticky akcje,
stopkę i centrum powiadomień. Fizyczny test NVDA/VoiceOver pozostaje osobną kontrolą
wdrożeniową; automatyczny audyt DOM i headless go nie zastępuje.
