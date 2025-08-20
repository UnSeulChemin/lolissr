<section class="section-content">

    <section class="card-character flex-gap-50">

        <figure class="card-character-img">
            <img alt="<?= $plush->origin ?>"
            src="<?= $pathRedirect; ?>public/images/plushs/thumbnail/<?= $plush->thumbnail.".".$plush->extension ?>">
        </figure>

        <article class="card-character-value">
            <p class="card-banner table-colonne">Origine</p>
            <p class="card-banner"><?= $plush->origin ?></p>
            <p class="card-banner table-colonne">Personnage</p>
            <p class="card-banner"><?= $plush->character ?></p>
            <p class="card-banner table-colonne">Entreprise</p>
            <p class="card-banner"><?= $plush->company ?></p>
            <p class="card-banner table-colonne">Date de sortie</p>
            <p class="card-banner"><?= $plush->release ?></p>
            <p class="card-banner table-colonne">Prix</p>
            <p class="card-banner"><?= $plush->price ?>€</p>
            <a class="card-banner table-colonne" href="<?= $plush->link ?>" target="_blank">Lien</a>
        </article>

    </section>

    <div class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>