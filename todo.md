# TODO - Plan rozwoju seats.k4.pl

## 1. Aktualizacja technologiczna

- [x] Aktualizacja Laravel z 8.x do 12.x
- [x] Aktualizacja PHP do 8.4
- [x] Aktualizacja Vue 3 i zależności npm
- [x] Migracja z Laravel Mix na Vite
- [x] Przegląd i aktualizacja wszystkich paczek Composer
- [x] Usunięcie przestarzałych paczek (fruitcake/laravel-cors, facade/ignition, beyondcode/dump-server)
- [x] Nowa struktura Laravel 12 (bootstrap/app.php, usunięcie Kernel.php, middleware w framework)
- [x] Migracje: klasy anonimowe, password_resets -> password_reset_tokens, cascadeOnDelete
- [x] Routing: array syntax zamiast string controller references
- [x] Modele: $dates -> $casts, casts() method na User
- [ ] Testy na serwerze produkcyjnym (wymaga ext-xml, ext-mbstring, ext-mysql)
- [ ] Migracja istniejącej bazy danych (rename password_resets -> password_reset_tokens)

## 2. Moduł zarządzania urlopami (rozszerzenie)

### Panel admina (biuro@web-systems.pl)

- [x] Widok zbiorczy urlopów wszystkich pracowników (zakładka "Wszyscy pracownicy")
- [x] Filtrowanie pracowników checkboxami
- [x] Tabela uprawnień urlopowych (staż, wymiar, wykorzystane, pozostałe)
- [x] Kalendarz roczny z zaznaczonymi urlopami
- [x] Konfiguracja stażu pracy per pracownik (data rozpoczęcia, wykształcenie)
- [x] Automatyczne obliczanie wymiaru urlopu (20/26 dni wg stażu)
- [x] Logowanie zmian stażu pracy (audit trail)
- [x] Oznaczenie BETA
- [ ] Konfiguracja rocznego limitu dni urlopowych (korekty, urlopy specjalne)
- [x] Przeniesienie urlopu z poprzedniego roku (zaległy urlop)
- [x] Ukrywanie nieaktywnych pracowników (domyślnie włączone)
- [x] Upload dokumentów pracownika (dyplom, inne)
- [ ] Raporty zbiorcze:
  - Zestawienie urlopów per pracownik (rok)
  - Wykorzystanie urlopów w firmie (procentowe)
  - Eksport do PDF/Excel
- [ ] Zatwierdzanie/odrzucanie wniosków urlopowych

### Odbiór dnia wolnego za święto w sobotę

- [x] Oznaczenie w kalendarzu usera i admina dni odebranych za sobotę świąteczną (fioletowy kolor)
- [x] Opcja we wniosku urlopowym: typ "Odbiór za sobotę" z wyborem konkretnego święta
- [x] Walidacja: 1 dzień per święto, nie można odebrać dwa razy tego samego
- [x] Zliczanie odebranych dni za sobotę per pracownik per rok (osobno od puli urlopowej)
- [x] Wyświetlanie w podsumowaniu rocznym: ile dni do odbioru, ile odebranych, ile pozostałych
- [x] Widok admina: kolumna "Soboty" w tabeli uprawnień (odebrane/do odbioru)
- [x] Uwaga: dzień wolny przysługuje tylko za święto przypadające w sobotę, NIE w niedzielę (KP art. 130 §2)

### Panel pracownika

- [x] Podgląd własnego bilansu urlopowego (wymiar, wykorzystane, pozostałe)
- [x] Kalendarz roczny własnych urlopów
- [ ] Status wniosku (oczekujący/zatwierdzony/odrzucony)

### Ustawienia (Settings)

- [x] Podział na widgety: Pracownicy i Święta
- [x] Dane stażu pracy per pracownik (data rozpoczęcia, wykształcenie, data ukończenia)
- [x] Wyliczanie stażu z uwzględnieniem pokrywających się okresów nauki i pracy
- [x] Info o urlopie na żądanie (4 dni z puli)
- [x] Opcja włącz/wyłącz pracownika

### Dane archiwalne

- [x] Import danych archiwalnych Konrada (2024)
- [x] Import brakującego urlopu Dariusza (30.10.2024)
- [ ] Korekta rozbieżności (patrz uwagi poniżej)
- [ ] Historia urlopów z poprzednich lat - dalsze importy

## 3. Poprawki bezpieczeństwa

- [ ] Dodanie autoryzacji do endpointów obecności (obecnie publiczne)
- [ ] Dodanie autoryzacji do endpointu `/getAllUsersVacations`
- [ ] Przejście na RESTful routing
- [ ] Walidacja uprawnień admin w kontrolerach (middleware)

## 4. Usprawnienia techniczne

- [x] Dodanie relacji Eloquent (hasMany/belongsTo) zamiast ręcznych query
- [x] Kaskadowe usuwanie na poziomie bazy (foreign key constraints) - dodane w migracjach
- [ ] Testy jednostkowe i integracyjne
- [ ] CI/CD pipeline
