<?php
declare(strict_types=1);

final class TicketRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function counts(): array
    {
        return $this->pdo->query("SELECT COUNT(*) AS total,
            SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new_count,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_count,
            SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS done_count
            FROM tickets")->fetch() ?: [];
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM tickets WHERE id = :id');
        $statement->execute(['id' => $id]);
        $ticket = $statement->fetch();
        return $ticket === false ? null : $ticket;
    }

    public function listing(array $filters, ?int $limit = 100): array
    {
        $where = [];
        $params = [];
        if ($filters['q'] !== '') {
            $where[] = "(title LIKE :title_query ESCAPE '\\' OR requester LIKE :requester_query ESCAPE '\\' OR description LIKE :description_query ESCAPE '\\')";
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $filters['q']);
            foreach (['title_query', 'requester_query', 'description_query'] as $key) {
                $params[$key] = '%' . $escaped . '%';
            }
        }
        foreach (['status', 'priority'] as $field) {
            if ($filters[$field] !== '') {
                $where[] = $field . ' = :' . $field;
                $params[$field] = $filters[$field];
            }
        }
        $sql = 'SELECT * FROM tickets' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY created_at DESC, id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
        }
        $statement = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        if ($limit !== null) {
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        $statement->execute();
        return $statement->fetchAll();
    }

    public function create(array $ticket): void
    {
        $statement = $this->pdo->prepare('INSERT INTO tickets (title, requester, category, priority, status, description) VALUES (:title, :requester, :category, :priority, :status, :description)');
        $statement->execute($ticket);
    }

    public function update(int $id, array $ticket): void
    {
        $statement = $this->pdo->prepare('UPDATE tickets SET title = :title, requester = :requester, category = :category, priority = :priority, status = :status, description = :description, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $statement->execute($ticket + ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM tickets WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function upsertExternal(string $externalId, array $ticket): bool
    {
        $check = $this->pdo->prepare('SELECT id FROM tickets WHERE external_id = :external_id');
        $check->execute(['external_id' => $externalId]);
        $existed = $check->fetch() !== false;

        $statement = $this->pdo->prepare('INSERT INTO tickets (external_id, title, requester, category, priority, status, description)
            VALUES (:external_id, :title, :requester, :category, :priority, :status, :description)
            ON CONFLICT(external_id) DO UPDATE SET
                title = excluded.title, requester = excluded.requester, category = excluded.category,
                priority = excluded.priority, status = excluded.status, description = excluded.description,
                updated_at = CURRENT_TIMESTAMP');
        $statement->execute(['external_id' => $externalId] + $ticket);
        return $existed;
    }

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $callback();
            $this->pdo->commit();
            return $result;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }
}
