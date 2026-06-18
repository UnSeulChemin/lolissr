<?php

declare(strict_types=1);

$search =
    (string) ($search ?? '');

$mangas =
    is_array($mangas ?? null)
        ? $mangas
        : [];

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

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

                        $note =
                            isset($manga->note)
                                ? (float) $manga->note
                                : null;

                        $isRead = (int) $manga->lu === 1;

                        if (
                            $slug === ''
                            || $livre === ''
                            || $thumbnail === ''
                            || $extension === ''
                        ) {
                            continue;
                        }

                        $href =
                            $baseUri
                            . 'manga/series/'
                            . rawurlencode($slug)
                            . '/'
                            . $numero;

                        $thumbnailPath =
                            $baseUri
                            . 'images/mangas/thumbnail/'
                            . $thumbnail
                            . '.'
                            . $extension;

                        $noteClass =
                            'collection-note-mid';

                        if ($note !== null)
                        {
                            if ($note >= 8)
                            {
                                $noteClass =
                                    'collection-note-good glow-red';
                            }
                            elseif ($note <= 4)
                            {
                                $noteClass =
                                    'collection-note-low';
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

                            <?php if ($note !== null): ?>

                                <span
                                    class="
                                        collection-card-badge
                                        <?= e($noteClass) ?>
                                    "
                                >
                                    ⭐ <?= (int) $note ?>/10
                                </span>

                            <?php endif; ?>

                            <span
                                class="
                                    collection-read-badge
                                    <?= $isRead ? 'active glow-blue' : '' ?>
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

                                Tome <?= str_pad(
                                    (string) $numero,
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