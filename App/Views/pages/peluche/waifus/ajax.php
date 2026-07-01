<?php

declare(strict_types=1);

/** @var array<int, object> $peluches */

$baseUri =
    rtrim(
        $baseUri ?? '',
        '/',
    ) . '/';

?>

<div class="collection-ajax-content">

<?php if ($peluches === []): ?>

    <p class="collection-empty">
        Aucune peluche trouvée.
    </p>

</div>

<?php return; endif; ?>

<section class="collection-grid">

<?php foreach ($peluches as $peluche):

    $slug =
        (string) ($peluche->slug ?? '');

    $waifu =
        (string) ($peluche->waifu ?? '');

    $company =
        (string) ($peluche->company ?? '');

    $thumbnail =
        (string) ($peluche->thumbnail ?? '');

    $extension =
        (string) ($peluche->extension ?? '');

    if (
        $slug === ''
        || $waifu === ''
        || $thumbnail === ''
        || $extension === ''
    ) {
        continue;
    }

    $numero =
        (int) ($peluche->numero ?? 1);

    $href =
        "{$baseUri}peluche/waifus/{$slug}/{$numero}";

    $thumbnailPath =
        "{$baseUri}images/peluche/thumbnail/{$thumbnail}.{$extension}";

?>

<a
    class="
        card
        transition-card
        card-link
        collection-card
        collection-card-link
    "
    data-prefetch
    href="<?= e($href) ?>"
>

    <div class="card-image-box-portrait">

        <img
            class="card-image-portrait"
            src="<?= e($thumbnailPath) ?>"
            alt="<?= e($waifu) ?>"
            loading="lazy"
            draggable="false"
        >

    </div>

    <p class="collection-card-title">
        <?= e($waifu) ?>
    </p>

    <p class="collection-card-subtitle">
        <?= e($company) ?>
    </p>

</a>

<?php endforeach; ?>

</section>

</div>