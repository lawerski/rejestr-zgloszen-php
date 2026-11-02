<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Rejestr zgłoszeń</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="site-name" href="/">Rejestr zgłoszeń</a>
            <nav aria-label="Nawigacja">
                <a href="/">Lista</a>
                <a href="/?page=import">Import CSV</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <?php if (is_string($flash) && $flash !== ''): ?>
            <p class="notice" role="status"><?= e($flash) ?></p>
        <?php endif; ?>
        <?php require $view; ?>
    </main>
</body>
</html>
