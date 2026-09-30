<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\Support\AssetVersions;

use Framework\Security\ContentSecurityPolicy;

/** @var ViewData $view */
/** @var list<string> $pageStylesheets */

$title = is_string($title ?? null) ? $title : '';
$content = is_string($content ?? null) ? $content : '';
$useJavaScriptBundle = \Framework\Application\App::isProduction();
/** @var array{entry: string, preloads: list<string>} $javascript */
$javascript = $useJavaScriptBundle
    ? config('javascript')
    : ['entry' => 'js/app.js', 'preloads' => []];
$commonCss = \Framework\Application\App::isProduction() ? 'app.bundle.css' : 'app.css';
$commonCssPath = dirname(__DIR__, 3) . '/public/css/' . $commonCss;
if (! is_file($commonCssPath))
{
    $commonCss = 'app.css';
    $commonCssPath = dirname(__DIR__, 3) . '/public/css/app.css';
}

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

    <link rel="stylesheet" href="<?= e($view->baseUri) ?>css/<?= e($commonCss) ?>?v=<?= e(AssetVersions::version('css/' . $commonCss)) ?>">

    <?php foreach ($pageStylesheets as $stylesheet): ?>
        <link rel="stylesheet" href="<?= e($stylesheet) ?>" data-page-style>
    <?php endforeach; ?>

    <?php foreach ($javascript['preloads'] as $module): ?>
        <link rel="modulepreload" href="<?= e($view->baseUri . $module) ?>">
    <?php endforeach; ?>

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

    <?php if (!$useJavaScriptBundle): ?>
    <script type="importmap" nonce="<?= ContentSecurityPolicy::escapedNonce() ?>">
        <?= json_encode(['imports' => [
            $view->baseUri . 'js/core/modal/avatar-modal.js' =>
                $view->baseUri . 'js/core/modal/avatar-modal.js?v=' . AssetVersions::version('js/core/modal/avatar-modal.js'),
            $view->baseUri . 'js/core/modal/banner-modal.js' =>
                $view->baseUri . 'js/core/modal/banner-modal.js?v=' . AssetVersions::version('js/core/modal/banner-modal.js'),
            $view->baseUri . 'js/profil/profile-customization.js' =>
                $view->baseUri . 'js/profil/profile-customization.js?v=' . AssetVersions::version('js/profil/profile-customization.js'),
            $view->baseUri . 'js/core/modal/title-modal.js' =>
                $view->baseUri . 'js/core/modal/title-modal.js?v=' . AssetVersions::version('js/core/modal/title-modal.js'),
            $view->baseUri . 'js/core/modal/frame-modal.js' =>
                $view->baseUri . 'js/core/modal/frame-modal.js?v=' . AssetVersions::version('js/core/modal/frame-modal.js'),
        ]], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>
    </script>
    <?php endif; ?>

    <script type="module" src="<?= e($view->baseUri . $javascript['entry']) ?>?v=<?= e(AssetVersions::version($javascript['entry'])) ?>"></script>

</body>

</html>
