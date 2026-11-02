<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

try {
    $repository = new TicketRepository(database());
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = input_string($_GET['action'] ?? null);

    if ($method === 'POST') {
        verify_csrf();
        if ($action === 'create' || $action === 'update') {
            $id = $action === 'update' ? ticket_id($_POST['id'] ?? null) : null;
            if ($id !== null && $repository->find($id) === null) {
                http_response_code(404);
                exit('Nie znaleziono zgłoszenia.');
            }
            $values = ticket_input($_POST);
            $errors = validate_ticket($values);
            if ($errors !== []) {
                $_SESSION['form_state'] = ['action' => $action, 'id' => $id, 'values' => $values, 'errors' => $errors];
                redirect_to($id === null ? '/?page=new' : '/?page=edit&id=' . $id);
            }
            if ($id === null) {
                $repository->create($values);
                $_SESSION['flash'] = 'Zgłoszenie zostało dodane.';
            } else {
                $repository->update($id, $values);
                $_SESSION['flash'] = 'Zmiany zostały zapisane.';
            }
            redirect_to('/');
        }
        if ($action === 'delete') {
            $id = ticket_id($_POST['id'] ?? null);
            $repository->delete($id);
            $_SESSION['flash'] = 'Zgłoszenie zostało usunięte.';
            redirect_to('/');
        }
        if ($action === 'import') {
            $file = $_FILES['csv'] ?? null;
            if (
                !is_array($file)
                || ($file['error'] ?? null) !== UPLOAD_ERR_OK
                || !is_int($file['size'] ?? null)
                || $file['size'] > 1_000_000
                || !is_string($file['tmp_name'] ?? null)
                || !is_uploaded_file($file['tmp_name'])
            ) {
                $_SESSION['import_error'] = 'Wybierz poprawny plik CSV o rozmiarze do 1 MB.';
                redirect_to('/?page=import');
            }
            $handle = fopen($file['tmp_name'], 'rb');
            if ($handle === false) {
                $_SESSION['import_error'] = 'Nie można odczytać przesłanego pliku.';
                redirect_to('/?page=import');
            }
            $importError = null;
            try {
                $vendorConfig = require dirname(__DIR__) . '/config/vendor.php';
                $importer = new VendorCsvImporter($repository, $vendorConfig);
                $result = $importer->import($handle);
            } catch (InvalidArgumentException $error) {
                $importError = $error->getMessage();
            } finally {
                fclose($handle);
            }
            if ($importError !== null) {
                $_SESSION['import_error'] = $importError;
                redirect_to('/?page=import');
            }
            $_SESSION['flash'] = 'Import zakończony: dodano ' . $result['added'] . ', zaktualizowano ' . $result['updated'] . ' zgłoszeń.';
            redirect_to('/');
        }
        http_response_code(400);
        exit('Nieznana operacja.');
    }

    if ($method !== 'GET') {
        header('Allow: GET, POST');
        http_response_code(405);
        exit('Niedozwolona metoda żądania.');
    }

    $page = input_string($_GET['page'] ?? null);
    $search = input_string($_GET['q'] ?? null);
    preg_match('/^.{0,100}/us', $search, $searchMatch);
    $filters = [
        'q' => $searchMatch[0] ?? '',
        'status' => input_string($_GET['status'] ?? null),
        'priority' => input_string($_GET['priority'] ?? null),
    ];
    if (!in_array($filters['status'], ['', 'new', 'in_progress', 'done'], true)) {
        $filters['status'] = '';
    }
    if (!in_array($filters['priority'], ['', 'low', 'normal', 'high'], true)) {
        $filters['priority'] = '';
    }

    if ($page === 'export') {
        $rows = $repository->listing($filters, null);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="zgloszenia-' . gmdate('Y-m-d') . '.csv"');
        header('X-Content-Type-Options: nosniff');
        $output = fopen('php://output', 'wb');
        if ($output === false) {
            throw new RuntimeException('Nie można przygotować pliku CSV.');
        }
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', 'ID dostawcy', 'Tytuł', 'Zgłaszający', 'Kategoria', 'Priorytet', 'Status', 'Opis', 'Utworzono', 'Zmieniono'], ';', '"', '');
        foreach ($rows as $row) {
            fputcsv($output, [
                $row['id'], csv_safe($row['external_id'] ?? ''), csv_safe($row['title']), csv_safe($row['requester']),
                label_for('category', $row['category']), label_for('priority', $row['priority']),
                label_for('status', $row['status']), csv_safe($row['description']),
                $row['created_at'], $row['updated_at'],
            ], ';', '"', '');
        }
        fclose($output);
        exit;
    }

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    $csrf = csrf_token();

    if ($page === 'import') {
        $importError = $_SESSION['import_error'] ?? null;
        unset($_SESSION['import_error']);
        $pageTitle = 'Import z systemu dostawcy';
        $view = dirname(__DIR__) . '/src/views/import.php';
    } elseif ($page === 'new' || $page === 'edit') {
        $id = $page === 'edit' ? ticket_id($_GET['id'] ?? null) : null;
        $ticket = $id === null ? null : $repository->find($id);
        if ($id !== null && $ticket === null) {
            http_response_code(404);
            exit('Nie znaleziono zgłoszenia.');
        }
        $values = $ticket ?? [
            'title' => '', 'requester' => '', 'category' => 'hardware',
            'priority' => 'normal', 'status' => 'new', 'description' => '',
        ];
        $errors = [];
        $formState = $_SESSION['form_state'] ?? null;
        unset($_SESSION['form_state']);
        if (is_array($formState) && ($formState['action'] ?? null) === ($id === null ? 'create' : 'update') && ($formState['id'] ?? null) === $id) {
            $values = $formState['values'];
            $errors = $formState['errors'];
        }
        $pageTitle = $id === null ? 'Nowe zgłoszenie' : 'Edytuj zgłoszenie #' . $id;
        $view = dirname(__DIR__) . '/src/views/form.php';
    } else {
        $counts = $repository->counts();
        $tickets = $repository->listing($filters);
        $pageTitle = 'Lista zgłoszeń';
        $view = dirname(__DIR__) . '/src/views/list.php';
    }
    require dirname(__DIR__) . '/src/views/layout.php';
} catch (Throwable $error) {
    error_log((string) $error);
    http_response_code(500);
    echo '<!doctype html><html lang="pl"><meta charset="utf-8"><title>Błąd</title><p>Nie udało się przetworzyć żądania. Sprawdź konfigurację PHP i uprawnienia katalogu var.</p></html>';
}

