<section class="section-content">

    <h2>Chinese Vocabulary</h2>

    <?= $chineseForm; ?>

    <section class="section-table">

        <div class="column-table flex-around-center">
            <h3 class="div-table">Mot</h3>
            <h3 class="div-table">Type</h3>
            <h3 class="div-table">Traduction FR</h3>
            <h3 class="div-table">Traduction EN</h3>
            <h3 class="div-table">Exemple</h3>
        </div>

        <?php foreach($chineses as $chinese): ?>
            <article class="article-content flex-around-center">
                <div class="div-table">
                    <p><?= $chinese->word ?></p>
                </div>
                <div class="div-table">
                    <p><?= $chinese->type ?></p>
                </div>
                <div class="div-table">
                    <p><?= $chinese->french ?></p>
                </div>
                <div class="div-table">
                    <p><?= $chinese->english ?></p>
                </div>
                <div class="div-table">
                    <p><?= $chinese->example ?></p>
                </div>
            </article>
        <?php endforeach; ?>

    </section>

</section>