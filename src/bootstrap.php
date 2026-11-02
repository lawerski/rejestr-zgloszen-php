<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/TicketRepository.php';
require_once __DIR__ . '/VendorCsvImporter.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function database(): PDO
{
    $directory = dirname(__DIR__) . '/var';
    if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
        throw new RuntimeException('Nie można utworzyć katalogu bazy danych.');
    }

    $path = $directory . '/helpdesk.sqlite';
    $isNew = !is_file($path) || filesize($path) === 0;
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $schema = file_get_contents(dirname(__DIR__) . '/database/sqlite.sql');
    if ($schema === false) {
        throw new RuntimeException('Nie można odczytać schematu bazy danych.');
    }
    $pdo->exec($schema);

    if ($isNew) {
        $seed = file_get_contents(dirname(__DIR__) . '/database/seed.sql');
        if ($seed === false) {
            throw new RuntimeException('Nie można odczytać przykładowych danych.');
        }
        $pdo->exec($seed);
    }

    return $pdo;
}

