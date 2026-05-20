<?php

declare(strict_types=1);

use App\Core\Support\Session;

$basePath = rtrim(
    (string) ($basePath ?? ''),
    '/'
) . '/';

$flashToast = null;

$success = Session::get('success');
$error = Session::get('error');

if (is_string($success) && $success !== '') {
    $flashToast = [
        'message' => $success,
        'type' => 'success',
    ];

    Session::remove('success');
} elseif (is_string($error) && $error !== '') {
    $flashToast = [
        'message' => $error,
        'type' => 'error',
    ];

    Session::remove('error');
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <?= csrf_meta_tag() ?>

    <title><?= e($title ?? '') ?></title>

    <link
        rel="shortcut icon"
        href="<?= e($basePath) ?>public/images/favicon/favicon.png">

    <link
        rel="stylesheet"
        href="<?= e($basePath) ?>public/css/app.css">

</head>

<body>

    <?php require_once view_path('partials/header.php'); ?>

    <main><?= $content ?? '' ?></main>

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
                | JSON_THROW_ON_ERROR
            ) ?>;
        </script>

    <?php endif; ?>

    <script
        type="module"
        src="<?= e($basePath) ?>public/js/core/app.js"></script>

</body>

</html>