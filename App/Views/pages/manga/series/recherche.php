<?php

declare(strict_types=1);

use App\DTO\Manga\Responses\MangaSearchItemData;

/** @var list<MangaSearchItemData> $mangas */
/** @var string $search */

$baseUri = view_base_uri();

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <h1
            class="
                home-section-title
                transition-card
            "
        >
            🔎 Résultats de recherche
        </h1>

        <?php if ($search === ''): ?>

            <p class="collection-empty">
                Saisissez un titre dans la barre de recherche.
            </p>

        <?php elseif ($mangas === []): ?>

            <p class="collection-empty">
                Aucun manga trouvé.
            </p>

        <?php else: ?>

            <div class="collection-ajax-content">

                <section class="collection-grid">

                    <?php foreach ($mangas as $manga): ?>

                        <?php

                        $href =
                            $baseUri
                            . 'manga/series/'
                            . rawurlencode($manga->slug)
                            . '/'
                            . $manga->numero;

                        $thumbnailPath =
                            $baseUri
                            . 'images/manga/thumbnail/'
                            . $manga->thumbnail
                            . '.'
                            . $manga->extension;

                        $noteClass =
                            'collection-note-mid';

                        if ($manga->note !== null)
                        {
                            if ($manga->note >= 8)
                            {
                                $noteClass = 'collection-note-good glow-red';
                            }
                            elseif ($manga->note <= 4)
                            {
                                $noteClass = 'collection-note-low';
                            }
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

                        <?php if ($manga->note !== null): ?>

                            <span
                                class="
                                    collection-card-badge
                                    <?= e($noteClass) ?>
                                "
                            >
                                ⭐ <?= (int) $manga->note ?>/10
                            </span>

                        <?php endif; ?>

                            <span
                                class="
                                    collection-read-badge
                                    <?= $manga->lu ? 'active glow-blue' : '' ?>
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
                                    alt="<?= e($manga->livre) ?>"
                                    loading="lazy"
                                    draggable="false"
                                >

                            </div>

                            <p class="collection-card-title">
                                <?= e($manga->livre) ?>
                            </p>

                            <p class="collection-card-subtitle">

                                Tome <?= str_pad(
                                    (string) $manga->numero,
                                    2,
                                    '0',
                                    STR_PAD_LEFT,
                                ) ?>

                            </p>

                        </a>

                    <?php endforeach; ?>

                </section>

            </div>

        <?php endif; ?>

    </div>

</section>