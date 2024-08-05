<section class="section-content">

    <h2>Goddess Story - Cards</h2>

    <?php foreach($goddesss as $goddess): ?>

        <article class="article-content">

            <p><?= $goddess->quality ?></p>

        </article>

    <?php endforeach; ?>

</section>