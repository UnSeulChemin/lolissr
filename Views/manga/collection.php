<?php $isCollection = !empty($titleFilter); ?>

<section class="section-content">

    <section class="flex-center-wrap-gap-50">

        <?php foreach ($mangas as $manga): ?>

            <article class="card-content">

                <figure class="card-figure">

                    <a class="flex"
                    href="<?= $basePath; ?>manga/collection/<?= $manga->slug ?><?= $isCollection ? '/' . $manga->numero : '' ?>">

                        <img
                            alt="<?= htmlspecialchars($manga->livre) ?>"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>">

                    </a>

                    <!-- Badge nombre de tomes (page collection principale) -->
                    <?php if (!$isCollection): ?>
                        <span class="badge-count">
                            <?= htmlspecialchars($manga->total) ?>
                        </span>
                    <?php endif; ?>

                    <!-- Badge note (page collection/berserk) -->
                    <?php if ($isCollection && $manga->note !== null): ?>
                        <span class="badge-note note-<?= (int) $manga->note ?>">
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

    <?php if (!$isCollection && isset($compteur)) : ?>

        <nav class="flex-center-center-gap-25 m-t-30">

            <?php
            $currentPage = basename($_GET['p'] ?? 1);
            if (!is_numeric($currentPage)) $currentPage = 1;

            for ($getId = 1; $getId <= $compteur; $getId++):
            ?>

                <a class="link-paginate <?= ($currentPage == $getId) ? 'active' : '' ?>"
                   href="<?= $basePath; ?>manga/page/<?= $getId; ?>">
                    <?= $getId; ?>
                </a>

            <?php endfor; ?>

        </nav>

    <?php endif; ?>

    <div class="m-t-30">
        <a class="link-section" href="<?= $basePath; ?>manga/collection">
            Back
        </a>
    </div>

</section>