<?php

declare(strict_types=1);

/** @var list<App\Models\Artbook> $artbooks */

$baseUri =
    rtrim(
        $baseUri ?? '',
        '/',
    ) . '/';

?>

<div class="collection-ajax-content">

<?php if ($artbooks === []): ?>

    <p class="collection-empty">
        Aucun artbook trouvé.
    </p>

</div>

<?php return; endif; ?>

<section class="collection-grid">

<?php foreach ($artbooks as $artbook):

    $slug =
        (string) ($artbook->slug ?? '');

    $title =
        (string) ($artbook->artbook ?? '');

    $author =
        (string) ($artbook->auteur ?? '');

    $serie =
        (string) ($artbook->serie ?? '');

    $thumbnail =
        (string) ($artbook->thumbnail ?? '');

    $extension =
        (string) ($artbook->extension ?? '');

    if (
        $slug === ''
        || $title === ''
        || $thumbnail === ''
        || $extension === ''
    ) {
        continue;
    }

    $thumbnailPath =
        "{$baseUri}images/artbooks/thumbnail/{$thumbnail}.{$extension}";

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
    href="<?= e($baseUri) ?>manga/artbooks/<?= e($slug) ?>"
>

    <div class="card-image-box-portrait">

        <img
            class="card-image-portrait"
            src="<?= e($thumbnailPath) ?>"
            alt="<?= e($title) ?>"
            loading="lazy"
            draggable="false"
        >

    </div>

    <p class="collection-card-title">
        <?= e($title) ?>
    </p>

    <p class="collection-card-subtitle">

        <?php if ($author !== ''): ?>

            <?= e($author) ?>

        <?php elseif ($serie !== ''): ?>

            <?= e($serie) ?>

        <?php else: ?>

            Artbook

        <?php endif; ?>

    </p>

</a>

<?php endforeach; ?>

</section>

</div>