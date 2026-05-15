# API i routing

## Endpointy wymagające autoryzacji

| Metoda | URL | Kontroler | Opis |
|--------|-----|-----------|------|
| GET | `/settings` | SettingsController@index | Panel admina (tylko admin) |
| GET | `/vacations` | VacationController@index | Strona urlopów |
| GET | `/holidayYears` | HolidayYearController@index | Lista zsynchronizowanych lat |
| POST | `/holidayYears/sync` | HolidayYearController@sync | Synchronizacja świąt z API |
| POST | `/changeStatus` | UserController@changeStatus | Zmiana statusu pracownika |
| POST | `/deleteUser` | UserController@deleteUser | Usunięcie pracownika |
| POST | `/editUser` | UserController@editUser | Edycja pracownika |
| POST | `/updateOrder` | UserController@updateOrder | Zmiana kolejności |
| GET | `/getUserVacations` | VacationController@getUserVacations | Urlopy zalogowanego |
| POST | `/addVacation` | VacationController@addVacation | Dodanie urlopu |
| POST | `/deleteVacation` | VacationController@deleteVacation | Usunięcie urlopu |
| GET | `/getVacationCard/{id}` | VacationController@getVacationCard | PDF karty urlopowej |

## Endpointy publiczne (bez autoryzacji)

| Metoda | URL | Kontroler | Opis |
|--------|-----|-----------|------|
| GET | `/` | welcome view | Kalendarz (strona główna) |
| GET | `/users` | UserController@getAllUsers | Lista wszystkich pracowników |
| GET | `/activeUsers` | UserController@getActiveUsers | Lista aktywnych pracowników |
| POST | `/addPresence` | PresenceController@addPresence | Dodanie obecności (1 dzień) |
| POST | `/addPresences` | PresenceController@addPresences | Dodanie obecności (wiele dni) |
| POST | `/removePresence` | PresenceController@removePresence | Usunięcie obecności (1 dzień) |
| POST | `/removePresences` | PresenceController@removePresences | Usunięcie obecności (wiele) |
| POST | `/getPresences` | PresenceController@getPresences | Dane obecności za miesiąc |
| POST | `/getAllUsersVacations` | VacationController@getAllUsersVacations | Urlopy wszystkich (miesiąc) |

## Endpointy autoryzacji (Laravel UI)

| Metoda | URL | Opis |
|--------|-----|------|
| GET/POST | `/login` | Logowanie |
| GET/POST | `/register` | Rejestracja |
| POST | `/logout` | Wylogowanie |
| GET/POST | `/password/reset` | Reset hasła |
| GET/POST | `/password/confirm` | Potwierdzenie hasła |

## API (Sanctum)

| Metoda | URL | Opis |
|--------|-----|------|
| GET | `/api/user` | Dane zalogowanego użytkownika |

## Uwagi

- Endpointy obecności (`addPresence`, `removePresence`, `getPresences`) nie wymagają autoryzacji - potencjalne ryzyko bezpieczeństwa
- Brak API w formacie RESTful (POST zamiast GET dla odczytu danych, brak zasobowych URL-i)
- Brak wersjonowania API
