<?php

declare(strict_types=1);

/** @var array<int, object> $figurines */

$baseUri =
    rtrim(
        $baseUri ?? '',
        '/',
    ) . '/';

?>

<div class="collection-ajax-content">

<?php if ($figurines === []): ?>

    <p class="collection-empty">
        Aucune figurine trouvée.
    </p>

</div>

<?php return; endif; ?>

<section class="collection-grid">

<?php foreach ($figurines as $figurine):

    $slug =
        (string) ($figurine->slug ?? '');

    $waifu =
        (string) ($figurine->waifu ?? '');

    $company =
        (string) ($figurine->company ?? '');

    $thumbnail =
        (string) ($figurine->thumbnail ?? '');

    $extension =
        (string) ($figurine->extension ?? '');

    if (
        $slug === ''
        || $waifu === ''
        || $thumbnail === ''
        || $extension === ''
    ) {
        continue;
    }

    $href =
        "{$baseUri}figurines/waifus/{$slug}";

    $thumbnailPath =
        "{$baseUri}images/figurines/thumbnail/{$thumbnail}.{$extension}";

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