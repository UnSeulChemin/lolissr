<section class="section-content">

    <h2>English Cards</h2>

    <?= $englishForm; ?>

    <section>
    <?php foreach($englishs as $english): ?>
        <article class="article-content">
            <p><?= $english->word ?></p>
        </article>
    <?php endforeach; ?>
    </section>

</section>