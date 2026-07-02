<?php

declare(strict_types=1);

/** @var array<int, object> $figurine */

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

    $origin =
        (string) ($figurine->origin ?? '');

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

    $numero =
        (int) ($figurine->numero ?? 1);

    $href =
        "{$baseUri}figurine/waifus/{$slug}/{$numero}";

    $thumbnailPath =
        "{$baseUri}images/figurine/thumbnail/{$thumbnail}.{$extension}";

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
        <?= $origin !== ''
            ? e($origin)
            : e($waifu)
        ?>
    </p>

    <p class="collection-card-subtitle">
        <?= e($waifu) ?>
    </p>

</a>

<?php endforeach; ?>

</section>

</div>