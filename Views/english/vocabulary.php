<section class="section-content">

    <h2>English Cards</h2>

    <?php foreach($englishs as $english): ?>

        <article class="article-content">

            <p><?= $english->vocabulary ?></p>

        </article>

    <?php endforeach; ?>

</section>