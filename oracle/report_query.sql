-- Przykładowe źródło danych do Oracle Reports (lub raportu w SQL Developer).
-- Parametry :date_from i :date_to podaj jako DATE. Górna granica jest wyłączna.
SELECT TRUNC(created_at, 'MM') AS report_month,
       category,
       COUNT(*) AS total,
       SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS completed,
       SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) AS high_priority
FROM hd_tickets
WHERE created_at >= :date_from
  AND created_at < :date_to
GROUP BY TRUNC(created_at, 'MM'), category
ORDER BY report_month, category;

