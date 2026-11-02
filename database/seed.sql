INSERT INTO tickets (title, requester, category, priority, status, description, created_at, updated_at) VALUES
('Brak dostępu do VPN', 'Anna Kowalska', 'access', 'high', 'new', 'Po aktualizacji hasła połączenie VPN odrzuca logowanie.', datetime('now', '-1 day'), datetime('now', '-1 day')),
('Drukarka na piętrze 2 nie drukuje', 'Marek Nowak', 'hardware', 'normal', 'in_progress', 'Zadanie trafia do kolejki, ale drukarka nie reaguje.', datetime('now', '-2 days'), datetime('now', '-1 day')),
('Aktualizacja pakietu biurowego', 'Ola Wiśniewska', 'software', 'low', 'done', 'Prośba o aktualizację aplikacji na stanowisku.', datetime('now', '-5 days'), datetime('now', '-3 days')),
('Nowe konto dla pracownika', 'Piotr Zieliński', 'access', 'normal', 'new', 'Przygotować konto i dostęp do współdzielonego folderu.', datetime('now', '-6 hours'), datetime('now', '-6 hours')),
('Błąd przy eksporcie PDF', 'Ewa Wójcik', 'software', 'high', 'in_progress', 'Eksport raportu kończy się komunikatem błędu.', datetime('now', '-3 days'), datetime('now', '-2 days')),
('Wymiana myszy', 'Jan Lewandowski', 'hardware', 'low', 'done', 'Uszkodzony przycisk myszy.', datetime('now', '-7 days'), datetime('now', '-4 days'));

