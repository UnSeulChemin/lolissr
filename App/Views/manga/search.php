<?php

declare(strict_types=1);

$search = isset($view['search'])
    ? (string) $view['search']
    : '';

$mangas = isset($view['mangas'])
    && is_array($view['mangas'])
        ? $view['mangas']
        : [];

$baseUri = rtrim(
    (string) ($baseUri ?? ''),
    '/',
) . '/';

?>

<section class="layout-container">

    <h1 class="home-section-title">
        🔎 Résultats de recherche
    </h1>

    <?php if ($search === ''): ?>

        <p
            class="home-empty"
            style="text-align:center;">

            Saisissez un titre dans la barre de recherche.

        </p>

    <?php elseif ($mangas === []): ?>

        <p
            class="home-empty"
            style="text-align:center;">

            Aucun manga trouvé.

        </p>

    <?php else: ?>

        <section class="collection-grid animate-fade-up-stagger">

            <?php foreach ($mangas as $manga): ?>

                <?php
                $slug = isset($manga->slug)
                    ? (string) $manga->slug
                    : '';

                $numero = isset($manga->numero)
                    ? (int) $manga->numero
                    : 0;

                $thumbnail = isset($manga->thumbnail)
                    ? (string) $manga->thumbnail
                    : '';

                $extension = isset($manga->extension)
                    ? (string) $manga->extension
                    : '';

                $livre = isset($manga->livre)
                    ? (string) $manga->livre
                    : '';

                $note = $manga->note ?? null;

                if (
                    $slug === ''
                    || $thumbnail === ''
                    || $extension === ''
                    || $livre === ''
                ) {
                    continue;
                }

                $noteClass = 'collection-note-mid';

                if ($note !== null) {
                    $noteValue = (int) $note;

                    if ($noteValue >= 8) {
                        $noteClass = 'collection-note-good';
                    } elseif ($noteValue <= 4) {
                        $noteClass = 'collection-note-low';
                    }
                }

                $href = $baseUri
                    . 'manga/series/'
                    . rawurlencode($slug)
                    . '/'
                    . $numero;

                $thumbnailPath = $baseUri
                    . 'images/mangas/thumbnail/'
                    . $thumbnail
                    . '.'
                    . $extension;
                ?>

                <a
                    class="collection-card-link"
                    href="<?= e($href) ?>">

                    <article class="card collection-card">

                        <div class="card-image-box-portrait">

                            <img
                                class="card-image-portrait card-image"
                                src="<?= e($thumbnailPath) ?>"
                                alt="<?= e($livre) ?>">

                        </div>

                        <p class="collection-card-title">

                            <?= e($livre) ?>

                        </p>

                        <p class="collection-card-subtitle">

                            Tome <?= $numero ?>

                        </p>

                        <?php if ($note !== null): ?>

                            <span
                                class="collection-card-badge collection-card-badge-note <?= e($noteClass) ?>">

                                ⭐ <?= (int) $note ?>

                            </span>

                        <?php endif; ?>

                    </article>

                </a>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>

</section>