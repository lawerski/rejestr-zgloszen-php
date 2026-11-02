# Rejestr zgłoszeń IT

Niewielka aplikacja do obsługi zgłoszeń w zespole wsparcia. Zgłoszenia można dopisać ręcznie albo wczytać z pliku CSV otrzymanego od dostawcy. Ten drugi przypadek jest głównym ćwiczeniem w projekcie: mapowanie obcych statusów, aktualizacja po ID dostawcy i wycofanie importu, gdy plik ma błędne dane.

## Uruchomienie

Potrzebne są PHP 8.2 lub nowsze i rozszerzenie `pdo_sqlite`. Nie ma zależności Composera.

```bash
cd helpdesk-php
php -S localhost:8000 -t public
```

Adres: `http://localhost:8000`. Przy pierwszym uruchomieniu aplikacja tworzy `var/helpdesk.sqlite` i dodaje kilka przykładowych zgłoszeń. Proces PHP musi mieć prawo zapisu w katalogu projektu. Jeśli chcesz wyczyścić dane, zatrzymaj serwer i usuń ten plik; powstanie ponownie przy następnym wejściu.

## Co można zrobić

- Dodać i edytować zgłoszenie, zmienić status i priorytet, usunąć rekord.
- Przeszukać tytuły, opisy i zgłaszających; połączyć szukanie z filtrami.
- Pobrać CSV z wynikami filtrów.
- Zaimportować `examples/vendor_export.csv`. Drugi import tego samego pliku aktualizuje trzy zgłoszenia zamiast tworzyć duplikaty.

Plik dostawcy ma separator `;` i nagłówki opisane w `config/vendor.php`. Tam też są mapy wartości, np. `Assigned` → `in_progress`. Import odrzuca nieznany kod lub błędny wiersz bez częściowego zapisu. Limit to 1 MB i 500 rekordów na plik. Ręczna edycja zaimportowanego zgłoszenia może zostać nadpisana przy następnym imporcie tego samego ID.

## Sprawdzenie

```bash
php tests/run.php
```

Test używa SQLite w pamięci, więc nie zmienia danych aplikacji. Sprawdza m.in. ponowny import, filtrowanie, odrzucenie błędnego pliku i wycofanie transakcji. Krótkie scenariusze do przejścia w przeglądarce są w `docs/zalozenia-i-testy.md`.

## Gdzie co jest

- `public/index.php` — obsługa żądań i eksportu; `public/assets/` — wygląd.
- `src/TicketRepository.php` — zapytania PDO; `src/VendorCsvImporter.php` — odczyt i walidacja CSV.
- `database/` — schemat SQLite i dane startowe; `config/vendor.php` — mapowanie dostawcy.
- `docs/` — założenia, testy akceptacyjne i notatki techniczne.
- `oracle/` — osobny wariant schematu, procedura PL/SQL i zapytanie raportowe.

Aplikacja PHP działa na SQLite. Skrypty Oracle są osobnym ćwiczeniem i nie są wywoływane z PHP. Nie ma tu pliku Oracle Forms ani gotowego raportu Oracle Reports. Do lokalnego uruchomienia nie potrzeba Oracle.

To wersja do pokazania lokalnie, bez kont użytkowników. Przed wystawieniem jej w sieci trzeba dodać logowanie i uprawnienia. Warto też samodzielnie przejść kod i uruchomić testy przed podaniem projektu w CV.

**Krótki opis do CV po przejrzeniu projektu:** Rejestr zgłoszeń IT w PHP 8.2 i SQLite; obsługa incydentów, filtrowanie, import CSV z mapowaniem danych dostawcy, testy logiki oraz scenariusze akceptacyjne.

