<section class="section-content">
   
    <section class="flex-center-wrap-gap-50">

        <?php foreach($figurines as $image): ?>
            <article class="card-content">
                <figure class="card-figure">
                    <a class="flex" href="<?= $pathRedirect; ?>figurine/character/<?= $image->id ?>">
                        <img alt="<?= $image->serie ?>" src="<?= $pathRedirect; ?>public/images/figurines/thumbnail/<?= $image->thumbnail.".".$image->extension ?>">
                        <span class="card-figure-span-2 flex-center-center"><?= $image->estimated ?></span>
                    </a>
                </figure>
                <div>
                    <p class="card-banner"><?= $image->serie ?></p>
                </div>
                <div>
                    <p class="card-banner"><?= $image->brand ?></p>
                </div>
           </article>
        <?php endforeach; ?>

    </section>

    <div class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>