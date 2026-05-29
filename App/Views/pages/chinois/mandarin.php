<?php

declare(strict_types=1);

/** @var array<int, object> $vocabulaires */

?>

<section class="layout-container dashboard-page">

    <section
        class="
            chinois-vocab-panel
            transition-card
        "
    >

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

                            <tr class="transition-card">

                                <td>

                                    <span class="chinois-vocab-word">
                                        <?= e((string) $vocabulaire->mot) ?>
                                    </span>

                                    <span class="chinois-vocab-pinyin">
                                        <?= e((string) $vocabulaire->pinyin) ?>
                                    </span>

                                </td>

                                <td>

                                    <span class="chinois-vocab-type">
                                        <?= e((string) $vocabulaire->type) ?>
                                    </span>

                                </td>

                                <td>
                                    <?= e((string) $vocabulaire->traduction) ?>
                                </td>

                                <td>

                                    <span class="chinois-vocab-example">
                                        <?= e((string) $vocabulaire->exemple) ?>
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