<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>

    <link
        rel="shortcut icon"
        href="<?= $basePath ?>public/images/favicon/favicon.png">

    <link
        rel="stylesheet"
        href="<?= $basePath ?>public/css/app.css">
</head>

<body>

    <?php require_once ROOT . '/Views/partials/header.php'; ?>

    <main>
        <?= $content ?>
    </main>

    <div
        id="toast"
        class="toast"
        aria-live="polite"
        aria-atomic="true"></div>

    <script type="module" src="<?= $basePath ?>public/js/app.js"></script>

    <?php if (!empty($_SESSION['success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () =>
            {
                if (window.showToast)
                {
                    window.showToast(
                        <?= json_encode($_SESSION['success']) ?>,
                        'success'
                    );
                }
            });
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () =>
            {
                if (window.showToast)
                {
                    window.showToast(
                        <?= json_encode($_SESSION['error']) ?>,
                        'error'
                    );
                }
            });
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

</body>
</html>