<section class="section-content">

    <h2>French Cards</h2>

    <?php foreach($frenchs as $french): ?>

        <article class="article-content">

            <p><?= $french->vocabulary ?></p>

        </article>

    <?php endforeach; ?>

</section>