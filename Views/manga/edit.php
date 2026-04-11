<section class="section-content">

    <h1 class="card-banner">
        Modifier <?= htmlspecialchars($manga->livre) ?> - Tome <?= (int) $manga->numero ?>
    </h1>

    <form action="<?= $basePath; ?>manga/update/<?= htmlspecialchars($manga->slug) ?>/<?= (int) $manga->numero ?>" method="post">

        <div class="m-t-30">
            <label for="note">Note :</label>

            <select name="note" id="note">

                <option value="" <?= $manga->note === null ? 'selected' : '' ?>>
                    Aucune note
                </option>

                <option value="1" <?= $manga->note === 1 ? 'selected' : '' ?>>1</option>
                <option value="2" <?= $manga->note === 2 ? 'selected' : '' ?>>2</option>
                <option value="3" <?= $manga->note === 3 ? 'selected' : '' ?>>3</option>
                <option value="4" <?= $manga->note === 4 ? 'selected' : '' ?>>4</option>
                <option value="5" <?= $manga->note === 5 ? 'selected' : '' ?>>5</option>

            </select>
        </div>

        <div class="m-t-30">
            <button type="submit" class="link-edit">Enregistrer</button>
        </div>

    </form>

    <div class="m-t-30">
        <a class="link-section" href="<?= $basePath; ?>manga/collection/<?= htmlspecialchars($manga->slug) ?>/<?= (int) $manga->numero ?>">
            Back
        </a>
    </div>

</section>