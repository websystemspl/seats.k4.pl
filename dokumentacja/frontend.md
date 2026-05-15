# Frontend

## Stack

- Vue 3.2 (Composition API nie jest używane - Options API)
- Tailwind CSS 3.1 z paletą K4
- Laravel Mix 6 (webpack)
- Axios do zapytań HTTP
- Moment.js do formatowania dat
- vuedraggable do drag & drop
- vue3-touch-events do gestów mobilnych
- tw-elements (komponenty Bootstrap-like)

## Komponenty Vue

### calendar.vue (~500 linii)
Główny widok aplikacji - kalendarz obecności.

**Funkcje:**
- Nawigacja po miesiącach (przyciski + swipe na mobile)
- Tabela z pracownikami (desktop) / widok jednego pracownika (mobile)
- Checkboxy do zaznaczania obecności per dzień
- Oznaczanie weekendów, świąt i urlopów
- Zaznaczanie/odznaczanie wszystkich dni roboczych jednym kliknięciem
- Sortowanie pracowników drag & drop (tylko admin, desktop)
- Podświetlenie dzisiejszego dnia

**Stany wizualne dnia:**
- Biały - dzień roboczy, brak obecności
- Checkbox zaznaczony - obecność
- Szary (`k4gray`) - weekend/święto
- Żółty (`k4yellow`) - urlop (ikona SVG)

### vacations.vue
Zarządzanie urlopami zalogowanego pracownika.

**Funkcje:**
- Lista urlopów z datami i liczbą dni
- Podsumowanie dni urlopowych per rok (wykorzystane / wolne soboty-święta)
- Formularz dodawania urlopu (data wniosku, od, do, godziny pracy)
- Walidacja: brak nakładania się, logiczna kolejność dat
- Generowanie PDF karty urlopowej
- Usuwanie urlopów

### settings.vue
Panel administracyjny.

**Funkcje:**
- Lista pracowników z drag & drop do zmiany kolejności
- Toggle aktywności (aktywny/nieaktywny)
- Edycja: imię, godziny pracy dziennie
- Usuwanie pracownika (z potwierdzeniem)
- Synchronizacja świąt per rok (wpisanie roku -> pobranie z API)
- Lista zsynchronizowanych lat z liczbą świąt w soboty

## Szablony Blade

### layouts/app.blade.php
Layout: nawigacja z logo K4, linki (Kalendarz, Urlopy, Ustawienia dla admina), menu mobilne (hamburger), slot na zawartość.

### welcome.blade.php
Montuje `#calendar` -> calendar.vue

### vacations.blade.php
Montuje `#vacations` -> vacations.vue

### settings.blade.php
Montuje `#settings` -> settings.vue

### vacationCard.blade.php
Szablon PDF karty urlopowej: dane pracownika, daty, wyliczenie dni roboczych i godzin, linie na podpisy. Font: DejaVu Sans (wsparcie polskich znaków w PDF).

## Kolory K4

| Nazwa | Hex | Zastosowanie |
|-------|-----|-------------|
| k4pink | #ff497c | Akcenty, przyciski |
| k4green | #a0ce4e | Status aktywny |
| k4dark | #1f2732 | Navbar, tła |
| k4yellow | #dd9933 | Urlopy, ostrzeżenia |
| k4red | #e00040 | Usuwanie, błędy |
| k4gray | #C8C8C8 | Dni wolne, nieaktywne |

## Build

```bash
npm run dev      # Development build
npm run watch    # Watch mode
npm run prod     # Production build (minified)
```

Konfiguracja w `webpack.mix.js`: Vue 3 SFC, PostCSS z Tailwind, kopia favicon.
