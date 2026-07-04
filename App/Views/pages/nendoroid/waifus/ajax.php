<?php

declare(strict_types=1);

/** @var array<int, object> $nendoroids */

?>

<div class="collection-ajax-content">

<?php if ($nendoroids === []): ?>

    <p class="collection-empty">
        Aucun Nendoroid trouvé.
    </p>

</div>

<?php return; endif; ?>

<section class="collection-grid">

<?php foreach ($nendoroids as $nendoroid):

    $slug =
        (string) ($nendoroid->slug ?? '');

    $waifu =
        (string) ($nendoroid->waifu ?? '');

    $company =
        (string) ($nendoroid->company ?? '');

    $thumbnail =
        (string) ($nendoroid->thumbnail ?? '');

    $extension =
        (string) ($nendoroid->extension ?? '');

    if (
        $slug === ''
        || $waifu === ''
        || $thumbnail === ''
        || $extension === ''
    ) {
        continue;
    }

    $numero =
        (int) ($nendoroid->numero ?? 1);

    $href =
        "{$view->baseUri}nendoroid/waifus/{$slug}/{$numero}";

    $thumbnailPath =
        "{$view->baseUri}images/nendoroid/thumbnail/{$thumbnail}.{$extension}";

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