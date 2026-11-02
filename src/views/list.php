<div class="page-head">
    <div>
        <h1>Zgłoszenia IT</h1>
        <p>Rejestr spraw zgłoszonych do zespołu wsparcia.</p>
    </div>
    <a class="button button-primary" href="/?page=new">Dodaj zgłoszenie</a>
</div>

<div class="summary" aria-label="Liczba zgłoszeń według statusu">
    <div><span>Wszystkie</span><strong><?= (int) ($counts['total'] ?? 0) ?></strong></div>
    <div><span>Nowe</span><strong><?= (int) ($counts['new_count'] ?? 0) ?></strong></div>
    <div><span>W trakcie</span><strong><?= (int) ($counts['in_progress_count'] ?? 0) ?></strong></div>
    <div><span>Zamknięte</span><strong><?= (int) ($counts['done_count'] ?? 0) ?></strong></div>
</div>

<section class="panel" aria-labelledby="list-heading">
    <div class="panel-head">
        <h2 id="list-heading">Lista</h2>
        <div class="panel-actions">
            <a href="/?page=import">Import CSV</a>
            <a href="/?<?= e(http_build_query(['page' => 'export'] + $filters)) ?>">Eksport CSV</a>
        </div>
    </div>

    <form class="filters" method="get" action="/">
        <label class="search-field">
            <span class="sr-only">Szukaj zgłoszeń</span>
            <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Tytuł, opis lub zgłaszający">
        </label>
        <label>
            <span class="sr-only">Status</span>
            <select name="status">
                <option value="">Wszystkie statusy</option>
                <?php foreach (['new', 'in_progress', 'done'] as $option): ?>
                    <option value="<?= e($option) ?>" <?= $filters['status'] === $option ? 'selected' : '' ?>><?= e(label_for('status', $option)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            <span class="sr-only">Priorytet</span>
            <select name="priority">
                <option value="">Wszystkie priorytety</option>
                <?php foreach (['low', 'normal', 'high'] as $option): ?>
                    <option value="<?= e($option) ?>" <?= $filters['priority'] === $option ? 'selected' : '' ?>><?= e(label_for('priority', $option)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Pokaż</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Zgłoszenie</th><th>Kategoria</th><th>Priorytet</th><th>Status</th><th>Dodano</th><th><span class="sr-only">Akcje</span></th></tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td class="ticket-main">
                            <a href="/?page=edit&amp;id=<?= (int) $ticket['id'] ?>"><?= e($ticket['title']) ?></a>
                            <small>#<?= (int) $ticket['id'] ?> · <?= e($ticket['requester']) ?><?php if ($ticket['external_id'] !== null): ?> · <?= e($ticket['external_id']) ?><?php endif; ?></small>
                        </td>
                        <td><?= e(label_for('category', $ticket['category'])) ?></td>
                        <td><span class="priority priority-<?= e($ticket['priority']) ?>"><?= e(label_for('priority', $ticket['priority'])) ?></span></td>
                        <td><span class="status status-<?= e($ticket['status']) ?>"><?= e(label_for('status', $ticket['status'])) ?></span></td>
                        <td><?= e(substr($ticket['created_at'], 0, 10)) ?></td>
                        <td class="row-actions">
                            <a href="/?page=edit&amp;id=<?= (int) $ticket['id'] ?>">Edytuj</a>
                            <form method="post" action="/?action=delete" onsubmit="return confirm('Usunąć to zgłoszenie?')">
                                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                <input type="hidden" name="id" value="<?= (int) $ticket['id'] ?>">
                                <button type="submit">Usuń</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($tickets === []): ?>
                    <tr><td class="empty" colspan="6">Brak wyników. Zmień filtry albo dodaj zgłoszenie.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="table-note">Lista pokazuje 100 najnowszych wyników. Eksport pobiera wszystkie pasujące zgłoszenia.</p>
</section>
