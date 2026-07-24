<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Manga\Responses\MangaSeriesItemData;

/** @var ViewData $view */
/** @var list<MangaSeriesItemData> $mangas */

$isSerieView ??= false;

?>

<div class="collection-ajax-content">

    <?php if ($mangas === []): ?>

        <p class="collection-empty">
            Aucun manga trouvé.
        </p>

    </div>

    <?php return; endif; ?>

    <section class="collection-grid">

        <?php foreach ($mangas as $manga): ?>

            <?php

            $slug = $manga->slug;
            $numero = $manga->numero;
            $livre = $manga->livre;
            $thumbnailPath = $manga->thumbnailUrl;

            if (
                $slug === ''
                || $livre === ''
                || $thumbnailPath === null
            )
            {
                continue;
            }

            if ($isSerieView)
            {
                $href =
                    "{$view->baseUri}manga/series/{$slug}/{$numero}";

                $displayNote =
                    $manga->note;

                $showReadBadge =
                    $manga->lu;

                $subtitle =
                    'Tome '
                    . str_pad(
                        (string) $numero,
                        2,
                        '0',
                        STR_PAD_LEFT,
                    );
            }
            else
            {
                $href =
                    "{$view->baseUri}manga/series/{$slug}";

                $displayNote =
                    $manga->averageNote;

                $showReadBadge =
                    $manga->isFullyRead;

                $subtitle =
                    "{$manga->total} tomes";
            }

            $noteClass =
                'collection-note-mid';

            if ($displayNote !== null)
            {
                if ($displayNote >= 8)
                {
                    $noteClass =
                        'collection-note-good glow-red';
                }
                elseif ($displayNote <= 4)
                {
                    $noteClass =
                        'collection-note-low';
                }
            }

            if ($displayNote === null)
            {
                $noteLabel = '0';
            }
            elseif ($isSerieView)
            {
                $noteLabel =
                    (string) ((int) $displayNote);
            }
            else
            {
                $noteLabel =
                    number_format(
                        $displayNote,
                        1,
                        ',',
                        '',
                    );
            }

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

                <?php if (! $isSerieView): ?>

                    <span
                        class="collection-status-badge <?= e($manga->statusClass) ?>"
                    >
                        <?= e($manga->statusLabel) ?>
                    </span>

                <?php endif; ?>

                <span
                    class="collection-card-badge <?= e($noteClass) ?>"
                >
                    ⭐ <?= e($noteLabel) ?>/10
                </span>

                <span
                    class="collection-read-badge <?= $showReadBadge ? 'active glow-blue' : '' ?>"
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
                        decoding="async"
                        draggable="false"
                    >

                </div>

                <p class="collection-card-title">
                    <?= e($livre) ?>
                </p>

                <p class="collection-card-subtitle">
                    <?= e($subtitle) ?>
                </p>

            </a>

        <?php endforeach; ?>

    </section>

</div>