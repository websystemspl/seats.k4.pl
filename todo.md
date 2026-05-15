# TODO - Plan rozwoju seats.k4.pl

## 1. Aktualizacja technologiczna

- [ ] Aktualizacja Laravel z 8.x do najnowszej wersji (12.x)
- [ ] Aktualizacja PHP do 8.4 lub 8.5
- [ ] Aktualizacja Vue 3 i zależności npm
- [ ] Migracja z Laravel Mix na Vite
- [ ] Aktualizacja Tailwind CSS do najnowszej wersji
- [ ] Przegląd i aktualizacja wszystkich paczek Composer
- [ ] Usunięcie przestarzałych paczek (fruitcake/laravel-cors - wbudowane od Laravel 9)

## 2. Moduł zarządzania urlopami (rozszerzenie)

### Panel admina (biuro@web-systems.pl)

- [ ] Widok zbiorczy urlopów wszystkich pracowników
- [ ] Konfiguracja rocznego limitu dni urlopowych per pracownik
- [ ] Nanoszenie zmian w limitach (korekty, urlopy specjalne)
- [ ] Automatyczne obliczanie:
  - Ile dni urlopu przysługuje w danym roku
  - Ile dni już wykorzystano
  - Ile dni pozostało do wykorzystania
  - Ile dni przeniesiono z poprzedniego roku (zaległy urlop)
- [ ] Raporty zbiorcze:
  - Zestawienie urlopów per pracownik (rok)
  - Wykorzystanie urlopów w firmie (procentowe)
  - Eksport do PDF/Excel
- [ ] Zatwierdzanie/odrzucanie wniosków urlopowych

### Panel pracownika

- [ ] Podgląd własnego bilansu urlopowego:
  - Limit roczny
  - Wykorzystane dni
  - Pozostałe dni
  - Zaległy urlop z poprzedniego roku
- [ ] Status wniosku (oczekujący/zatwierdzony/odrzucony)

### Dane archiwalne

- [ ] Import danych archiwalnych (po uruchomieniu funkcjonalności)
- [ ] Historia urlopów z poprzednich lat

## 3. Poprawki bezpieczeństwa

- [ ] Dodanie autoryzacji do endpointów obecności (obecnie publiczne)
- [ ] Dodanie autoryzacji do endpointu `/getAllUsersVacations`
- [ ] Przejście na RESTful routing
- [ ] Walidacja uprawnień admin w kontrolerach (middleware)

## 4. Usprawnienia techniczne

- [ ] Dodanie relacji Eloquent (hasMany/belongsTo) zamiast ręcznych query
- [ ] Kaskadowe usuwanie na poziomie bazy (foreign key constraints)
- [ ] Testy jednostkowe i integracyjne
- [ ] CI/CD pipeline
