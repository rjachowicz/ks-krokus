# TODO.md

## P0 — baza danych

- [x] Przełączyć środowisko lokalne z SQLite na PostgreSQL.
- [x] Skonfigurować osobną bazę PostgreSQL dla testów.
- [x] Uruchomić migracje i ograniczyć runtime wyłącznie do PostgreSQL.
- [x] Dodać indeks zakresowy dla wydarzeń wielodniowych.
- [x] Odtworzyć lokalne dane startowe bez wpisów wyników.

## P0 — uploady i wdrożenie

- [x] Usunąć przyczynę `validation.uploaded`.
- [x] Uzgodnić limity Laravela i PHP.
- [x] Zweryfikować `multipart/form-data`, nazwy pól i dysk publiczny.
- [x] Przetestować zapis, podmianę, usuwanie i rollback plików.
- [x] Zachować poprzednie zdjęcie przy błędzie uploadu.
- [x] Dodać polskie komunikaty i testy regresyjne.
- [x] Udokumentować wdrożenie Railway.
- [ ] Podpiąć na Railway wolumen do `/app/storage/app/public`.
- [ ] Wykonać produkcyjny smoke test zdjęcia 1 MB, 3 MB, 6 MB i ponad 6 MB.

## P1 — formularze i panel

- [x] Przeprowadzić audyt wszystkich zasobów panelu.
- [x] Ujednolicić błędy, checkboxy, selecty, daty i przyciski.
- [x] Uzupełnić `old()`, `attributes()`, `messages()` i autocomplete.
- [x] Dodać serwerowe ARIA i podsumowanie błędów bez zależności od JavaScriptu.
- [x] Dodać wymagania uploadu, podgląd i usuwanie wybranych plików.
- [x] Zweryfikować autoryzację i ochronę ostatniego administratora.
- [x] Naprawić relację wyników z definicjami konkurencji i pokryć publiczne wyniki,
  CRUD panelu oraz dashboard testami regresyjnymi.
- [x] Uzależnić opis trenera od statusu trenera w interfejsie, walidacji i zapisie.

## P1 — motyw i kontakt

- [x] Przełącznik light/dark, `prefers-color-scheme` i brak migotania.
- [x] Bezpieczna obsługa niedostępnego `localStorage`.
- [x] POST `/kontakt`, Form Request, Mailable, rate limiting i honeypot.
- [x] Polskie komunikaty i testy `Mail::fake()`.
- [ ] Sprawdzić rzeczywiste SMTP w środowisku produkcyjnym.
- [ ] Wykonać ręczną kontrolę wszystkich ekranów w obu motywach.

## P2 — kalendarz, UX i dostępność

- [x] Walidować filtry enumami i zakres miesiąca/roku.
- [x] Obsłużyć wydarzenia wielodniowe bez duplikowania danych.
- [x] Sprawdzić N+1, mobile, puste stany, sukcesy, błędy i potwierdzenia.
- [x] Zapewnić focus-visible, etykiety, ARIA, skip link i obsługę klawiatury.
- [x] Dodać polskie strony błędów HTTP.
- [ ] Przeprowadzić końcowy test z NVDA/VoiceOver i audyt kontrastu narzędziem
  na środowisku wdrożeniowym.

## P2 — responsywność

- [x] Sprawdzić 320, 375, 768, 1024, 1366 i 1920 px.
- [x] Usunąć poziomy scroll części publicznej i panelu.
- [x] Sprawdzić tabele, formularze, sidebar, topbar, galerie i kalendarz.

## P3 — dalszy rozwój

- [x] Usunąć wykryty martwy CSS, przykładowy kod i puste zasoby.
- [x] Zweryfikować importy JS, komponenty Blade i zależności.
- [x] Dodać testy regresyjne i dokumentację wdrożenia.
- [ ] Dodać przywracanie kosza lub cykliczne trwałe czyszczenie zdjęć
  miękko usuniętych aktualności.
- [ ] Dostarczyć brakujący `public/files/Uchwala_kolekcjonerska.pdf`.
- [ ] Rozważyć `pg_trgm` i indeksy wyszukiwania po wzroście liczby rekordów.
- [ ] Dodać automatyczne skalowanie/kompresję zdjęć i generowanie miniatur.
- [ ] Skonfigurować monitoring błędów, alerty, backup PostgreSQL i test odtwarzania.
