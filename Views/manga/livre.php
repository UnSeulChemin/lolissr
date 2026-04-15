<section class="layout-container">

    <section class="detail-card animate-fade-up">

        <figure class="detail-image">

            <div class="detail-image-inner">

                <img
                    src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>"
                    alt="<?= htmlspecialchars($manga->livre) ?>">

            </div>

        </figure>

        <article class="detail-content">

            <div class="detail-row">
                <div class="detail-label">Livre</div>
                <div class="detail-value"><?= htmlspecialchars($manga->livre) ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Tome</div>
                <div class="detail-value">
                    <?= str_pad((string) ((int) $manga->numero), 2, '0', STR_PAD_LEFT) ?>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Jacquette</div>

                <div class="detail-value detail-value-notes">
                    <div
                        class="ajax-note-group"
                        data-field="jacquette"
                        data-current="<?= $manga->jacquette !== null ? (int) $manga->jacquette : '' ?>">

                        <?php for ($note = 1; $note <= 5; $note++): ?>
                            <button
                                class="ajax-note-button <?= ($manga->jacquette !== null && (int) $manga->jacquette === $note) ? 'active' : '' ?>"
                                type="button"
                                data-value="<?= $note ?>">
                                <?= $note ?>
                            </button>
                        <?php endfor; ?>

                    </div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">État du livre</div>

                <div class="detail-value detail-value-notes">
                    <div
                        class="ajax-note-group"
                        data-field="livre_note"
                        data-current="<?= $manga->livre_note !== null ? (int) $manga->livre_note : '' ?>">

                        <?php for ($note = 1; $note <= 5; $note++): ?>
                            <button
                                class="ajax-note-button <?= ($manga->livre_note !== null && (int) $manga->livre_note === $note) ? 'active' : '' ?>"
                                type="button"
                                data-value="<?= $note ?>">
                                <?= $note ?>
                            </button>
                        <?php endfor; ?>

                    </div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Note totale</div>
                <div
                    class="detail-value"
                    id="ajax-note-total">
                    <?= $manga->note !== null ? (int) $manga->note . '/10' : 'Non calculée' ?>
                </div>
            </div>

            <div class="detail-row detail-row-comment">
                <div class="detail-label">Commentaire</div>

                <div class="detail-value detail-comment-box <?= empty($manga->commentaire) ? 'is-empty' : '' ?>">
                    <?= !empty($manga->commentaire)
                        ? nl2br(htmlspecialchars($manga->commentaire))
                        : 'Aucun commentaire' ?>
                </div>
            </div>

            <div class="detail-actions">
                <a
                    class="form-submit"
                    href="<?= $basePath; ?>manga/update/<?= rawurlencode($manga->slug) ?>/<?= (int) $manga->numero ?>">
                    Modifier
                </a>
            </div>

        </article>

    </section>

    <div class="collection-back-wrapper">
        <a
            class="form-submit collection-back-button"
            href="<?= $basePath; ?>manga/serie/<?= rawurlencode($manga->slug) ?>">
            Retour
        </a>
    </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', () =>
{
    const slug = "<?= rawurlencode($manga->slug) ?>";
    const numero = "<?= (int) $manga->numero ?>";
    const basePath = "<?= $basePath ?>";

    const totalEl = document.getElementById('ajax-note-total');
    const noteButtons = document.querySelectorAll('.ajax-note-button');

    const state = {
        jacquette: <?= $manga->jacquette !== null ? (int) $manga->jacquette : 'null' ?>,
        livre_note: <?= $manga->livre_note !== null ? (int) $manga->livre_note : 'null' ?>
    };

    let isSaving = false;

    function refreshActiveButtons()
    {
        document.querySelectorAll('.ajax-note-group').forEach((group) =>
        {
            const field = group.dataset.field;
            const currentValue = state[field];

            group.querySelectorAll('.ajax-note-button').forEach((button) =>
            {
                const buttonValue = Number(button.dataset.value);
                button.classList.toggle('active', currentValue === buttonValue);
                button.disabled = isSaving;
            });
        });
    }

    async function saveNotes()
    {
        const formData = new FormData();
        formData.append('jacquette', state.jacquette ?? '');
        formData.append('livre_note', state.livre_note ?? '');

        try
        {
            isSaving = true;
            refreshActiveButtons();

            const response = await fetch(
                `${basePath}manga/ajax/update-note/${slug}/${numero}`,
                {
                    method: 'POST',
                    body: formData
                }
            );

            if (!response.ok)
            {
                throw new Error('Erreur réseau');
            }

            const data = await response.json();

            if (!data.success)
            {
                throw new Error(data.message || 'Erreur lors de la mise à jour');
            }

            state.jacquette = data.jacquette !== null ? Number(data.jacquette) : null;
            state.livre_note = data.livre_note !== null ? Number(data.livre_note) : null;

            totalEl.textContent = data.note !== null
                ? `${data.note}/10`
                : 'Non calculée';
        }
        catch (error)
        {
            alert(error.message || 'Erreur lors de la mise à jour');
        }
        finally
        {
            isSaving = false;
            refreshActiveButtons();
        }
    }

    noteButtons.forEach((button) =>
    {
        button.addEventListener('click', async () =>
        {
            if (isSaving)
            {
                return;
            }

            const group = button.closest('.ajax-note-group');
            const field = group.dataset.field;
            const value = Number(button.dataset.value);

            state[field] = value;

            refreshActiveButtons();
            await saveNotes();
        });
    });

    refreshActiveButtons();
});
</script>