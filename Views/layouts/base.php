<?php

$flashToast = null;

if (!empty($_SESSION['success']))
{
    $flashToast = [
        'message' => $_SESSION['success'],
        'type' => 'success'
    ];

    unset($_SESSION['success']);
}
elseif (!empty($_SESSION['error']))
{
    $flashToast = [
        'message' => $_SESSION['error'],
        'type' => 'error'
    ];

    unset($_SESSION['error']);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="shortcut icon" href="<?= $basePath ?>public/images/favicon/favicon.png">
    <link rel="stylesheet" href="<?= $basePath ?>public/css/app.css">
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

    <?php if ($flashToast !== null): ?>
        <script>
            window.flashToast = <?= json_encode($flashToast, JSON_UNESCAPED_UNICODE) ?>;
        </script>
    <?php endif; ?>

    <script type="module" src="<?= $basePath ?>public/js/app.js"></script>

</body>
</html>