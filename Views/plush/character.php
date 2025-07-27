<section class="section-content">

    <section class="card-character flex-gap-50">

        <figure class="card-character-img">
            <img alt="<?= $plush->serie ?>"
            src="<?= $pathRedirect; ?>public/images/plushs/thumbnail/<?= $plush->thumbnail.".".$plush->extension ?>">
        </figure>

        <article class="card-character-value">
            <p class="card-banner table-colonne">Série</p>
            <p class="card-banner"><?= $plush->serie ?></p>
            <p class="card-banner table-colonne">Marque</p>
            <p class="card-banner"><?= $plush->brand ?></p>
            <p class="card-banner table-colonne">Prix</p>
            <p class="card-banner"><?= $plush->price ?>€</p>
            <p class="card-banner table-colonne">Date</p>
            <p class="card-banner"><?= $plush->date ?></p>
            <p class="card-banner table-colonne">Stock</p>
            <p class="card-banner"><?= $plush->stock ?></p>
            <a class="card-banner table-colonne" href="<?= $plush->link ?>" target="_blank">Lien</a>
        </article>

    </section>

    <div class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>