# TODO.md

## P0 — baza danych

- [x] Przełączyć środowisko lokalne z SQLite na PostgreSQL.
- [x] Skonfigurować osobną bazę PostgreSQL dla testów.
- [x] Uruchomić wszystkie migracje na lokalnym PostgreSQL.
- [x] Przenieść istniejące dane aplikacyjne i zsynchronizować sekwencje.
- [x] Ograniczyć konfigurację runtime wyłącznie do PostgreSQL.
- [x] Usunąć lokalną kopię SQLite po ręcznym potwierdzeniu działania.

## P0 — błędy i bezpieczeństwo

- [ ] Naprawić `validation.uploaded`.
- [ ] Sprawdzić limity PHP i Railway.
- [ ] Sprawdzić `multipart/form-data`.
- [ ] Sprawdzić zapis, podmianę i usuwanie plików.
- [ ] Upewnić się, że błędny upload nie usuwa poprzedniego pliku.
- [ ] Dodać polskie tłumaczenia.
- [ ] Dodać `messages()` i `attributes()`.
- [ ] Zweryfikować autoryzację.
- [ ] Dodać testy uploadów i uprawnień.

## P1 — formularze

- [ ] Audyt aktualności.
- [ ] Audyt wydarzenia.
- [ ] Audyt wyniku.
- [ ] Audyt użytkownika.
- [ ] Audyt funkcji klubowej.
- [ ] Audyt konkurencji.
- [ ] Ujednolicić błędy.
- [ ] Ujednolicić checkboxy.
- [ ] Ujednolicić selecty.
- [ ] Ujednolicić daty i czas.
- [ ] Dodać autocomplete.
- [ ] Dodać ARIA.
- [ ] Sprawdzić `old()`.
- [ ] Dodać wymagania uploadu i podgląd.

## P1 — motyw

- [ ] Przełącznik light/dark.
- [ ] `localStorage`.
- [ ] `prefers-color-scheme`.
- [ ] Brak migotania.
- [ ] Test wszystkich ekranów w obu motywach.

## P1 — kontakt

- [ ] POST `/kontakt`.
- [ ] `ContactMessageRequest`.
- [ ] Mailable.
- [ ] Konfiguracja odbiorcy.
- [ ] Rate limiting.
- [ ] Honeypot.
- [ ] Komunikaty.
- [ ] Testy Mail fake.

## P2 — kalendarz

- [ ] Walidować filtry enumami.
- [ ] Sprawdzić błędny miesiąc/rok.
- [ ] Obsłużyć wydarzenia wielodniowe.
- [ ] Sprawdzić mobile.
- [ ] Testy zakresów i filtrów.
- [ ] Sprawdzić N+1.

## P2 — UX i dostępność

- [ ] Kolejność TAB.
- [ ] Focus-visible.
- [ ] Kontrast.
- [ ] Czytnik ekranu.
- [ ] Puste stany.
- [ ] Komunikaty sukcesu.
- [ ] Dostępne toasty.
- [ ] Potwierdzenia usuwania.

## P2 — responsywność

- [ ] 320 px.
- [ ] 375 px.
- [ ] 768 px.
- [ ] 1024 px.
- [ ] 1366 px.
- [ ] 1920 px.
- [ ] Brak poziomego scrolla.
- [ ] Tabele.
- [ ] Długie formularze.
- [ ] Sidebar i topbar.

## P3 — porządki

- [ ] Martwy CSS.
- [ ] Martwy JS.
- [ ] Nieużywane importy.
- [ ] Duplikacja komponentów.
- [ ] Testy regresyjne.
- [ ] Dokumentacja wdrożenia.
