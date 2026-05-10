<?php

$vocabulaires = isset($vocabulaires) && is_array($vocabulaires) ? $vocabulaires : [];

?>

<section class="layout-container dashboard-page">

    <section class="dashboard-header">

        <div class="dashboard-title-box animate-fade-up">

            <h1 class="dashboard-title">
                🐉 Mandarin
            </h1>

            <p class="dashboard-description">
                Vocabulaire, grammaire, immersion
                et notes en chinois standard.
            </p>

        </div>

    </section>

    <section class="chinois-vocab-panel animate-fade-up">

        <div class="chinois-vocab-header">

            <div>
                <h2 class="chinois-vocab-title">
                    Vocabulaire
                </h2>

                <p class="chinois-vocab-subtitle">
                    Liste des mots et phrases enregistrés en mandarin.
                </p>
            </div>

        </div>

        <div class="chinois-vocab-table-wrap">

            <table class="chinois-vocab-table">

                <thead>
                    <tr>
                        <th>Mot</th>
                        <th>Type</th>
                        <th>Traduction</th>
                        <th>Exemple</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($vocabulaires === []): ?>

                        <tr>
                            <td colspan="4">
                                Aucun vocabulaire enregistré.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($vocabulaires as $vocabulaire): ?>

                            <tr>
                                <td>
                                    <span class="chinois-vocab-word">
                                        <?= e($vocabulaire->mot) ?>
                                    </span>

                                    <span class="chinois-vocab-pinyin">
                                        <?= e($vocabulaire->pinyin) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="chinois-vocab-type">
                                        <?= e($vocabulaire->type) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e($vocabulaire->traduction) ?>
                                </td>

                                <td>
                                    <span class="chinois-vocab-example">
                                        <?= e($vocabulaire->exemple) ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</section>