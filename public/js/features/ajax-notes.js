import { showToast } from '../core/toast.js';

export function initMangaAjaxNotes()
{
    const mangaDetailCard = document.querySelector('.js-detail-card');

    if (!mangaDetailCard)
    {
        return;
    }

    if (mangaDetailCard.dataset.ajaxNotesInit === 'true')
    {
        return;
    }

    mangaDetailCard.dataset.ajaxNotesInit = 'true';

    const mangaSlug = mangaDetailCard.dataset.slug;
    const mangaNumero = mangaDetailCard.dataset.numero;
    const basePath = mangaDetailCard.dataset.basePath;

    const totalNoteElement = document.getElementById('ajax-note-total');
    const noteButtons = mangaDetailCard.querySelectorAll('.ajax-note-button');
    const noteGroups = mangaDetailCard.querySelectorAll('.ajax-note-group');

    const noteState = {
        jacquette: mangaDetailCard.dataset.jacquette !== ''
            ? Number(mangaDetailCard.dataset.jacquette)
            : null,

        livre_note: mangaDetailCard.dataset.livreNote !== ''
            ? Number(mangaDetailCard.dataset.livreNote)
            : null
    };

    let isSavingNotes = false;

    function refreshNoteButtonsState()
    {
        noteGroups.forEach((group) =>
        {
            const fieldName = group.dataset.field;
            const currentValue = noteState[fieldName];

            group.querySelectorAll('.ajax-note-button').forEach((button) =>
            {
                const buttonValue = Number(button.dataset.value);

                button.classList.toggle('active', currentValue === buttonValue);
                button.disabled = isSavingNotes;
            });
        });
    }

    async function saveMangaNotes()
    {
        const formData = new FormData();

        formData.append('jacquette', noteState.jacquette ?? '');
        formData.append('livre_note', noteState.livre_note ?? '');

        try
        {
            isSavingNotes = true;
            refreshNoteButtonsState();

            const response = await fetch(
                `${basePath}manga/ajax/update-note/${mangaSlug}/${mangaNumero}`,
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
                throw new Error(data.message || 'Erreur lors de la sauvegarde');
            }

            noteState.jacquette = data.jacquette !== null
                ? Number(data.jacquette)
                : null;

            noteState.livre_note = data.livre_note !== null
                ? Number(data.livre_note)
                : null;

            if (totalNoteElement)
            {
                totalNoteElement.textContent = data.note !== null
                    ? `${data.note}/10`
                    : 'Non calculée';
            }

            showToast('✓ Sauvegardé', 'success');
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
            isSavingNotes = false;
            refreshNoteButtonsState();
        }
    }

    noteButtons.forEach((button) =>
    {
        button.addEventListener('click', async () =>
        {
            if (isSavingNotes)
            {
                return;
            }

            const noteGroup = button.closest('.ajax-note-group');

            if (!noteGroup)
            {
                return;
            }

            const fieldName = noteGroup.dataset.field;

            if (!Object.hasOwn(noteState, fieldName))
            {
                return;
            }

            const selectedValue = Number(button.dataset.value);

            noteState[fieldName] = selectedValue;

            refreshNoteButtonsState();
            await saveMangaNotes();
        });
    });

    refreshNoteButtonsState();
}