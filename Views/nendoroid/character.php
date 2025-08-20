<section class="section-content">

    <section class="card-character flex-gap-50">

        <figure class="card-character-img">
            <img alt="<?= $nendoroid->origin ?>"
            src="<?= $pathRedirect; ?>public/images/nendoroids/thumbnail/<?= $nendoroid->thumbnail.".".$nendoroid->extension ?>">
        </figure>

        <article class="card-character-value">
            <p class="card-banner table-colonne">Origine</p>
            <p class="card-banner"><?= $nendoroid->origin ?></p>
            <p class="card-banner table-colonne">Personnage</p>
            <p class="card-banner"><?= $nendoroid->character ?></p>
            <p class="card-banner table-colonne">Entreprise</p>
            <p class="card-banner"><?= $nendoroid->company ?></p>
            <p class="card-banner table-colonne">Date de sortie</p>
            <p class="card-banner"><?= $nendoroid->release ?></p>
            <p class="card-banner table-colonne">Prix</p>
            <p class="card-banner"><?= $nendoroid->price ?>€</p>
            <a class="card-banner table-colonne" href="<?= $nendoroid->link ?>" target="_blank">Lien</a>
        </article>

    </section>

    <div class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>