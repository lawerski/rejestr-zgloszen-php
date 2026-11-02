<?php
declare(strict_types=1);

final class VendorCsvImporter
{
    public function __construct(private TicketRepository $repository, private array $config)
    {
    }

    /** @param resource $handle */
    public function import($handle): array
    {
        if (!is_resource($handle)) {
            throw new InvalidArgumentException('Nie można odczytać pliku CSV.');
        }
        $header = fgetcsv($handle, 0, ';', '"', '');
        if ($header === false || $header === [null]) {
            throw new InvalidArgumentException('Plik CSV jest pusty.');
        }
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $indexes = [];
        foreach ($this->config['columns'] as $field => $column) {
            $index = array_search($column, $header, true);
            if ($index === false) {
                throw new InvalidArgumentException('Brakuje kolumny: ' . $column . '.');
            }
            $indexes[$field] = $index;
        }

        $records = [];
        $seen = [];
        $rowNumber = 1;
        while (($row = fgetcsv($handle, 0, ';', '"', '')) !== false) {
            $rowNumber++;
            if ($row === [null]) {
                continue;
            }
            if (count($row) !== count($header)) {
                throw new InvalidArgumentException('Wiersz ' . $rowNumber . ': liczba pól nie zgadza się z nagłówkiem.');
            }
            if (count($records) >= 500) {
                throw new InvalidArgumentException('Jeden plik może zawierać maksymalnie 500 zgłoszeń.');
            }
            $source = [];
            foreach ($indexes as $field => $index) {
                $source[$field] = input_string($row[$index]);
            }
            $externalId = $source['external_id'];
            if ($externalId === '' || char_count($externalId) > 64 || preg_match('//u', $externalId) !== 1 || isset($seen[$externalId])) {
                throw new InvalidArgumentException('Wiersz ' . $rowNumber . ': identyfikator dostawcy jest pusty, za długi lub powtórzony.');
            }
            $seen[$externalId] = true;
            $ticket = [];
            foreach (['title', 'requester', 'description'] as $field) {
                $ticket[$field] = $source[$field];
            }
            foreach (['category', 'priority', 'status'] as $field) {
                $ticket[$field] = $this->config[$field][$source[$field]] ?? '';
            }
            if (validate_ticket($ticket) !== []) {
                throw new InvalidArgumentException('Wiersz ' . $rowNumber . ': nieprawidłowe dane lub nieznana wartość w konfiguracji dostawcy.');
            }
            $records[] = ['external_id' => $externalId, 'ticket' => $ticket];
        }

        return $this->repository->transaction(function () use ($records): array {
            $added = 0;
            $updated = 0;
            foreach ($records as $record) {
                if ($this->repository->upsertExternal($record['external_id'], $record['ticket'])) {
                    $updated++;
                } else {
                    $added++;
                }
            }
            return ['added' => $added, 'updated' => $updated];
        });
    }
}
