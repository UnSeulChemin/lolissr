<?php $isCollection = !empty($titleFilter); ?>

<section class="section-content">

    <section class="flex-center-wrap-gap-50">

        <?php foreach ($mangas as $manga): ?>

            <article class="card-content">

                <figure class="card-figure">

                    <?php $isCollection = !empty($titleFilter); ?>
                    <a class="flex" href="<?= $basePath; ?>manga/collection/<?= strtolower(str_replace(' ', '-', $manga->livre)) ?><?= $isCollection ? '/' . $manga->id : '' ?>">

                        <img alt="<?= $manga->livre ?>"
                             src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= $manga->thumbnail . '.' . $manga->extension ?>">

                    </a>

                </figure>

                <div>
                    <p class="card-banner"><?= $manga->livre ?></p>
                </div>

            </article>

        <?php endforeach; ?>

    </section>

    <?php if (!$isCollection) : ?>

        <nav class="flex-center-center-gap-25 m-t-30">
            <?php
            $base = basename($_GET["p"] ?? 1);
            if (!is_numeric($base)) $base = 1;

            for ($getId = 1; $getId <= $compteur; $getId++):
            ?>

                <a class="link-paginate <?= ($base == $getId) ? 'active' : '' ?>"
                   href="<?= $basePath; ?>manga/page/<?= $getId; ?>">
                    <?= $getId; ?>
                </a>

            <?php endfor; ?>
        </nav>

    <?php endif; ?>

    <div class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>