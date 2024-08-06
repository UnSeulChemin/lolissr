<section class="section-content">

    <h2>Chinese Cards</h2>

    <?= $chineseForm; ?>

    <section>
    <?php foreach($chineses as $chinese): ?>
        <article class="article-content">
            <p><?= $chinese->word ?></p>
        </article>
    <?php endforeach; ?>
    </section>

</section>