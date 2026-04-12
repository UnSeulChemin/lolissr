<?php
$isCollection = isset($slugFilter) && !empty($slugFilter);
$currentPage = $currentPage ?? 1;
?>

<section class="section-content">

    <section class="flex-center-wrap-gap-50">

        <?php foreach ($mangas as $manga): ?>

            <article class="card-content">

                <figure class="card-figure">

                    <a class="flex"
                       href="<?= $basePath; ?>manga/collection/<?= htmlspecialchars($manga->slug) ?><?= $isCollection ? '/' . (int) $manga->numero : '' ?>">

                        <img
                            alt="<?= htmlspecialchars($manga->livre) ?>"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>">

                    </a>

                    <?php if (!$isCollection): ?>
                        <span class="badge-count">
                            <?= (int) $manga->total ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($isCollection && $manga->note !== null): ?>
                        <?php
                        $noteClass = 'note-mid';

                        if ((int) $manga->note >= 8)
                        {
                            $noteClass = 'note-good';
                        }
                        elseif ((int) $manga->note <= 4)
                        {
                            $noteClass = 'note-low';
                        }
                        ?>
                        <span class="badge-note <?= $noteClass; ?>">
                            <?= (int) $manga->note ?>
                        </span>
                    <?php endif; ?>

                </figure>

                <div>
                    <p class="card-banner">
                        <?= htmlspecialchars($manga->livre) ?>
                    </p>
                </div>

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