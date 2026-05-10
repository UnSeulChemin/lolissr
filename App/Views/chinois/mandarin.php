<?php

$vocabulaires = isset($vocabulaires) && is_array($vocabulaires) ? $vocabulaires : [];

?>

<section class="layout-container dashboard-page">

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
                                        <?= htmlspecialchars((string) $vocabulaire->mot, ENT_QUOTES, 'UTF-8') ?>
                                    </span>

                                    <span class="chinois-vocab-pinyin">
                                        <?= htmlspecialchars((string) $vocabulaire->pinyin, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="chinois-vocab-type">
                                        <?= htmlspecialchars((string) $vocabulaire->type, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars((string) $vocabulaire->traduction, ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <span class="chinois-vocab-example">
                                        <?= htmlspecialchars((string) $vocabulaire->exemple, ENT_QUOTES, 'UTF-8') ?>
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