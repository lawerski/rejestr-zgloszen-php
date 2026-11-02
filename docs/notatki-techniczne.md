# Notatki techniczne

Żądanie trafia do `public/index.php`, który wywołuje metody repozytorium i wybiera widok z `src/views/`. Dane są w SQLite poza katalogiem `public`. Zapytania przyjmujące dane użytkownika używają parametrów PDO. Formularze zapisujące mają token CSRF, a widoki kodują tekst przez `htmlspecialchars`.

Import czyta plik według nazw kolumn z `config/vendor.php`. Wszystkie wiersze są walidowane przed zapisem. Aktualizacja używa `external_id`, a zapisy są w jednej transakcji. Limit 500 wierszy pozwala przechować je w pamięci. Eksport CSV dodaje apostrof przed tekstem, który arkusz mógłby odczytać jako formułę.

**Gdy import się nie udaje:** sprawdź separator `;`, kodowanie UTF-8, nazwy kolumn, mapy kodów i numer wiersza z komunikatu. Przy błędzie serwera sprawdź log PHP oraz prawa zapisu do `var/`. Próbę odtworzenia problemu wykonaj na kopii bazy. Po zmianie uruchom `php tests/run.php` i ponów odpowiedni scenariusz z `zalozenia-i-testy.md`.

Skrypty `oracle/` nie są częścią lokalnego połączenia PHP. `schema_and_procedure.sql` tworzy tabelę `HD_TICKETS`, trigger aktualizujący czas zmiany, procedurę `HD_SET_STATUS` i widok statusów. `report_query.sql` grupuje zgłoszenia po miesiącu i kategorii. Wymagają osobnej bazy Oracle; nie były uruchamiane w tym projekcie.

