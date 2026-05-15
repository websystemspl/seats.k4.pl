# Schemat bazy danych

## Tabele

### users
| Kolumna | Typ | Opis |
|---------|-----|------|
| id | bigInt PK | |
| name | string | Imię i nazwisko |
| email | string, unique | Adres email |
| email_verified_at | timestamp, nullable | Data weryfikacji |
| password | string | Hash hasła |
| is_admin | boolean, default: false | Rola admina |
| is_active | boolean, default: false | Czy konto aktywne |
| working_time | smallInt, default: 8 | Godziny pracy/dzień |
| order | smallInt, default: 0 | Kolejność wyświetlania |
| remember_token | string | Token "zapamiętaj mnie" |
| created_at, updated_at | timestamps | |

### presences
| Kolumna | Typ | Opis |
|---------|-----|------|
| id | bigInt PK | |
| user_id | bigInt FK -> users | Pracownik |
| date | date | Dzień obecności |

Brak timestamps. Jedna obecność = jeden dzień roboczy.

### vacations
| Kolumna | Typ | Opis |
|---------|-----|------|
| id | bigInt PK | |
| user_id | bigInt FK -> users | Pracownik |
| working_time | smallInt | Godziny pracy w dniu urlopu |
| request_date | date | Data złożenia wniosku |
| start_date | date | Początek urlopu |
| end_date | date | Koniec urlopu |
| days_off | int | Dni wolne w okresie |

Brak timestamps.

### holiday_years
| Kolumna | Typ | Opis |
|---------|-----|------|
| id | bigInt PK | |
| year | smallInt, unique | Rok kalendarzowy |
| holidays | JSON | Tablica dat świąt |
| saturday_holiday_count | smallInt | Święta w soboty |
| synced_at | timestamp, nullable | Data synchronizacji |
| created_at, updated_at | timestamps | |

### Tabele systemowe Laravel
- `password_resets` - tokeny resetowania haseł
- `personal_access_tokens` - tokeny Sanctum
- `failed_jobs` - nieudane zadania kolejki
- `migrations` - historia migracji

## Migracje

Chronologicznie:
1. `create_users_table` - tabela użytkowników
2. `create_password_resets_table`
3. `create_failed_jobs_table`
4. `create_personal_access_tokens_table`
5. `create_presences_table` - obecności
6. `create_vacations_table` - urlopy
7. `create_holiday_years_table` - lata świąteczne

## Relacje

- User 1:N Presence (kaskadowe usuwanie w kontrolerze)
- User 1:N Vacation (kaskadowe usuwanie w kontrolerze)
- HolidayYear - niezależna tabela (lookup po roku)
