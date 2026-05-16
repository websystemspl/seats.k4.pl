# seats.k4.pl

Aplikacja webowa firmy **Web Systems** dostępna pod adresem **seats.k4.pl**, służąca do zarządzania obecności i urlopów pracowników.

## Główne funkcje

- **Kalendarz obecności** — interaktywny widok miesięczny, zaznaczanie obecności pracowników na poszczególne dni, obsługa gestów (swipe) na mobile
- **Moduł urlopowy (BETA)** — kompleksowe zarządzanie urlopami wypoczynkowymi:
  - Składanie wniosków urlopowych z automatycznym wyliczaniem dni roboczych
  - Generowanie kart urlopowych w PDF
  - Automatyczne obliczanie stażu pracy (z uwzględnieniem wykształcenia wg KP art. 155 §1)
  - Wyliczanie wymiaru urlopu (20 lub 26 dni w zależności od stażu)
  - Śledzenie urlopu zaległego z poprzednich lat
  - Odbiór dni wolnych za święta przypadające w sobotę (KP art. 130 §2)
  - Kalendarz roczny z oznaczeniem urlopów, świąt i dni odebranych
  - Panel admina: zbiorczy widok urlopów wszystkich pracowników, filtrowanie, tabela uprawnień
- **Synchronizacja świąt** — pobieranie polskich dni wolnych z API Nager.at, zliczanie świąt w sobotę
- **Zarządzanie pracownikami** — dane stażu pracy, wykształcenie, upload dokumentów (dyplomy), logowanie zmian, aktywacja/dezaktywacja, sortowanie drag & drop
- **Autoryzacja** — logowanie, rejestracja, reset hasła, role (admin / użytkownik)

## Stack technologiczny

| Warstwa | Technologia | Wersja |
|---------|------------|--------|
| Backend | Laravel | 12.x |
| PHP | PHP | 8.4 |
| Frontend | Vue.js | 3.x |
| CSS | Tailwind CSS | 3.x |
| Baza danych | MySQL / MariaDB | 5.7+ |
| Auth | Laravel Sanctum | 4.x |
| PDF | barryvdh/laravel-dompdf | 3.x |
| Build | Vite | 6.x |

## Dokumentacja

Szczegółowa dokumentacja techniczna w katalogu [dokumentacja/](dokumentacja/):

- [Architektura](dokumentacja/architektura.md) — struktura projektu, modele, kontrolery
- [Baza danych](dokumentacja/baza-danych.md) — schemat tabel, migracje
- [API i routing](dokumentacja/api-routing.md) — endpointy, metody HTTP
- [Frontend](dokumentacja/frontend.md) — komponenty Vue, szablony Blade, stylowanie
- [Konfiguracja](dokumentacja/konfiguracja.md) — zmienne środowiskowe, ustawienia

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
