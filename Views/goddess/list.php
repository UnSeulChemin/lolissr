<section class="section-content">
   
    <section class="flex-center-wrap-gap-50">

        <?php foreach($goddesss as $goddess): ?>
            <article class="card-content">
                <figure class="card-figure">
                    <a class="flex" href="<?= $pathRedirect; ?>goddess/character/<?= $goddess->id ?>">
                        <img alt="<?= $goddess->character ?>" src="<?= $pathRedirect; ?>public/images/goddess/thumbnail/<?= $goddess->thumbnail.".".$goddess->extension ?>">
                        <span class="card-figure-span-1 flex-center-center"><?= $goddess->rarity ?></span>
                        <span class="card-figure-span-2 flex-center-center"><?= $goddess->note ?> / 5</span>
                    </a>
                </figure>
                <div>
                    <p class="card-banner"><?= $goddess->character ?></p>
                </div>
                <div>
                    <p class="card-banner"><?= $goddess->serie ?></p>
                </div>
            </article>
        <?php endforeach; ?>

    </section>

    <nav class="flex-center-center-gap-25 m-t-30">
        <?php
        $base = basename($_GET["p"]);
        if (!is_numeric($base)): $base = 1; endif;

        for ($getId = 1; $getId <= $count; $getId++):
            if ($base != $getId): 
                ?><a class="link-paginate" href="<?= $pathRedirect; ?>goddess/page/<?= $getId; ?>"><?= $getId; ?></a><?php
            else: 
                ?><a class="link-paginate active"><?= $getId; ?></a><?php
            endif;
        endfor; ?>
    </nav>

    <div class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>