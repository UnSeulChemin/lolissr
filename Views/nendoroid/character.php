<section class="section-content">

    <section class="card-character flex-gap-50">

        <figure class="card-character-img">
            <img alt="<?= $nendoroid->serie ?>"
            src="<?= $pathRedirect; ?>public/images/nendoroids/thumbnail/<?= $nendoroid->thumbnail.".".$nendoroid->extension ?>">
        </figure>

        <article class="card-character-value">
            <p class="card-banner table-colonne">Série</p>
            <p class="card-banner"><?= $nendoroid->serie ?></p>
            <p class="card-banner table-colonne">Marque</p>
            <p class="card-banner"><?= $nendoroid->brand ?></p>
            <p class="card-banner table-colonne">Prix</p>
            <p class="card-banner"><?= $nendoroid->price ?>€</p>
            <p class="card-banner table-colonne">Date</p>
            <p class="card-banner"><?= $nendoroid->date ?></p>
            <p class="card-banner table-colonne">Stock</p>
            <p class="card-banner"><?= $nendoroid->stock ?></p>
            <a class="card-banner table-colonne" href="<?= $nendoroid->link ?>" target="_blank">Lien</a>
        </article>

    </section>

    <div class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>