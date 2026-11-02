<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/TicketRepository.php';
require_once dirname(__DIR__) . '/src/VendorCsvImporter.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $message);
    }
    echo 'OK: ' . $message . PHP_EOL;
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec(file_get_contents(dirname(__DIR__) . '/database/sqlite.sql'));
$repository = new TicketRepository($pdo);
$importer = new VendorCsvImporter($repository, require dirname(__DIR__) . '/config/vendor.php');

$source = fopen(dirname(__DIR__) . '/examples/vendor_export.csv', 'rb');
check($source !== false, 'plik przykładowy jest dostępny');
$first = $importer->import($source);
fclose($source);
check($first === ['added' => 3, 'updated' => 0], 'pierwszy import dodaje trzy zgłoszenia');

$source = fopen(dirname(__DIR__) . '/examples/vendor_export.csv', 'rb');
$second = $importer->import($source);
fclose($source);
check($second === ['added' => 0, 'updated' => 3], 'powtórny import aktualizuje bez duplikatów');
check((int) $repository->counts()['total'] === 3, 'po imporcie są trzy rekordy');

$filtered = $repository->listing(['q' => 'aplikacją', 'status' => 'in_progress', 'priority' => 'high']);
check(count($filtered) === 1 && $filtered[0]['external_id'] === 'EXT-1002', 'wyszukiwanie i filtry łączą się poprawnie');

$ticket = ['title' => "Test'; DROP TABLE tickets; --", 'requester' => 'Testowy Użytkownik', 'category' => 'other', 'priority' => 'low', 'status' => 'new', 'description' => 'Dane testowe'];
check(validate_ticket($ticket) === [], 'formularz przyjmuje poprawne dane');
$repository->create($ticket);
check((int) $repository->counts()['total'] === 4, 'zapytanie parametryzowane zapisuje tekst bez wykonania SQL');
check(count($repository->listing(['q' => '%', 'status' => '', 'priority' => ''])) === 0, 'procent w wyszukiwarce jest znakiem dosłownym');
check(csv_safe('=1+1') === "'=1+1", 'eksport osłania komórkę przypominającą formułę');

$invalid = fopen('php://temp', 'w+');
fwrite($invalid, "Ticket ID;Subject;Requester;Type;Severity;State;Details\nEXT-1002;Zmieniony tytuł;Tomasz Maj;Application;P1;Assigned;Opis\nEXT-9999;Nowy test;Jan Testowy;Device;P3;Unknown;Opis\n");
rewind($invalid);
try {
    $importer->import($invalid);
    throw new RuntimeException('FAIL: błędny plik nie został odrzucony');
} catch (InvalidArgumentException $error) {
    check(str_contains($error->getMessage(), 'Wiersz 3'), 'nieznana wartość dostawcy wskazuje wadliwy wiersz');
} finally {
    fclose($invalid);
}
check((int) $repository->counts()['total'] === 4, 'błędny import nie dodaje rekordów');
check($repository->listing(['q' => 'Zmieniony tytuł', 'status' => '', 'priority' => '']) === [], 'błędny import nie zmienia istniejącego zgłoszenia');

try {
    $repository->transaction(function () use ($repository, $ticket): void {
        $repository->create($ticket);
        throw new RuntimeException('przerwana operacja');
    });
} catch (RuntimeException $error) {
    check($error->getMessage() === 'przerwana operacja', 'błąd transakcji jest przekazywany wyżej');
}
check((int) $repository->counts()['total'] === 4, 'przerwana transakcja cofa zapis');

echo 'Wszystkie testy przeszły.' . PHP_EOL;

