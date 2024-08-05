<section class="section-content">

    <h2>Chinese Cards</h2>

    <?php foreach($chineses as $chinese): ?>

        <article class="article-content">

            <p><?= $chinese->vocabulary ?></p>

        </article>

    <?php endforeach; ?>

</section>