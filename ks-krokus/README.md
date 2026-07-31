# KS Krokus

Aplikacja Klubu Strzeleckiego Krokus LOK w Nowym Sączu: publiczna strona
klubu, kalendarz, wyniki oraz panel administracyjny.

## Wymagania

- PHP 8.4,
- rozszerzenia PHP `fileinfo`, `PDO`, `pdo_pgsql` oraz `gd` do uruchamiania testów,
- Composer 2,
- Node.js i npm,
- PostgreSQL.

## Uruchomienie lokalne

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm ci
npm run build
```

Uzupełnij połączenie PostgreSQL i konfigurację poczty w `.env`. Do pracy
deweloperskiej użyj `composer dev`; polecenie uruchamia serwer PHP z limitami
uploadu zgodnymi z aplikacją, podgląd logów i Vite (8 MB na poziomie PHP, 6 MB
na poziomie walidacji). Aplikacja nie używa obecnie zadań asynchronicznych, więc
nie wymaga lokalnego ani produkcyjnego workera kolejki.

## Jakość

```bash
composer test
vendor/bin/pint --test
npm run build
composer audit --locked
npm audit --audit-level=moderate
```

## Wdrożenie

Instrukcja produkcyjna, konfiguracja wolumenu na zdjęcia i lista zmiennych
środowiskowych znajdują się w `../docs/DEPLOYMENT.md`.
