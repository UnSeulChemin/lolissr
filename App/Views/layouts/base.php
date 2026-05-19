<?php

declare(strict_types=1);

$basePath = rtrim($basePath, '/') . '/';

$flashToast = null;

if (!empty($_SESSION['success']))
{
    $flashToast = [
        'message' => (string) $_SESSION['success'],
        'type' => 'success',
    ];

    unset($_SESSION['success']);
}
elseif (!empty($_SESSION['error']))
{
    $flashToast = [
        'message' => (string) $_SESSION['error'],
        'type' => 'error',
    ];

    unset($_SESSION['error']);
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?= csrf_meta_tag() ?>

    <title><?= e($title) ?></title>

    <link rel="shortcut icon" href="<?= e($basePath) ?>public/images/favicon/favicon.png">

    <link rel="stylesheet" href="<?= e($basePath) ?>public/css/app.css">

</head>

<body>

    <?php require_once view_path('partials/header.php'); ?>

    <main><?= $content ?></main>

    <div
        id="toast"
        class="toast"
        aria-live="polite"
        aria-atomic="true"></div>

    <script>
        window.csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';
    </script>

    <?php if ($flashToast !== null): ?>

        <script>
            window.flashToast = <?= json_encode(
                $flashToast,
                JSON_UNESCAPED_UNICODE
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
            ) ?>;
        </script>

    <?php endif; ?>

    <script type="module" src="<?= e($basePath) ?>public/js/core/app.js"></script>

</body>

</html>