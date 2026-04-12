<section class="section-content">

    <h1 class="card-banner">
        Modifier <?= htmlspecialchars($manga->livre) ?> - Tome <?= (int) $manga->numero ?>
    </h1>

    <form action="<?= $basePath; ?>manga/update/<?= htmlspecialchars($manga->slug) ?>/<?= (int) $manga->numero ?>" method="post">

        <div class="m-t-30">
            <label for="jacquette">Note jacquette :</label>

            <select name="jacquette" id="jacquette">
                <option value="">Choisir</option>
                <option value="1" <?= $manga->jacquette === 1 ? 'selected' : '' ?>>1</option>
                <option value="2" <?= $manga->jacquette === 2 ? 'selected' : '' ?>>2</option>
                <option value="3" <?= $manga->jacquette === 3 ? 'selected' : '' ?>>3</option>
                <option value="4" <?= $manga->jacquette === 4 ? 'selected' : '' ?>>4</option>
                <option value="5" <?= $manga->jacquette === 5 ? 'selected' : '' ?>>5</option>
            </select>
        </div>

        <div class="m-t-30">
            <label for="livre_note">Note livre :</label>

            <select name="livre_note" id="livre_note">
                <option value="">Choisir</option>
                <option value="1" <?= $manga->livre_note === 1 ? 'selected' : '' ?>>1</option>
                <option value="2" <?= $manga->livre_note === 2 ? 'selected' : '' ?>>2</option>
                <option value="3" <?= $manga->livre_note === 3 ? 'selected' : '' ?>>3</option>
                <option value="4" <?= $manga->livre_note === 4 ? 'selected' : '' ?>>4</option>
                <option value="5" <?= $manga->livre_note === 5 ? 'selected' : '' ?>>5</option>
            </select>
        </div>

        <div class="m-t-30">
            <p>
                Note totale :
                <?= $manga->note !== null ? (int) $manga->note . '/10' : 'Non calculée' ?>
            </p>
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