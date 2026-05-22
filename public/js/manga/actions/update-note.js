import { showToast }
    from '../../core/toast.js';

/*
|------------------------------------------------------------------
| CSRF
|------------------------------------------------------------------
*/

function getCsrfToken()
{
    return document
        .querySelector(
            'meta[name="csrf-token"]',
        )
        ?.getAttribute(
            'content',
        )
        ?? '';
}

/*
|------------------------------------------------------------------
| Init
|------------------------------------------------------------------
*/

export function initUpdateNote()
{
    /*
    |--------------------------------------------------------------
    | Anti double init global
    |--------------------------------------------------------------
    */

    if (
        document.body.dataset
            .updateNoteInit
        === 'true'
    ) {
        return;
    }

    document.body.dataset
        .updateNoteInit =
            'true';

    /*
    |--------------------------------------------------------------
    | State
    |--------------------------------------------------------------
    */

    let isSavingNotes =
        false;

    /*
    |--------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------
    */

    function getDetailCard()
    {
        return document.querySelector(
            '.js-detail-card',
        );
    }

    function refreshNoteButtonsState()
    {
        const mangaDetailCard =
            getDetailCard();

        if (!mangaDetailCard) {
            return;
        }

        const noteGroups =
            mangaDetailCard.querySelectorAll(
                '.ajax-note-group',
            );

        noteGroups.forEach(
            group =>
            {
                const fieldName =
                    group.dataset.field;

                const currentValue =
                    Number(
                        mangaDetailCard.dataset[
                            fieldName
                        ] ?? '',
                    );

                group
                    .querySelectorAll(
                        '.ajax-note-button',
                    )
                    .forEach(
                        button =>
                        {
                            const buttonValue =
                                Number(
                                    button.dataset.value,
                                );

                            button.classList.toggle(
                                'active',
                                currentValue
                                === buttonValue,
                            );

                            button.disabled =
                                isSavingNotes;
                        },
                    );
            },
        );
    }

    async function saveNotes(
        fieldName,
        value,
    )
    {
        const mangaDetailCard =
            getDetailCard();

        if (!mangaDetailCard) {
            return;
        }

        const mangaSlug =
            mangaDetailCard.dataset.slug;

        const mangaNumero =
            mangaDetailCard.dataset.numero;

        const basePath =
            mangaDetailCard.dataset.basePath;

        const totalNoteElement =
            document.getElementById(
                'ajax-note-total',
            );

        const formData =
            new FormData();

        /*
        |----------------------------------------------------------
        | Existing values
        |----------------------------------------------------------
        */

        const currentJacquette =
            mangaDetailCard.dataset.jacquette
            || '0';

        const currentLivreNote =
            mangaDetailCard.dataset.livreNote
            || '0';

        formData.append(
            'jacquette',
            fieldName === 'jacquette'
                ? String(value)
                : currentJacquette,
        );

        formData.append(
            'livre_note',
            fieldName === 'livreNote'
                ? String(value)
                : currentLivreNote,
        );

        /*
        |----------------------------------------------------------
        | CSRF
        |----------------------------------------------------------
        */

        const csrfToken =
            getCsrfToken();

        if (csrfToken !== '') {
            formData.append(
                'csrf_token',
                csrfToken,
            );
        }

        try {

            isSavingNotes = true;

            refreshNoteButtonsState();

            const response =
                await fetch(
                    `${basePath}manga/ajax/update-note/${mangaSlug}/${mangaNumero}`,
                    {
                        method: 'POST',

                        headers:
                        {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },

                        body: formData,
                    },
                );

            const data =
                await response.json();

            if (
                !response.ok
                || !data.success
            ) {
                throw new Error(
                    data.message
                    ?? 'Erreur',
                );
            }

            /*
            |------------------------------------------------------
            | Update dataset
            |------------------------------------------------------
            */

            const notes =
                data.data?.notes
                ?? null;

            if (
                notes?.jacquette
                !== undefined
            ) {
                mangaDetailCard.dataset.jacquette =
                    String(
                        notes.jacquette,
                    );
            }

            if (
                notes?.livreNote
                !== undefined
            ) {
                mangaDetailCard.dataset.livreNote =
                    String(
                        notes.livreNote,
                    );
            }

            /*
            |------------------------------------------------------
            | Update total
            |------------------------------------------------------
            */

            if (
                totalNoteElement
            ) {
                totalNoteElement.textContent =
                    notes?.note !== null
                    && notes?.note !== undefined
                        ? `${notes.note}/10`
                        : 'Non calculée';
            }

            refreshNoteButtonsState();

            showToast(
                data.message
                ?? '✓ Sauvegardé',
                'success',
            );

        } catch (error) {

            console.error(
                error,
            );

            showToast(
                error?.message
                ?? 'Erreur',
                'error',
            );

        } finally {

            isSavingNotes = false;

            refreshNoteButtonsState();
        }
    }

    /*
    |--------------------------------------------------------------
    | Delegation click
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        async event =>
        {
            const button =
                event.target.closest(
                    '.ajax-note-button',
                );

            if (!button) {
                return;
            }

            const mangaDetailCard =
                getDetailCard();

            if (!mangaDetailCard) {
                return;
            }

            if (isSavingNotes) {
                return;
            }

            const noteGroup =
                button.closest(
                    '.ajax-note-group',
                );

            if (!noteGroup) {
                return;
            }

            const fieldName =
                noteGroup.dataset.field;

            if (!fieldName) {
                return;
            }

            const value =
                Number(
                    button.dataset.value,
                );

            /*
            |------------------------------------------------------
            | Local update
            |------------------------------------------------------
            */

            if (
                fieldName
                === 'jacquette'
            ) {
                mangaDetailCard.dataset.jacquette =
                    String(value);
            }

            if (
                fieldName
                === 'livreNote'
            ) {
                mangaDetailCard.dataset.livreNote =
                    String(value);
            }

            refreshNoteButtonsState();

            await saveNotes(
                fieldName,
                value,
            );
        },
    );

    /*
    |--------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------
    */

    refreshNoteButtonsState();

    /*
    |--------------------------------------------------------------
    | Re-sync after AJAX
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'ajax:series-loaded',
        () =>
        {
            refreshNoteButtonsState();
        },
    );
}