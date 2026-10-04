<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Manga\Responses\MangaListItemData;

/** @var ViewData $view */
/** @var list<MangaListItemData> $mangas */

$isSerieView ??= false;
$upcoming ??= [];

?>

<div class="collection-ajax-content">

    <?php if ($mangas === []): ?>

        <p class="collection-empty">
            Aucun manga trouvé.
        </p>

    </div>

    <?php return; endif; ?>

    <section class="collection-grid u-grid u-justify-center">

        <?php if ($isSerieView): ?>

            <?php foreach ($upcoming as $release): ?>

                <a
                    class="card collection-card collection-card-link collection-card-upcoming u-flex u-w-full"
                    href="<?= e($release->sourceUrl) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="<?= e('Tome ' . $release->number . ' à paraître le ' . $release->dateLabel) ?>"
                >
                    <span class="collection-status-badge collection-status-upcoming">À paraître</span>
                    <div class="card-image-box-portrait u-row-center u-clip">
                        <?php if ($release->imageUrl !== null): ?>
                            <img class="card-image-portrait u-block u-w-full" src="<?= e(image_url($release->imageUrl)) ?>" alt="<?= e('Tome ' . $release->number) ?>" loading="lazy" decoding="async">
                        <?php else: ?>
                            <span class="collection-upcoming-placeholder u-row-center u-w-full">Tome <?= $release->number ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="collection-card-title u-block u-relative u-text-center u-clip u-bold"><?= e($mangas[0]->livre) ?></p>
                    <p class="collection-card-subtitle u-relative u-text-center">Tome <?= $release->number ?></p>
                    <time class="collection-release-date" datetime="<?= e($release->date) ?>"><?= e($release->dateLabel) ?></time>
                </a>

            <?php endforeach; ?>

        <?php endif; ?>

        <?php foreach ($mangas as $manga): ?>

            <?php

            $slug = $manga->slug;
            $numero = $manga->numero;
            $livre = $manga->livre;
            $thumbnailPath = $manga->thumbnailUrl;

            if ($slug === '' || $livre === '' || $thumbnailPath === null)
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
                    . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);
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
                    number_format($displayNote, 1, ',', '');
            }

            ?>

            <a
                class="
                    card
                    transition-card
                    card-link
                    collection-card
                    collection-card-link
                 u-flex u-w-full"
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

                <div class="card-image-box-portrait u-row-center u-clip">

                    <img
                        class="card-image-portrait u-block u-w-full"
                        src="<?= e(image_url($thumbnailPath, true)) ?>"
                        alt="<?= e($livre) ?>"
                        loading="lazy"
                        decoding="async"
                        draggable="false"
                    >

                </div>

                <p class="collection-card-title u-block u-relative u-text-center u-clip u-bold">
                    <?= e($livre) ?>
                </p>

                <p class="collection-card-subtitle u-relative u-text-center">
                    <?= e($subtitle) ?>
                </p>

            </a>

        <?php endforeach; ?>

    </section>

</div>
