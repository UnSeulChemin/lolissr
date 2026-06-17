<?php

declare(strict_types=1);

use Framework\Support\Session;

// =========================================
// BASE URI
// =========================================

$baseUri = trim(
    (string) ($baseUri ?? ''),
    '/',
);

$baseUri =
    $baseUri !== ''
        ? '/' . $baseUri . '/'
        : '/';

// =========================================
// DEFAULT VARIABLES
// =========================================

$title =
    is_string($title ?? null)
        ? $title
        : '';

$content =
    is_string($content ?? null)
        ? $content
        : '';

// =========================================
// FLASH TOAST
// =========================================

$flashToast = null;

$success =
    Session::pull('success');

$error =
    Session::pull('error');

if (
    is_string($success)
    && $success !== ''
) {

    $flashToast = [
        'message' => $success,
        'type' => 'success',
    ];

} elseif (
    is_string($error)
    && $error !== ''
) {

    $flashToast = [
        'message' => $error,
        'type' => 'error',
    ];
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta
        http-equiv="x-dns-prefetch-control"
        content="off">

    <meta
        name="referrer"
        content="no-referrer">

    <?= csrf_meta_tag() ?>

    <title>
        <?= e($title) ?>
    </title>

    <link
        rel="shortcut icon"
        href="<?= e($baseUri) ?>images/favicon/favicon.png">

    <link
        rel="stylesheet"
        href="<?= e($baseUri) ?>css/app.css">

</head>

<body>

    <?php require_once view_path(
        'layouts/header.php',
    ); ?>

    <main class="app-content">
        <?= $content ?>
    </main>

    <div
        id="toast"
        class="toast"
        aria-live="polite"
        aria-atomic="true">
    </div>

    <script>

        // =================================
        // CSRF
        // =================================

        window.csrfToken = document
            .querySelector(
                'meta[name="csrf-token"]',
            )
            ?.getAttribute(
                'content',
            )
            || '';

    </script>

    <?php if ($flashToast !== null): ?>

        <script>

            window.flashToast =
                <?= json_encode(
                    $flashToast,
                    JSON_UNESCAPED_UNICODE
                    | JSON_HEX_TAG
                    | JSON_HEX_AMP
                    | JSON_HEX_APOS
                    | JSON_HEX_QUOT
                    | JSON_THROW_ON_ERROR,
                ) ?>;

        </script>

    <?php endif; ?>

    <script
        type="module"
        src="<?= e($baseUri) ?>js/app.js">
    </script>

</body>

</html>