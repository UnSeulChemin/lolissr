<?php
$isCollection = isset($slugFilter) && !empty($slugFilter);
$currentPage = $currentPage ?? 1;
?>

<section class="collection-grid animate-fade-up-stagger">

    <?php foreach ($mangas as $manga): ?>

        <?php
        $href = $isCollection
            ? $basePath . 'manga/' . rawurlencode($manga->slug) . '/' . (int) $manga->numero
            : $basePath . 'manga/serie/' . rawurlencode($manga->slug);

        $noteClass = 'collection-note-mid';

        if ($isCollection && $manga->note !== null)
        {
            if ((int) $manga->note >= 8)
            {
                $noteClass = 'collection-note-good';
            }
            elseif ((int) $manga->note <= 4)
            {
                $noteClass = 'collection-note-low';
            }
        }
        ?>

        <a
            class="card card-link collection-card-link"
            href="<?= $href; ?>">

            <?php if ($isCollection && $manga->note !== null): ?>
                <span class="collection-card-badge <?= $noteClass; ?>">
                    ⭐ <?= (int) $manga->note; ?>/10
                </span>
            <?php endif; ?>

            <div class="card-image-box-portrait">
                <img
                    class="card-image-portrait"
                    src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension); ?>"
                    alt="<?= htmlspecialchars($manga->livre); ?>">
            </div>

            <p class="collection-card-title">
                <?= htmlspecialchars($manga->livre); ?>
            </p>

            <?php if ($isCollection): ?>
                <p class="collection-card-subtitle">
                    Tome <?= str_pad((string) $manga->numero, 2, '0', STR_PAD_LEFT); ?>
                </p>
            <?php else: ?>
                <p class="collection-card-subtitle">
                    <?= (int) $manga->total; ?> tomes
                </p>
            <?php endif; ?>

        </a>

    <?php endforeach; ?>

</section>

<?php if (!$isCollection && isset($compteur)): ?>
    <nav class="collection-pagination">

        <?php for ($getId = 1; $getId <= $compteur; $getId++): ?>
            <a
                class="collection-pagination-link <?= ($currentPage === $getId) ? 'active' : ''; ?>"
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