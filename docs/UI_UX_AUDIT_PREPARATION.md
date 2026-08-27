# Przygotowanie audytu UI/UX

Stan wejściowy dla brancha `refactor/ui-ux-audit`, utworzonego 2026-08-27 z
`main`. Dokument jest inwentaryzacją; nie opisuje zmian funkcjonalnych.

## Istniejące testy UI

- `tests/Feature/InterfaceAuditTest.php` — struktura głównych ekranów, dostępność
  tabel i formularzy, stopka, logowanie, filtry, headery, ikony i inicjalizacja
  croppera.
- `tests/Feature/HeaderAccountNavigationTest.php` i
  `AdminAccountNavigationTest.php` — nawigacja konta, role, menu oraz wylogowanie.
- `tests/Feature/PublicContentTest.php` i `CalendarEventModalTest.php` — publiczne
  treści, kalendarz, filtry, wydarzenia wielodniowe i fallback modala.
- `tests/Feature/AccountManagementTest.php` — renderowanie i operacje „Mojego
  konta” oraz danych członkowskich.
- `tests/Feature/SaleListingModuleTest.php` — publiczne i panelowe widoki
  ogłoszeń, formularz, galeria, filtry i dostępne akcje statusów.
- `tests/Feature/ResultsModuleTest.php` — publiczne i panelowe wyniki, filtry,
  autocomplete oraz widok archiwalny.
- `tests/Feature/AdminCrudAuditTest.php` — przepływy CRUD aktualności, wydarzeń,
  wyników, użytkowników, funkcji klubowych i konkurencji.
- `tests/Feature/MediaManagementTest.php` — render mediów, dane kadrowania,
  miniatury i ponowne kadrowanie.

Są to testy PHPUnit/Feature renderowanego HTML i backendu. Repozytorium nie ma
obecnie zależności, konfiguracji ani skryptu dla Laravel Dusk, Playwright,
Cypress, Selenium lub Puppeteer.

## Brakujące testy browser/E2E

- realne interakcje klawiaturą i zarządzanie fokusem w menu headera, dropdownie
  konta oraz dialogach potwierdzenia, wydarzenia i croppera;
- obsługa croppera, uploadu/drag and drop, galerii ogłoszeń i lightboxa na
  rzeczywistym DOM;
- zachowanie autocomplete wyników, stanów loading/error/retry oraz blokady
  podwójnego wysłania formularza;
- trwały audyt viewportów 320–1920 px w light/dark, poziomego overflow i
  podstawowych regresji wizualnych;
- scenariusze bez JavaScriptu oraz fizyczna kontrola NVDA/VoiceOver pozostają
  oddzielnymi kontrolami dostępności.

## Mapa plików UI

- Header: `resources/views/partials/header.blade.php`,
  `resources/views/layouts/admin.blade.php`, `resources/css/layout/header.css`,
  `resources/css/pages/admin.css`, `resources/js/modules/mobile-menu.js`,
  `resources/js/modules/sticky-header.js` i `resources/js/modules/theme.js`.
- Cropper: `resources/views/components/media-cropper-dialog.blade.php`,
  `resources/views/components/media-crop-fields.blade.php`,
  `resources/js/modules/media-cropper.js` oraz sekcja `.media-cropper` w
  `resources/css/components/forms.css`.
- Aktualności: `resources/views/news/*`, `resources/views/admin/posts/*`,
  `resources/css/pages/public-content.css` i `resources/js/modules/rich-text.js`.
- Kalendarz: `resources/views/calendar/*`, `resources/css/pages/calendar.css`,
  `resources/js/modules/event-dialog.js` i `resources/js/modules/event-reminders.js`.
- Konto: `resources/views/account/show.blade.php` i
  `resources/css/pages/account.css`; wariant panelowy osadza ten sam widok w
  `resources/views/layouts/admin.blade.php`.
- Ogłoszenia: `resources/views/listings/*`, `resources/views/my-listings/*`,
  `resources/views/admin/sale-listings/*`, `resources/css/pages/listings.css` i
  `resources/js/modules/listing-images.js`.
- Wyniki: `resources/views/results/*`, `resources/views/admin/results/*` i
  `resources/js/modules/result-user-combobox.js`; wspólne style panelowe są w
  `resources/css/pages/admin.css`.
- Funkcje klubowe: `resources/views/admin/positions/*`; wspólne style formularzy
  i panelu są w `resources/css/components/forms.css` oraz
  `resources/css/pages/admin.css`.

Punktem wejścia assetów są `resources/css/app.css` i `resources/js/app.js`.
