<section class="section-content">

    <section class="card-character flex-gap-50">

        <figure class="card-character-img">
            <img alt="<?= $figurine->serie ?>"
            src="<?= $pathRedirect; ?>public/images/figurines/thumbnail/<?= $figurine->thumbnail.".".$figurine->extension ?>">
        </figure>

        <article class="card-character-value">
            <p class="card-banner table-colonne">Série</p>
            <p class="card-banner"><?= $figurine->serie ?></p>
            <p class="card-banner table-colonne">Marque</p>
            <p class="card-banner"><?= $figurine->brand ?></p>
            <p class="card-banner table-colonne">Prix</p>
            <p class="card-banner"><?= $figurine->price ?>€</p>
            <p class="card-banner table-colonne">Date</p>
            <p class="card-banner"><?= $figurine->date ?></p>
            <a class="card-banner table-colonne" href="<?= $figurine->link ?>" target="_blank">Lien</a>
        </article>

    </section>

    <div class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>