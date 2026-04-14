<?php
$isCollection = isset($slugFilter) && !empty($slugFilter);
$currentPage = $currentPage ?? 1;
?>

<section class="layout-container">

    <section class="collection-grid animate-fade-up-stagger">

        <?php foreach ($mangas as $manga): ?>

            <a
                class="collection-card-link"
                href="<?= $isCollection
                    ? $basePath . 'manga/' . rawurlencode($manga->slug) . '/' . (int) $manga->numero
                    : $basePath . 'manga/serie/' . rawurlencode($manga->slug) ?>">

                <article class="card collection-card">

                    <div class="collection-card-image-box">

                        <img
                            class="collection-card-image card-image"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>"
                            alt="<?= htmlspecialchars($manga->livre) ?>">

                    </div>

                    <p class="collection-card-title">
                        <?= htmlspecialchars($manga->livre) ?>
                    </p>

                    <?php if (!$isCollection): ?>
                        <p class="collection-card-subtitle">
                            <?= (int) $manga->total ?> tomes
                        </p>

                        <span class="collection-card-badge-count">
                            📚 <?= (int) $manga->total ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($isCollection && $manga->note !== null): ?>
                        <?php
                        $noteClass = 'collection-note-mid';

                        if ((int) $manga->note >= 8)
                        {
                            $noteClass = 'collection-note-good';
                        }
                        elseif ((int) $manga->note <= 4)
                        {
                            $noteClass = 'collection-note-low';
                        }
                        ?>
                        <span class="collection-card-badge-note <?= htmlspecialchars($noteClass) ?>">
                            ✨ <?= (int) $manga->note ?>
                        </span>
                    <?php endif; ?>

                </article>

            </a>

        <?php endforeach; ?>

    </section>

    <?php if (!$isCollection && isset($compteur)): ?>
        <nav class="collection-pagination">

            <?php for ($getId = 1; $getId <= $compteur; $getId++): ?>
                <a
                    class="collection-pagination-link <?= ($currentPage === $getId) ? 'active' : '' ?>"
                    href="<?= $basePath; ?>manga/collection/page/<?= $getId; ?>">
                    <?= $getId; ?>
                </a>
            <?php endfor; ?>

        </nav>
    <?php endif; ?>

    <?php if ($isCollection): ?>
        <div class="collection-back-wrapper">
            <a
                class="form-submit collection-back-button"
                href="<?= $basePath; ?>manga/collection">
                Retour
            </a>
        </div>
    <?php endif; ?>

</section>