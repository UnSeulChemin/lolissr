<section class="section-content">

    <section class="card-character flex-gap-50">

        <figure class="card-character-img">
            <img alt="<?= $manga->livre ?>"
            src="<?= $routeRedirection; ?>public/images/mangas/thumbnail/<?= $manga->thumbnail.".".$manga->extension ?>">
        </figure>

        <article class="card-character-value">
            <p class="card-banner table-colonne">Livre</p>
            <p class="card-banner"><?= $manga->livre ?></p>
        </article>

    </section>

    <div class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>