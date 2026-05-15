# Architektura aplikacji

## Struktura katalogów

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/              # Kontrolery autoryzacji (Laravel UI)
│   │   ├── HolidayYearController.php
│   │   ├── PresenceController.php
│   │   ├── SettingsController.php
│   │   ├── UserController.php
│   │   └── VacationController.php
│   ├── Middleware/             # Standardowe middleware Laravel
│   └── Kernel.php
├── Models/
│   ├── HolidayYear.php
│   ├── Presence.php
│   ├── User.php
│   └── Vacation.php
├── Providers/
database/
├── factories/UserFactory.php
├── migrations/
├── seeders/DatabaseSeeder.php
resources/
├── js/
│   ├── app.js                 # Entry point Vue
│   ├── calendar.vue           # Kalendarz obecności
│   ├── settings.vue           # Panel admina
│   └── vacations.vue          # Zarządzanie urlopami
├── views/
│   ├── layouts/app.blade.php  # Layout główny
│   ├── welcome.blade.php      # Strona kalendarza
│   ├── settings.blade.php     # Strona ustawień
│   ├── vacations.blade.php    # Strona urlopów
│   ├── vacationCard.blade.php # Szablon PDF karty urlopowej
│   └── auth/                  # Widoki logowania/rejestracji
routes/
├── web.php                    # Routing główny
└── api.php                    # API (Sanctum)
```

## Modele

### User
- `name`, `email`, `password` - dane logowania
- `working_time` (smallInt, domyślnie 8) - wymiar godzin pracy dziennie
- `order` (smallInt) - kolejność wyświetlania na liście
- `is_admin` (bool) - rola administratora
- `is_active` (bool) - czy konto aktywne

### Presence
- `user_id` - powiązanie z pracownikiem
- `date` - dzień obecności
- Brak timestamps

### Vacation
- `user_id` - powiązanie z pracownikiem
- `working_time` (smallInt) - godziny pracy w dniu urlopu
- `request_date` - data złożenia wniosku
- `start_date`, `end_date` - zakres urlopu
- `days_off` - dni wolne w ramach okresu urlopowego
- Brak timestamps

### HolidayYear
- `year` (unique) - rok kalendarzowy
- `holidays` (JSON) - lista dat świątecznych
- `saturday_holiday_count` - ile świąt wypada w sobotę
- `synced_at` - data ostatniej synchronizacji

## Kontrolery

### UserController
Zarządzanie pracownikami: lista wszystkich/aktywnych, edycja, usuwanie (kaskadowe z obecnościami i urlopami), zmiana statusu aktywności, zmiana kolejności.

### PresenceController
Obecności: dodawanie/usuwanie pojedynczych i zbiorczych, pobieranie danych miesięcznych z budowaniem obiektu kalendarza (częstotliwość obecności per dzień).

### VacationController
Urlopy: dodawanie z walidacją (brak nakładania się, daty logiczne), usuwanie, lista urlopów użytkownika, urlopy wszystkich per miesiąc, generowanie PDF karty urlopowej z wyliczeniem dni roboczych i godzin.

### HolidayYearController
Święta: synchronizacja z API Nager.at (`https://date.nager.at/api/v3/PublicHolidays/{rok}/PL`), zapis do bazy, liczenie świąt w soboty.

### SettingsController
Przekierowanie do panelu admina (sprawdzenie roli).

## Autoryzacja

- Laravel UI (standardowe kontrolery Auth)
- Sesje + CSRF
- Sanctum dla tokenów API
- Rola admin sprawdzana per kontroler/widok (`is_admin`)
- Admin: biuro@web-systems.pl
