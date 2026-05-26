import {
    showToast,
} from '../../core/toast.js';

// ==================================================
// CSRF
// ==================================================

function getCsrfToken()
{
    return document
        .querySelector(
            'meta[name="csrf-token"]',
        )
        ?.getAttribute('content')
        ?? '';
}

// ==================================================
// Init
// ==================================================

export function initUpdateNote()
{
    if (
        document.body.dataset
            .updateNoteInit
        === 'true'
    ) {
        return;
    }

    document.body.dataset
        .updateNoteInit = 'true';

    let isSavingNotes =
        false;

    // ==================================================
    // Elements
    // ==================================================

    const getDetailCard = () =>
    {
        return document.querySelector(
            '.js-detail-card',
        );
    };

    const totalNoteEl =
        document.getElementById(
            'ajax-note-total',
        );

    // ==================================================
    // UI
    // ==================================================

    const refreshNoteButtonsState = () =>
    {
        const card =
            getDetailCard();

        if (! card) {
            return;
        }

        card.querySelectorAll(
            '.ajax-note-group',
        ).forEach(
            (group) =>
            {
                const fieldName =
                    group.dataset.field;

                const currentValue =
                    Number(
                        card.dataset[
                            fieldName
                        ] ?? '',
                    );

                group.querySelectorAll(
                    '.ajax-note-button',
                ).forEach(
                    (button) =>
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
    };

    // ==================================================
    // Save
    // ==================================================

    const saveNotes = async (
        fieldName,
        value,
    ) =>
    {
        const card =
            getDetailCard();

        if (! card) {
            return;
        }

        const slug =
            card.dataset.slug;

        const numero =
            card.dataset.numero;

        const basePath =
            card.dataset.basePath;

        const formData =
            new FormData();

        formData.append(
            'jacquette',
            fieldName === 'jacquette'
                ? value
                : card.dataset.jacquette
                    || '0',
        );

        formData.append(
            'livre_note',
            fieldName === 'livreNote'
                ? value
                : card.dataset.livreNote
                    || '0',
        );

        const csrf =
            getCsrfToken();

        if (csrf !== '') {

            formData.append(
                'csrf_token',
                csrf,
            );
        }

        try {

            isSavingNotes =
                true;

            refreshNoteButtonsState();

            const response =
                await fetch(
                    `${basePath}manga/ajax/update-note/${slug}/${numero}`,
                    {
                        method: 'POST',

                        headers: {
                            'X-Requested-With':
                                'XMLHttpRequest',

                            'X-Partial':
                                'true',

                            'Accept':
                                'application/json',
                        },

                        body: formData,
                    },
                );

            const data =
                await response.json();

            if (
                ! response.ok
                || ! data.success
            ) {

                throw new Error(
                    data.message
                    ?? 'Erreur',
                );
            }

            const notes =
                data.data?.notes
                ?? {};

            if (
                notes.jacquette
                !== undefined
            ) {

                card.dataset.jacquette =
                    String(
                        notes.jacquette,
                    );
            }

            if (
                notes.livreNote
                !== undefined
            ) {

                card.dataset.livreNote =
                    String(
                        notes.livreNote,
                    );
            }

            // ==========================================
            // Total
            // ==========================================

            if (totalNoteEl) {

                const total =
                    Number(
                        card.dataset.jacquette
                        ?? 0,
                    )
                    + Number(
                        card.dataset.livreNote
                        ?? 0,
                    );

                totalNoteEl.textContent =
                    `${total}/10`;
            }

            refreshNoteButtonsState();

            showToast(
                data.message
                ?? '✓ Sauvegardé',
                'success',
            );

        } catch (error) {

            if (
                error.name
                === 'AbortError'
            ) {
                return;
            }

            console.error(
                error,
            );

            showToast(
                error?.message
                ?? 'Erreur',
                'error',
            );

        } finally {

            isSavingNotes =
                false;

            refreshNoteButtonsState();
        }
    };

    // ==================================================
    // Click
    // ==================================================

    document.addEventListener(
        'click',
        async (event) =>
        {
            const button =
                event.target.closest(
                    '.ajax-note-button',
                );

            if (! button) {
                return;
            }

            const card =
                getDetailCard();

            if (
                ! card
                || isSavingNotes
            ) {
                return;
            }

            const group =
                button.closest(
                    '.ajax-note-group',
                );

            if (! group) {
                return;
            }

            const fieldName =
                group.dataset.field;

            if (! fieldName) {
                return;
            }

            const value =
                Number(
                    button.dataset.value,
                );

            card.dataset[fieldName] =
                String(value);

            refreshNoteButtonsState();

            await saveNotes(
                fieldName,
                value,
            );
        },
    );

    // ==================================================
    // Init
    // ==================================================

    refreshNoteButtonsState();

    document.addEventListener(
        'ajax:series-loaded',
        refreshNoteButtonsState,
    );
}

document.addEventListener(
    'DOMContentLoaded',
    () =>
    {
        initUpdateNote();
    },
);