<div class="form-page">
    <a class="back-link" href="/">← Wróć do listy</a>
    <h1><?= e($pageTitle) ?></h1>
    <p>Pola oznaczone * są wymagane.</p>

    <?php if ($errors !== []): ?>
        <p class="form-error" role="alert">Sprawdź zaznaczone pola.</p>
    <?php endif; ?>

    <form class="form-card" method="post" action="/?action=<?= $id === null ? 'create' : 'update' ?>">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <?php if ($id !== null): ?>
            <input type="hidden" name="id" value="<?= (int) $id ?>">
        <?php endif; ?>

        <div class="form-grid">
            <label class="field field-wide">
                <span>Tytuł *</span>
                <input name="title" value="<?= e($values['title']) ?>" maxlength="120" required
                       aria-invalid="<?= isset($errors['title']) ? 'true' : 'false' ?>">
                <small><?= e($errors['title'] ?? '3–120 znaków') ?></small>
            </label>

            <label class="field">
                <span>Zgłaszający *</span>
                <input name="requester" value="<?= e($values['requester']) ?>" maxlength="80" required
                       aria-invalid="<?= isset($errors['requester']) ? 'true' : 'false' ?>">
                <small><?= e($errors['requester'] ?? 'Imię i nazwisko') ?></small>
            </label>

            <label class="field">
                <span>Kategoria *</span>
                <select name="category" aria-invalid="<?= isset($errors['category']) ? 'true' : 'false' ?>">
                    <?php foreach (['hardware', 'software', 'access', 'other'] as $option): ?>
                        <option value="<?= e($option) ?>" <?= $values['category'] === $option ? 'selected' : '' ?>><?= e(label_for('category', $option)) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category'])): ?><small><?= e($errors['category']) ?></small><?php endif; ?>
            </label>

            <label class="field">
                <span>Priorytet *</span>
                <select name="priority" aria-invalid="<?= isset($errors['priority']) ? 'true' : 'false' ?>">
                    <?php foreach (['low', 'normal', 'high'] as $option): ?>
                        <option value="<?= e($option) ?>" <?= $values['priority'] === $option ? 'selected' : '' ?>><?= e(label_for('priority', $option)) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['priority'])): ?><small><?= e($errors['priority']) ?></small><?php endif; ?>
            </label>

            <label class="field">
                <span>Status *</span>
                <select name="status" aria-invalid="<?= isset($errors['status']) ? 'true' : 'false' ?>">
                    <?php foreach (['new', 'in_progress', 'done'] as $option): ?>
                        <option value="<?= e($option) ?>" <?= $values['status'] === $option ? 'selected' : '' ?>><?= e(label_for('status', $option)) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['status'])): ?><small><?= e($errors['status']) ?></small><?php endif; ?>
            </label>

            <label class="field field-wide">
                <span>Opis</span>
                <textarea name="description" rows="5" maxlength="1500"
                          aria-invalid="<?= isset($errors['description']) ? 'true' : 'false' ?>"><?= e($values['description']) ?></textarea>
                <small><?= e($errors['description'] ?? 'Opcjonalnie, do 1500 znaków') ?></small>
            </label>
        </div>

        <div class="form-actions">
            <a class="button" href="/">Anuluj</a>
            <button class="button button-primary" type="submit"><?= $id === null ? 'Dodaj' : 'Zapisz' ?></button>
        </div>
    </form>
</div>
