<?php

declare(strict_types=1);

/** @var array<int, object> $mangas */
/** @var bool $isSerieView */
/** @var string $baseUri */

$isSerieView =
    (bool) $isSerieView;

$baseUri =
    rtrim(
        $baseUri,
        '/',
    ) . '/';

?>

<div class="collection-ajax-content">

<?php if ($mangas === []): ?>

    <p class="collection-empty">
        Aucun manga trouvé.
    </p>

</div>

<?php return; endif; ?>

<section class="collection-grid">

<?php foreach ($mangas as $manga):

    $slug =
        isset($manga->slug)
            ? (string) $manga->slug
            : '';

    $numero =
        isset($manga->numero)
            ? (int) $manga->numero
            : 0;

    $livre =
        isset($manga->livre)
            ? (string) $manga->livre
            : '';

    $thumbnail =
        isset($manga->thumbnail)
            ? (string) $manga->thumbnail
            : '';

    $extension =
        isset($manga->extension)
            ? (string) $manga->extension
            : '';

    $statut =
        isset($manga->statut)
            ? (string) $manga->statut
            : 'en_cours';

    $note =
        isset($manga->note)
            ? (float) $manga->note
            : null;

    $averageNote =
        isset($manga->averageNote)
            ? (float) $manga->averageNote
            : (
                isset($manga->average_note)
                    ? (float) $manga->average_note
                    : null
            );

    $total =
        isset($manga->total)
            ? (int) $manga->total
            : 0;

    $totalLu =
        isset($manga->totalLu)
            ? (int) $manga->totalLu
            : 0;

    $isFullyRead =
        $total > 0
        && $totalLu >= $total;

    if (
        $slug === ''
        || $livre === ''
        || $thumbnail === ''
        || $extension === ''
    ) {
        continue;
    }

    $href =
        $isSerieView
            ? "{$baseUri}manga/series/{$slug}/{$numero}"
            : "{$baseUri}manga/series/{$slug}";

    $thumbnailPath =
        "{$baseUri}images/mangas/thumbnail/{$thumbnail}.{$extension}";

    $displayNote =
        $isSerieView
            ? $note
            : $averageNote;

    $noteClass =
        'collection-note-mid';

    if ($displayNote !== null) {

        if ($displayNote >= 8) {

            $noteClass =
                'collection-note-good glow-red';

        } elseif ($displayNote <= 4) {

            $noteClass =
                'collection-note-low';
        }
    }

    $noteLabel =
        $displayNote !== null
            ? (
                $isSerieView
                    ? (string) ((int) $displayNote)
                    : number_format(
                        $displayNote,
                        1,
                        ',',
                        '',
                    )
            )
            : '0';

    $statusLabel =
        $statut === 'termine'
            ? 'Terminé'
            : 'En cours';

    $statusClass =
        $statut === 'termine'
            ? 'collection-status-finished'
            : 'collection-status-progress';

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

    <?php if ($isSerieView === false): ?>

        <span
            class="
                collection-status-badge
                <?= e($statusClass) ?>
            "
        >
            <?= e($statusLabel) ?>
        </span>

    <?php endif; ?>

    <span
        class="
            collection-card-badge
            <?= e($noteClass) ?>
        "
    >
        ⭐ <?= e($noteLabel) ?>/10
    </span>

    <span
        class="
            collection-read-badge
            <?= $isFullyRead ? 'active glow-blue' : '' ?>
        "
    >

        <svg
            class="collection-read-icon"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <path d="M7 3C6.45 3 6 3.45 6 4V21L12 17L18 21V4C18 3.45 17.55 3 17 3H7Z"/>
        </svg>

    </span>

    <div class="card-image-box-portrait">

        <img
            class="card-image-portrait"
            src="<?= e($thumbnailPath) ?>"
            alt="<?= e($livre) ?>"
            loading="lazy"
            draggable="false"
        >

    </div>

    <p class="collection-card-title">
        <?= e($livre) ?>
    </p>

    <p class="collection-card-subtitle">

        <?= $isSerieView
                ? 'Tome '
                    . str_pad(
                        (string) $numero,
                        2,
                        '0',
                        STR_PAD_LEFT,
                    )
                : $total . ' tomes'
    ?>

    </p>

</a>

<?php endforeach; ?>

</section>

</div>