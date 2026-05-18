import { showToast } from '../core/toast.js';

export function initMangaAjaxNotes()
{
    const mangaDetailCard = document.querySelector('.js-detail-card');

    if (!mangaDetailCard)
    {
        return;
    }

    if (mangaDetailCard.dataset.mangaAjaxNotesInit === 'true')
    {
        return;
    }

    mangaDetailCard.dataset.mangaAjaxNotesInit = 'true';

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

                button.classList.toggle(
                    'active',
                    currentValue === buttonValue
                );

                button.disabled = isSavingNotes;
            });
        });
    }

    async function saveMangaNotes()
    {
        const formData = new FormData();

        formData.append('jacquette', noteState.jacquette ?? 0);
        formData.append('livre_note', noteState.livre_note ?? 0);
        formData.append('csrf_token', window.csrfToken || '');

        try
        {
            isSavingNotes = true;
            refreshNoteButtonsState();

            const response = await fetch(
                `${basePath}manga/ajax/update-note/${mangaSlug}/${mangaNumero}`,
                {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success)
            {
                throw new Error(data.message || 'Erreur');
            }

            // 🔥 IMPORTANT: UPDATE STATE FROM BACKEND
            noteState.jacquette = data.jacquette !== undefined
                ? Number(data.jacquette)
                : noteState.jacquette;

            noteState.livre_note = data.livre_note !== undefined
                ? Number(data.livre_note)
                : noteState.livre_note;

            // 🔥 IMPORTANT: UPDATE DOM DATASET
            mangaDetailCard.dataset.jacquette = noteState.jacquette ?? '';
            mangaDetailCard.dataset.livreNote = noteState.livre_note ?? '';

            // 🔥 IMPORTANT: UPDATE TOTAL DISPLAY
            if (totalNoteElement)
            {
                totalNoteElement.textContent = data.note !== null
                    ? `${data.note}/10`
                    : 'Non calculée';
            }

            // 🔥 IMPORTANT: REFRESH BUTTONS UI
            refreshNoteButtonsState();

            showToast('✓ Sauvegardé', 'success');
        }
        catch (error)
        {
            showToast(error.message || 'Erreur', 'error');
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

            if (!Object.prototype.hasOwnProperty.call(noteState, fieldName))
            {
                return;
            }

            noteState[fieldName] = Number(button.dataset.value);

            refreshNoteButtonsState();
            await saveMangaNotes();
        });
    });

    refreshNoteButtonsState();
}