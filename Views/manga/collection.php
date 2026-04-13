<?php
$isCollection = isset($slugFilter) && !empty($slugFilter);
$currentPage = $currentPage ?? 1;
?>

<section class="layout-container">

    <section class="manga-grid">

        <?php foreach ($mangas as $manga): ?>

            <article class="manga-card">

                <figure class="manga-card-figure">

                    <a
                        class="manga-card-link"
                        href="<?= $isCollection
                            ? $basePath . 'manga/' . rawurlencode($manga->slug) . '/' . (int) $manga->numero
                            : $basePath . 'manga/serie/' . rawurlencode($manga->slug) ?>">

                        <img
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>"
                            alt="<?= htmlspecialchars($manga->livre) ?>">
                    </a>

                    <?php if (!$isCollection): ?>
                        <span class="manga-card-badge-count">
                            <?= (int) $manga->total ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($isCollection && $manga->note !== null): ?>
                        <?php
                        $noteClass = 'manga-note-mid';

                        if ((int) $manga->note >= 8)
                        {
                            $noteClass = 'manga-note-good';
                        }
                        elseif ((int) $manga->note <= 4)
                        {
                            $noteClass = 'manga-note-low';
                        }
                        ?>
                        <span class="manga-card-badge-note <?= htmlspecialchars($noteClass) ?>">
                            <?= (int) $manga->note ?>
                        </span>
                    <?php endif; ?>

                </figure>

                <p class="manga-card-title">
                    <?= htmlspecialchars($manga->livre) ?>
                </p>

            </article>

        <?php endforeach; ?>

    </section>

    <?php if (!$isCollection && isset($compteur)): ?>
        <nav class="flex-center-center-gap-25 m-t-30">

            <?php for ($getId = 1; $getId <= $compteur; $getId++): ?>
                <a class="link-paginate <?= ($currentPage === $getId) ? 'active' : '' ?>"
                   href="<?= $basePath; ?>manga/collection/page/<?= $getId; ?>">
                    <?= $getId; ?>
                </a>
            <?php endfor; ?>

        </nav>
    <?php endif; ?>

    <?php if ($isCollection): ?>
        <div class="m-t-30">
            <a class="link-section" href="<?= $basePath; ?>manga/collection">
                Retour
            </a>
        </div>
    <?php endif; ?>

</section>