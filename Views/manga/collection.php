<?php $isCollection = !empty($titleFilter); ?>

<section class="section-content">

    <section class="flex-center-wrap-gap-50">

        <?php foreach ($mangas as $manga): ?>

            <?php $slug = strtolower(preg_replace('/\s+/', '-', trim($manga->livre))); ?>

            <article class="card-content">

                <figure class="card-figure">

                    <a class="flex"
                       href="<?= htmlspecialchars($basePath . 'manga/collection/' . $slug . ($isCollection ? '/' . $manga->numero : '')) ?>">

                        <img
                            alt="<?= htmlspecialchars($manga->livre) ?>"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>">

                    </a>

                </figure>

                <div>
                    <p class="card-banner"><?= htmlspecialchars($manga->livre) ?></p>
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
        <a class="link-section" href="<?= $basePath; ?>manga/collection">Back</a>
    </div>

</section>