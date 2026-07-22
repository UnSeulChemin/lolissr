<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;

use Framework\Security\ContentSecurityPolicy;

/** @var ViewData $view */

$title = is_string($title ?? null) ? $title : '';
$content = is_string($content ?? null) ? $content : '';

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta http-equiv="x-dns-prefetch-control" content="off">

    <meta name="referrer" content="no-referrer">

    <?= csrf_meta_tag() ?>

    <title><?= e($title) ?></title>

    <link rel="shortcut icon" href="<?= e($view->baseUri) ?>images/favicon/favicon.png">

    <link rel="stylesheet" href="<?= e($view->baseUri) ?>css/app.css">

</head>

<body>

    <?php require_once view_path('layouts/header.php'); ?>

    <main class="app-content">

        <?= $content ?>

    </main>

    <div id="toast" class="toast" aria-live="polite" aria-atomic="true"></div>

    <script nonce="<?= ContentSecurityPolicy::escapedNonce() ?>">

        window.appConfig = Object.freeze({
            baseUri: <?= json_encode(
                $view->baseUri,
                JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
                | JSON_THROW_ON_ERROR
            ) ?>,
        });

        window.csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

        window.flashToast = <?= json_encode(
            $view->toast,
            JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
            | JSON_THROW_ON_ERROR
        ) ?>;

    </script>

    <script type="module" src="<?= e($view->baseUri) ?>js/app.js"></script>

</body>

</html>