<section class="section-content">

    <section class="card-character flex-gap-50">

        <figure class="card-character-img">
            <img alt="<?= $figurine->origin ?>"
            src="<?= $pathRedirect; ?>public/images/figurines/thumbnail/<?= $figurine->thumbnail.".".$figurine->extension ?>">
        </figure>

        <article class="card-character-value">
            <p class="card-banner table-colonne">Origine</p>
            <p class="card-banner"><?= $figurine->origin ?></p>
            <p class="card-banner table-colonne">Personnage</p>
            <p class="card-banner"><?= $figurine->character ?></p>
            <p class="card-banner table-colonne">Entreprise</p>
            <p class="card-banner"><?= $figurine->company ?></p>
            <p class="card-banner table-colonne">Date de sortie</p>
            <p class="card-banner"><?= $figurine->release ?></p>
            <p class="card-banner table-colonne">Prix</p>
            <p class="card-banner"><?= $figurine->price ?>€</p>
            <a class="card-banner table-colonne" href="<?= $figurine->link ?>" target="_blank">Lien</a>
        </article>

    </section>

    <div class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>