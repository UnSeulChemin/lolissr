<?php
$search = $search ?? '';
?>

<section class="layout-container">

    <h1 class="home-section-title">
        🔎 Résultats de recherche
    </h1>

    <?php if ($search !== ''): ?>
        <p class="home-empty" style="text-align: center; margin-bottom: 10px;">
            Résultats pour : <strong><?= htmlspecialchars($search) ?></strong>
        </p>
    <?php endif; ?>

    <?php if ($search === ''): ?>
        <p class="home-empty" style="text-align: center;">
            Saisissez un titre dans la barre de recherche.
        </p>
    <?php elseif (empty($mangas)): ?>
        <p class="home-empty" style="text-align: center;">
            Aucun manga trouvé.
        </p>
    <?php else: ?>

        <section class="collection-grid animate-fade-up-stagger">

            <?php foreach ($mangas as $manga): ?>
                <?php
                $noteClass = 'collection-note-mid';

                if ($manga->note !== null)
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
                    class="collection-card-link"
                    href="<?= $basePath; ?>manga/<?= rawurlencode($manga->slug) ?>/<?= (int) $manga->numero ?>">

                    <article class="card collection-card">

                        <div class="card-image-box-portrait">
                            <img
                                class="card-image-portrait card-image"
                                src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>"
                                alt="<?= htmlspecialchars($manga->livre) ?>">
                        </div>

                        <p class="collection-card-title">
                            <?= htmlspecialchars($manga->livre) ?>
                        </p>

                        <p class="collection-card-subtitle">
                            Tome <?= (int) $manga->numero ?>
                        </p>

                        <?php if ($manga->note !== null): ?>
                            <span class="collection-card-badge collection-card-badge-note <?= htmlspecialchars($noteClass) ?>">
                                ⭐ <?= (int) $manga->note ?>
                            </span>
                        <?php endif; ?>

                    </article>

                </a>
            <?php endforeach; ?>

        </section>

    <?php endif; ?>

</section>