<div class="form-page">
    <a class="back-link" href="/">← Wróć do listy</a>
    <h1>Import z CSV</h1>
    <p>Plik pochodzi z przykładowego systemu dostawcy. Zgłoszenia o tym samym ID zewnętrznym zostaną zaktualizowane.</p>

    <?php if (is_string($importError) && $importError !== ''): ?>
        <p class="form-error" role="alert"><?= e($importError) ?></p>
    <?php endif; ?>

    <form class="form-card" method="post" action="/?action=import" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <label class="field">
            <span>Plik CSV</span>
            <input type="file" name="csv" accept=".csv,text/csv" required>
            <small>UTF-8, separator „;”, do 1 MB i 500 wierszy.</small>
        </label>
        <div class="form-actions"><button class="button button-primary" type="submit">Importuj</button></div>
    </form>

    <p class="help-text">Do sprawdzenia importu użyj <code>examples/vendor_export.csv</code>. Mapowanie kolumn i kodów dostawcy jest w <code>config/vendor.php</code>.</p>
</div>
