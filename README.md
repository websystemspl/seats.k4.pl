# seats.k4.pl

Aplikacja webowa firmy **Web Systems** dostępna pod adresem **seats.k4.pl**, służąca do zarządzania i raportowania miejsc w biurze dla pracowników.

## Główne funkcje

- **Kalendarz obecności** - interaktywny widok miesięczny, zaznaczanie obecności pracowników na poszczególne dni, obsługa gestów (swipe) na mobile
- **Zarządzanie urlopami** - składanie wniosków urlopowych, automatyczne wyliczanie dni roboczych (z uwzględnieniem weekendów i świąt), generowanie kart urlopowych w PDF
- **Synchronizacja świąt** - pobieranie polskich dni wolnych z API Nager.at, cache w bazie danych per rok
- **Panel administracyjny** - zarządzanie pracownikami (dodawanie, edycja, dezaktywacja, usuwanie), sortowanie drag & drop, synchronizacja świąt
- **Autoryzacja** - logowanie, rejestracja, reset hasła, role (admin / użytkownik)

## Stack technologiczny

| Warstwa | Technologia | Wersja |
|---------|------------|--------|
| Backend | Laravel | 8.x |
| PHP | PHP | 7.3+ / 8.0+ |
| Frontend | Vue.js | 3.2 |
| CSS | Tailwind CSS | 3.1 |
| Baza danych | MySQL | 5.7+ |
| Auth | Laravel Sanctum | 2.11 |
| PDF | barryvdh/laravel-dompdf | 2.0 |
| Build | Laravel Mix | 6.0 |

## Dokumentacja

Szczegółowa dokumentacja znajduje się w katalogu [dokumentacja/](dokumentacja/):

- [Architektura](dokumentacja/architektura.md) - struktura projektu, modele, kontrolery
- [Baza danych](dokumentacja/baza-danych.md) - schemat tabel, migracje
- [API i routing](dokumentacja/api-routing.md) - endpointy, metody HTTP
- [Frontend](dokumentacja/frontend.md) - komponenty Vue, szablony Blade, stylowanie
- [Konfiguracja](dokumentacja/konfiguracja.md) - zmienne środowiskowe, ustawienia

## Uruchomienie lokalne

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve
```

## Repozytorium

https://github.com/websystemspl/seats.k4.pl
