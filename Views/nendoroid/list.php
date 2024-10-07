<section class="section-content">
   
    <section class="flex-center-wrap-gap-50">

        <?php foreach($nendoroids as $image): ?>
            <article class="card-content">
                <figure class="card-figure">
                    <a class="flex" href="<?= $pathRedirect; ?>nendoroid/character/<?= $image->id ?>">
                        <img alt="<?= $image->serie ?>" src="<?= $pathRedirect; ?>public/images/nendoroids/thumbnail/<?= $image->thumbnail.".".$image->extension ?>">
                        <?php if ($image->hololive == 'Y'): ?>
                            <span class="card-figure-span-1 flex-center-center">hololive</span>
                        <?php endif; ?>
                        <?php if ($image->love == 'Y'): ?>
                            <span class="card-figure-span-1 flex-center-center">love</span>
                        <?php endif; ?>
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

    <nav class="flex-center-center-gap-25 m-t-30">
        <?php
        $base = basename($_GET["p"]);
        if (!is_numeric($base)): $base = 1; endif;

        for ($getId = 1; $getId <= $count; $getId++):
            if ($base != $getId): 
                ?><a class="link-paginate" href="<?= $pathRedirect; ?>nendoroid/page/<?= $getId; ?>"><?= $getId; ?></a><?php
            else: 
                ?><a class="link-paginate active"><?= $getId; ?></a><?php
            endif;
        endfor; ?>
    </nav>

    <div class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>