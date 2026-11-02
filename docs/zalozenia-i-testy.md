# Założenia i sprawdzenie działania

Założenie: mały zespół IT prowadzi własną listę zgłoszeń. Część spraw przychodzi z narzędzia zewnętrznego jako CSV. Nie ma synchronizacji online ani kont użytkowników; to lokalny prototyp.

## Zachowanie, które ma znaczenie

1. Nowe zgłoszenie wymaga tytułu (3–120 znaków), osoby zgłaszającej, kategorii, priorytetu i statusu.
2. Na liście można jednocześnie szukać tekstu oraz filtrować po statusie i priorytecie. Eksport bierze wszystkie pasujące rekordy, także gdy lista pokazuje tylko pierwsze 100.
3. ID dostawcy jest unikatowe. Kolejny import tego samego pliku aktualizuje istniejące zgłoszenia.
4. Jeżeli choć jeden wiersz CSV ma nieznany kod lub błędne dane, nic z pliku nie trafia do bazy.

## Scenariusze do przejścia w przeglądarce

| Czynność | Oczekiwany wynik |
| --- | --- |
| Dodaj zgłoszenie, a potem ustaw status „W trakcie”. | Rekord jest na liście, a licznik statusu się zmienia. |
| Wyszukaj fragment tytułu i wybierz priorytet. | Lista zawiera tylko pasujące rekordy. |
| Wczytaj `examples/vendor_export.csv` dwa razy. | Pierwszy import dodaje 3 rekordy, drugi aktualizuje 3; liczba rekordów nie rośnie za drugim razem. |
| W kopii CSV zamień `Assigned` na `Unknown` i spróbuj importować. | Aplikacja wskazuje błędny wiersz i nie zapisuje zmian. |
| Ustaw filtr i pobierz CSV. | W pliku są tylko pasujące zgłoszenia. |

Skrypt `tests/run.php` sprawdza logikę bez użycia przeglądarki. Wynik testu UAT można zapisać jako: data, scenariusz, wynik, kroki odtworzenia błędu.

