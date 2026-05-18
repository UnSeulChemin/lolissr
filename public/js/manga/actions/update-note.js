import { showToast } from '../../core/toast.js';

function getCsrfToken()
{
    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';
}

export function initUpdateNote()
{
    const mangaDetailCard = document.querySelector(
        '.js-detail-card'
    );

    if (!mangaDetailCard)
    {
        return;
    }

    if (
        mangaDetailCard.dataset.updateNoteInit
        === 'true'
    )
    {
        return;
    }

    mangaDetailCard.dataset.updateNoteInit = 'true';

    const mangaSlug =
        mangaDetailCard.dataset.slug;

    const mangaNumero =
        mangaDetailCard.dataset.numero;

    const basePath =
        mangaDetailCard.dataset.basePath;

    const totalNoteElement =
        document.getElementById(
            'ajax-note-total'
        );

    const noteButtons =
        mangaDetailCard.querySelectorAll(
            '.ajax-note-button'
        );

    const noteGroups =
        mangaDetailCard.querySelectorAll(
            '.ajax-note-group'
        );

    const noteState = {
        jacquette:
            mangaDetailCard.dataset.jacquette !== ''
                ? Number(
                    mangaDetailCard.dataset.jacquette
                )
                : null,

        livre_note:
            mangaDetailCard.dataset.livreNote !== ''
                ? Number(
                    mangaDetailCard.dataset.livreNote
                )
                : null
    };

    let isSavingNotes = false;

    function refreshNoteButtonsState()
    {
        noteGroups.forEach((group) =>
        {
            const fieldName =
                group.dataset.field;

            const currentValue =
                noteState[fieldName];

            group
                .querySelectorAll(
                    '.ajax-note-button'
                )
                .forEach((button) =>
                {
                    const buttonValue = Number(
                        button.dataset.value
                    );

                    button.classList.toggle(
                        'active',
                        currentValue === buttonValue
                    );

                    button.disabled =
                        isSavingNotes;
                });
        });
    }

    async function saveNotes()
    {
        const formData = new FormData();

        formData.append(
            'jacquette',
            String(noteState.jacquette ?? 0)
        );

        formData.append(
            'livre_note',
            String(noteState.livre_note ?? 0)
        );

        const csrfToken = getCsrfToken();

        if (csrfToken !== '')
        {
            formData.append(
                'csrf_token',
                csrfToken
            );
        }

        try
        {
            isSavingNotes = true;

            refreshNoteButtonsState();

            const response = await fetch(
                `${basePath}manga/ajax/update-note/${mangaSlug}/${mangaNumero}`,
                {
                    method: 'POST',

                    headers:
                    {
                        'Accept': 'application/json',
                        'X-Requested-With':
                            'XMLHttpRequest'
                    },

                    body: formData
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success)
            {
                throw new Error(
                    data.message ?? 'Erreur'
                );
            }

            noteState.jacquette =
                data.jacquette !== undefined
                    ? Number(data.jacquette)
                    : noteState.jacquette;

            noteState.livre_note =
                data.livre_note !== undefined
                    ? Number(data.livre_note)
                    : noteState.livre_note;

            mangaDetailCard.dataset.jacquette =
                noteState.jacquette ?? '';

            mangaDetailCard.dataset.livreNote =
                noteState.livre_note ?? '';

            if (totalNoteElement)
            {
                totalNoteElement.textContent =
                    data.note !== null
                        ? `${data.note}/10`
                        : 'Non calculée';
            }

            refreshNoteButtonsState();

            showToast(
                '✓ Sauvegardé',
                'success'
            );
        }
        catch (error)
        {
            showToast(
                error.message ?? 'Erreur',
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
        button.addEventListener(
            'click',
            async () =>
            {
                if (isSavingNotes)
                {
                    return;
                }

                const noteGroup =
                    button.closest(
                        '.ajax-note-group'
                    );

                if (!noteGroup)
                {
                    return;
                }

                const fieldName =
                    noteGroup.dataset.field;

                if (
                    !Object.prototype
                        .hasOwnProperty.call(
                            noteState,
                            fieldName
                        )
                )
                {
                    return;
                }

                noteState[fieldName] = Number(
                    button.dataset.value
                );

                refreshNoteButtonsState();

                await saveNotes();
            }
        );
    });

    refreshNoteButtonsState();
}