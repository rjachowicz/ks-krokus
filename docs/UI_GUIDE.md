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

Formularze filtrowania korzystają ze wspólnego `filter-form`, `filter-form__row`
i `filter-form__actions`. Etykieta, kontrolka oraz blok pomocy/błędu zajmują osobne
wspólne tory siatki. Akcje są umieszczane w torze kontrolki, dlatego nie wolno
wyrównywać ich lokalnym `margin-top`, a pojawienie się komunikatu nie zmienia
położenia inputów, selectów ani przycisków w tym samym rzędzie. Na szerokości
mobilnej układ przechodzi w jedną kolumnę, a grupa akcji zajmuje pełną szerokość.

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

Publiczny header i topbar panelu korzystają z półprzezroczystego
`--header-bg`, rozmycia z prefiksem WebKit oraz nieprzezroczystego
`--header-bg-fallback` wewnątrz `@supports not`. Warstwa musi mieć jawny
`z-index` i `isolation`, a po przewinięciu czytelne obramowanie. Nie ustawiaj
całkowicie przezroczystego tła, bo sam `backdrop-filter` nie gwarantuje kontrastu.

## Ikony

Ikony interfejsu renderuje wspólny komponent `<x-icon>` z jednym zestawem SVG,
`aria-hidden="true"`, `currentColor`, rozmiarem 20 px i stroke 1.8. Chevron jest
jedynym mniejszym wariantem. Header publiczny i panel nie duplikują ikon motywu,
powiadomień, konta, chevrona ani wylogowania; nie stosuj tekstowych pseudoikon.

## Menu konta w panelu

Prawa strona topbara zawiera przełącznik motywu i jeden trigger konta. Dropdown
pokazuje imię i nazwisko, rolę, konto, powiadomienia z badge, przejście do strony
publicznej w tej samej karcie oraz wylogowanie. Trigger ma `aria-expanded` i
`aria-controls`; menu obsługuje Enter, Space, ArrowDown, Escape z powrotem fokusu,
klik poza obszarem oraz `hidden`/`inert`. Szerokość i maksymalna wysokość panelu
są ograniczone viewportem, również na telefonie.

## Toasty

Toast może informować o zapisie, aktualizacji, usunięciu, publikacji i błędzie ogólnym. Nie zastępuje błędów pod polami.

## Powiadomienia konta

- desktopowy header pokazuje jedną ikonę z licznikiem nieprzeczytanych;
- na mobile odnośnik i licznik są w menu konta;
- panel administracyjny używa tej samej zwartej kontrolki w topbarze;
- lista rozróżnia nowe i przeczytane kolorem, obramowaniem oraz tekstem statusu;
- pojedyncze i zbiorcze oznaczanie jako przeczytane jest dostępne bez JavaScriptu;
- pusta lista używa zwartego, neutralnego `empty-state`, a nie komunikatu błędu.

## Modal wydarzenia

- miesięczny kalendarz używa natywnego `<dialog>` i zachowuje pełny adres w
  `href`, dlatego Enter działa standardowo, Space jest dodane jako równoważna
  aktywacja, a menu kontekstowe i brak JavaScriptu nadal prowadzą do pełnej strony;
- dialog ma stałe `aria-labelledby` i `aria-describedby`, jawny przycisk zamknięcia,
  natywną obsługę Escape oraz izolację fokusu przez `showModal()`;
- po zamknięciu fokus wraca do dokładnie tego wystąpienia wydarzenia, które otworzyło
  modal; zmodyfikowane kliknięcia nie są przechwytywane;
- podczas pobierania treść ma `aria-busy`, komunikat i spinner, a błąd ma `role="alert"`,
  polski opis i dostępną akcję ponowienia;
- treść ma własny scroll, szerokość i wysokość ograniczone viewportem, kontrolkę
  zamknięcia 44 px oraz jednokolumnowy układ i pełnoszerokie akcje na telefonie;
- animacja wejścia i spinner są wyłączane przez `prefers-reduced-motion: reduce`;
- sekcja przypomnienia pokazuje wyłącznie stan bieżącego użytkownika: logowanie,
  zamknięty termin, ustawienie albo anulowanie; nie pokazuje liczby ani listy osób;
- ustawienie otwiera osobny natywny dialog z etykietą aktualnego hasła, opisem
  celu potwierdzenia, błędem przy polu i przywróceniem fokusu; błąd serwerowy z
  modala przechodzi do pełnego widoku wydarzenia i ponownie otwiera dialog;
- dokładnie 24 godziny przed rozpoczęciem przycisk ustawienia znika, natomiast
  anulowanie istniejącej subskrypcji pozostaje dostępne.

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
