# Konfiguracja

## Wymagania

- PHP 7.3+ lub 8.0+
- MySQL 5.7+ (lub PostgreSQL/SQLite)
- Node.js + npm/yarn
- Composer

## Zmienne środowiskowe (.env)

### Aplikacja
- `APP_NAME` - nazwa aplikacji
- `APP_ENV` - środowisko (local/production)
- `APP_KEY` - klucz szyfrowania (generowany przez `php artisan key:generate`)
- `APP_DEBUG` - tryb debugowania
- `APP_URL` - URL aplikacji (https://seats.k4.pl)

### Baza danych
- `DB_CONNECTION` - sterownik (mysql)
- `DB_HOST` - host bazy (127.0.0.1)
- `DB_PORT` - port (3306)
- `DB_DATABASE` - nazwa bazy
- `DB_USERNAME` - użytkownik
- `DB_PASSWORD` - hasło

### Sesja
- Driver: file (domyślnie)
- Lifetime: 120 minut
- Szyfrowanie cookies

### Mail (do resetowania haseł)
- `MAIL_MAILER` - smtp
- `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`

### Cache
- Driver: file (domyślnie)
- Święta cachowane w tabeli `holiday_years`

## Zależności PHP (composer.json)

| Paczka | Wersja | Opis |
|--------|--------|------|
| laravel/framework | ^8.75 | Framework |
| laravel/sanctum | ^2.11 | Tokeny API |
| laravel/ui | ^3.4 | Scaffolding auth |
| barryvdh/laravel-dompdf | ^2.0 | Generowanie PDF |
| guzzlehttp/guzzle | ^7.0.1 | HTTP client |
| fruitcake/laravel-cors | ^2.0 | Obsługa CORS |

## Zależności JS (package.json)

| Paczka | Wersja | Opis |
|--------|--------|------|
| vue | ^3.2.36 | Framework frontend |
| vuedraggable | ^4.1.0 | Drag & drop |
| axios | ^1.8 | HTTP client |
| moment | ^2.29.4 | Formatowanie dat |
| tailwindcss | ^3.1.8 | CSS utility |
| tw-elements | 1.0.0-alpha12 | Komponenty UI |
| vue3-touch-events | ^4.1.0 | Gesty mobilne |
| @formkit/auto-animate | ^0.9.0 | Animacje list |
| laravel-mix | ^6.0.6 | Build tool |

## Seeder

`DatabaseSeeder` tworzy 5 testowych użytkowników przez `UserFactory`.

## Komendy

```bash
composer install          # Instalacja zależności PHP
npm install               # Instalacja zależności JS
php artisan migrate       # Migracja bazy
php artisan db:seed       # Seed testowych danych
php artisan serve         # Serwer deweloperski
npm run dev               # Build frontend
npm run watch             # Watch mode
npm run prod              # Build produkcyjny
```
