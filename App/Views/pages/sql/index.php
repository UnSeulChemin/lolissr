<?php

declare(strict_types=1);

$sql =
    (string) ($sql ?? '');

$result =
    $result ?? [];

$error =
    $error ?? null;

?>

<section class="layout-container dashboard-page">

    <section class="form-page">

        <section class="form-card transition-form">

            <form
                class="form-layout"
                action=""
                method="post"
            >

                <?= csrf_field() ?>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="sql"
                    >

                        Requête SQL

                    </label>

                    <textarea
                        class="
                            form-textarea
                            sql-textarea
                        "
                        name="sql"
                        id="sql"
                        rows="15"
                        spellcheck="false"
                        autofocus
                        required
                        placeholder="SHOW TABLES;"
                    ><?= e($sql) ?></textarea>

                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="form-submit"
                    >

                        Exécuter

                    </button>

                </div>

            </form>

        </section>

        <?php if (
            $error !== null
            || $result !== []
        ): ?>

            <section
                class="
                    home-grid
                    home-grid-top
                    card-grid-3
                    sql-grid
                "
            >

                <article
                    class="
                        card
                        transition-card
                        card-medium
                        sql-query-card
                    "
                >

                    <h2 class="home-card-title">

                        📝 Requête SQL

                    </h2>

                    <pre class="sql-result-query"><?= e($sql) ?></pre>

                </article>

                <article
                    class="
                        card
                        transition-card
                        card-link-wide
                        card-wide
                        sql-result-card
                    "
                >

                    <?php if ($error !== null): ?>

                        <p class="sql-error">

                            <?= e($error) ?>

                        </p>

                    <?php else: ?>

                        <h2 class="home-card-title">

                            📊 Résultat

                        </h2>

                        <p class="sql-result-count">

                            <?= count($result) ?>

                            ligne(s)

                        </p>

                        <?php

                        $columns =
                            array_keys(
                                (array) $result[0],
                            );

                        ?>

                        <div class="sql-table-wrapper">

                            <table class="sql-table">

                                <thead>

                                    <tr>

                                        <?php foreach (
                                            $columns as $column
                                        ): ?>

                                            <th>

                                                <?= e($column) ?>

                                            </th>

                                        <?php endforeach; ?>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php foreach (
                                        $result as $row
                                    ): ?>

                                        <tr>

                                            <?php foreach (
                                                (array) $row as $value
                                            ): ?>

                                                <td>

                                                    <?= e(
                                                        (string) $value,
                                                    ) ?>

                                                </td>

                                            <?php endforeach; ?>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php endif; ?>

                </article>

            </section>

        <?php endif; ?>

    </section>

</section>