<section class="section-content">
   
    <section class="card-character flex-gap-50">

        <figure class="card-character-img">
            <img alt="<?= $goddess->character ?>"
            src="<?= $pathRedirect; ?>public/images/goddess/thumbnail/<?= $goddess->thumbnail.".".$goddess->extension ?>">
        </figure>

        <article class="card-character-value">
            <p class="card-banner table-colonne">Personnage</p>
            <p class="card-banner"><?= $goddess->character ?></p>
            <p class="card-banner table-colonne">Série</p>
            <p class="card-banner"><?= $goddess->serie ?></p>
            <p class="card-banner table-colonne">Rareté</p>
            <p class="card-banner"><?= $goddess->rarity ?></p>
            <p class="card-banner table-colonne">Setbox</p>
            <p class="card-banner"><?= $goddess->set ?></p>
            <p class="card-banner table-colonne">Setbox Date</p>
            <p class="card-banner"><?= $goddess->date ?></p>
        </article>

    </section>

    <div class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>