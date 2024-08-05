<section class="section-content">

    <h2>French Cards</h2>

    <?= $frenchForm; ?>

    <section>
    <?php foreach($frenchs as $french): ?>
        <article class="article-content">
            <p><?= $french->word ?></p>
        </article>
    <?php endforeach; ?>
    </section>

</section>