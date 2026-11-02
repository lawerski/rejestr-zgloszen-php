<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function input_string(mixed $value): string
{
    return is_string($value) ? trim($value) : '';
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $provided = $_POST['_csrf'] ?? null;
    if (!is_string($provided) || !hash_equals(csrf_token(), $provided)) {
        http_response_code(403);
        exit('Nieprawidłowy token formularza. Odśwież stronę i spróbuj ponownie.');
    }
}

function redirect_to(string $url): never
{
    header('Location: ' . $url, true, 303);
    exit;
}

function ticket_id(mixed $value): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false || $id === null) {
        http_response_code(400);
        exit('Nieprawidłowy identyfikator zgłoszenia.');
    }
    return $id;
}

function ticket_input(array $input): array
{
    $result = [];
    foreach (['title', 'requester', 'category', 'priority', 'status', 'description'] as $field) {
        $result[$field] = input_string($input[$field] ?? null);
    }
    return $result;
}

function char_count(string $value): int
{
    $count = preg_match_all('/./us', $value);
    return $count === false ? 0 : $count;
}

function validate_ticket(array $ticket): array
{
    $errors = [];
    foreach (['title', 'requester', 'description'] as $field) {
        if (preg_match('//u', $ticket[$field]) !== 1) {
            $errors[$field] = 'Wpisz poprawny tekst UTF-8.';
        }
    }
    if (!isset($errors['title']) && (char_count($ticket['title']) < 3 || char_count($ticket['title']) > 120)) {
        $errors['title'] = 'Tytuł powinien mieć od 3 do 120 znaków.';
    }
    if (!isset($errors['requester']) && (char_count($ticket['requester']) < 2 || char_count($ticket['requester']) > 80)) {
        $errors['requester'] = 'Podaj imię i nazwisko (2–80 znaków).';
    }
    if (!isset($errors['description']) && char_count($ticket['description']) > 1500) {
        $errors['description'] = 'Opis może mieć maksymalnie 1500 znaków.';
    }
    foreach (['category' => ['hardware', 'software', 'access', 'other'], 'priority' => ['low', 'normal', 'high'], 'status' => ['new', 'in_progress', 'done']] as $field => $allowed) {
        if (!in_array($ticket[$field], $allowed, true)) {
            $errors[$field] = 'Wybierz wartość z listy.';
        }
    }
    return $errors;
}

function label_for(string $kind, string $value): string
{
    $labels = [
        'category' => ['hardware' => 'Sprzęt', 'software' => 'Oprogramowanie', 'access' => 'Dostęp', 'other' => 'Inne'],
        'priority' => ['low' => 'Niski', 'normal' => 'Normalny', 'high' => 'Wysoki'],
        'status' => ['new' => 'Nowe', 'in_progress' => 'W trakcie', 'done' => 'Zamknięte'],
    ];
    return $labels[$kind][$value] ?? $value;
}

function csv_safe(string $value): string
{
    return preg_match('/^\s*[=+\-@]/u', $value) === 1 ? "'" . $value : $value;
}

