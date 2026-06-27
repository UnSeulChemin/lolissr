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
        $artbook->slug;

    $title =
        $artbook->artbook;

    $author =
        $artbook->auteur;

    $serie =
        $artbook->serie;

    $thumbnail =
        $artbook->thumbnail;

    $extension =
        $artbook->extension;

    $numero =
        $artbook->numero;

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

    $subtitle =
        $serie !== null
        && trim($serie) !== ''
            ? $serie
            : (
                $author !== null
                && trim($author) !== ''
                    ? $author
                    : 'Artbook'
            );

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
    href="<?= e($baseUri) ?>manga/artbooks/<?= e($slug) ?>/<?= $numero ?>"
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
        <?= e($subtitle) ?>
    </p>

</a>

<?php endforeach; ?>

</section>

</div>