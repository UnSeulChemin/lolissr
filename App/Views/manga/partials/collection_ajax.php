<?php

$mangas = isset($mangas) && is_array($mangas) ? $mangas : [];
$compteur = isset($compteur) ? (int) $compteur : 0;
$currentPage = isset($currentPage) ? (int) $currentPage : 1;
$slugFilter = $slugFilter ?? null;
$isCollection = is_string($slugFilter) && trim($slugFilter) !== '';

?>

<?php if ($mangas === []): ?>

    <p class="collection-empty">
        Aucun manga trouvé.
    </p>

<?php else: ?>

    <section class="collection-grid animate-fade-up-stagger">

        <?php foreach ($mangas as $manga): ?>

            <?php
            $slug = isset($manga->slug) ? (string) $manga->slug : '';
            $numero = isset($manga->numero) ? (int) $manga->numero : 0;
            $thumbnail = isset($manga->thumbnail) ? (string) $manga->thumbnail : '';
            $extension = isset($manga->extension) ? (string) $manga->extension : '';
            $livre = isset($manga->livre) ? (string) $manga->livre : '';
            $note = $manga->note ?? null;
            $total = isset($manga->total) ? (int) $manga->total : 0;

            if ($slug === '' || $thumbnail === '' || $extension === '' || $livre === '') {
                continue;
            }

            $href = $isCollection
                ? $basePath . 'manga/' . rawurlencode($slug) . '/' . $numero
                : $basePath . 'manga/serie/' . rawurlencode($slug);

            $noteClass = 'collection-note-mid';

            if ($isCollection && $note !== null) {
                if ((int) $note >= 8) {
                    $noteClass = 'collection-note-good';
                } elseif ((int) $note <= 4) {
                    $noteClass = 'collection-note-low';
                }
            }
            ?>

            <a class="card card-link collection-card-link" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">

                <?php if ($isCollection && $note !== null): ?>
                    <span class="collection-card-badge <?= htmlspecialchars($noteClass, ENT_QUOTES, 'UTF-8'); ?>">
                        ⭐ <?= (int) $note; ?>/10
                    </span>
                <?php endif; ?>

                <div class="card-image-box-portrait">
                    <img
                        class="card-image-portrait"
                        src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($thumbnail . '.' . $extension, ENT_QUOTES, 'UTF-8'); ?>"
                        alt="<?= htmlspecialchars($livre, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <p class="collection-card-title">
                    <?= htmlspecialchars($livre, ENT_QUOTES, 'UTF-8'); ?>
                </p>

                <?php if ($isCollection): ?>
                    <p class="collection-card-subtitle">
                        Tome <?= str_pad((string) $numero, 2, '0', STR_PAD_LEFT); ?>
                    </p>
                <?php else: ?>
                    <p class="collection-card-subtitle">
                        <?= $total; ?> tomes
                    </p>
                <?php endif; ?>

            </a>

        <?php endforeach; ?>

    </section>

<?php endif; ?>

<?php if (!$isCollection && $compteur > 1): ?>
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