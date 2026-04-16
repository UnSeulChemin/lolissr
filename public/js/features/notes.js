import { showToast } from '../core/toast.js';

export function initAjaxNotes()
{
    const detailCard = document.querySelector('.js-detail-card');

    if (!detailCard)
    {
        return;
    }

    const slug = detailCard.dataset.slug;
    const numero = detailCard.dataset.numero;
    const basePath = detailCard.dataset.basePath;

    const totalEl = document.getElementById('ajax-note-total');
    const noteButtons = detailCard.querySelectorAll('.ajax-note-button');

    const state = {
        jacquette: detailCard.dataset.jacquette !== ''
            ? Number(detailCard.dataset.jacquette)
            : null,

        livre_note: detailCard.dataset.livreNote !== ''
            ? Number(detailCard.dataset.livreNote)
            : null
    };

    let isSaving = false;

    function refreshActiveButtons()
    {
        detailCard.querySelectorAll('.ajax-note-group').forEach((group) =>
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
                showToast(
                    '✓ Sauvegardé',
                    'success'
                );
            }

            state.jacquette = data.jacquette !== null
                ? Number(data.jacquette)
                : null;

            state.livre_note = data.livre_note !== null
                ? Number(data.livre_note)
                : null;

            if (totalEl)
            {
                totalEl.textContent = data.note !== null
                    ? `${data.note}/10`
                    : 'Non calculée';
            }

            showToast('✓ Sauvegardé');
        }
        catch (error)
        {
            showToast(
                error.message || 'Erreur lors de la mise à jour',
                'error'
            );
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

            if (!group)
            {
                return;
            }

            const field = group.dataset.field;
            const value = Number(button.dataset.value);

            state[field] = value;

            refreshActiveButtons();
            await saveNotes();
        });
    });

    refreshActiveButtons();
}