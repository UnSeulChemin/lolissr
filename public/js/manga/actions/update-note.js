// ==================================================
// Update Note
// ==================================================

import {
    showToast,
} from '../../core/toast.js';

import {
    debug,
    debugError,
} from '../../core/debug.js';

// ==================================================
// State
// ==================================================

let initialized =
    false;

let isSavingNotes =
    false;

// ==================================================
// Helpers
// ==================================================

function getCsrfToken()
{
    return (
        document
            .querySelector(
                'meta[name="csrf-token"]',
            )
            ?.getAttribute(
                'content',
            )
        || ''
    );
}

function getDetailCard()
{
    return document.querySelector(
        '.js-detail-card',
    );
}

function getTotalNoteElement()
{
    return document.getElementById(
        'ajax-note-total',
    );
}

function refreshNoteButtonsState()
{
    const card =
        getDetailCard();

    if (!card) {
        return;
    }

    card.querySelectorAll(
        '.ajax-note-group',
    ).forEach(
        (
            group,
        ) =>
        {
            const fieldName =
                group.dataset.field;

            if (!fieldName) {
                return;
            }

            const currentValue =
                Number(
                    card.dataset[
                        fieldName
                    ] ?? 0,
                );

            group.querySelectorAll(
                '.ajax-note-button',
            ).forEach(
                (
                    button,
                ) =>
                {
                    if (
                        !(
                            button
                            instanceof HTMLButtonElement
                        )
                    ) {
                        return;
                    }

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

function updateTotalNote()
{
    const card =
        getDetailCard();

    const totalNoteEl =
        getTotalNoteElement();

    if (
        !card
        || !totalNoteEl
    ) {
        return;
    }

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

async function saveNotes(
    fieldName,
    value,
)
{
    const card =
        getDetailCard();

    if (!card) {
        return;
    }

    const slug =
        card.dataset.slug;

    const numero =
        card.dataset.numero;

    const basePath =
        card.dataset.basePath;

    if (
        !slug
        || !numero
        || !basePath
    ) {

        throw new Error(
            'Informations manga manquantes',
        );
    }

    const formData =
        new FormData();

    formData.append(
        'jacquette',
        fieldName === 'jacquette'
            ? String(
                value,
            )
            : (
                card.dataset.jacquette
                || '0'
            ),
    );

    formData.append(
        'livre_note',
        fieldName === 'livreNote'
            ? String(
                value,
            )
            : (
                card.dataset.livreNote
                || '0'
            ),
    );

    const csrfToken =
        getCsrfToken();

    if (csrfToken) {

        formData.append(
            'csrf_token',
            csrfToken,
        );
    }

    const response =
        await fetch(
            `${basePath}manga/ajax/update-note/${slug}/${numero}`,
            {
                method:
                    'POST',

                credentials:
                    'same-origin',

                headers:
                {
                    'X-Requested-With':
                        'XMLHttpRequest',

                    'Accept':
                        'application/json',
                },

                body:
                    formData,
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
            || 'Erreur',
        );
    }

    return data;
}

// ==================================================
// Init
// ==================================================

export function initUpdateNote()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    // ==============================================
    // Click
    // ==============================================

    document.addEventListener(
        'click',
        async (
            event,
        ) =>
        {
            const target =
                event.target;

            if (
                !(target instanceof Element)
            ) {
                return;
            }

            const button =
                target.closest(
                    '.ajax-note-button',
                );

            if (
                !(
                    button
                    instanceof HTMLButtonElement
                )
            ) {
                return;
            }

            if (
                isSavingNotes
            ) {
                return;
            }

            const card =
                getDetailCard();

            if (!card) {
                return;
            }

            const group =
                button.closest(
                    '.ajax-note-group',
                );

            if (
                !group
            ) {
                return;
            }

            const fieldName =
                group.dataset.field;

            if (!fieldName) {
                return;
            }

            const previousValue =
                card.dataset[
                    fieldName
                ] || '0';

            const value =
                Number(
                    button.dataset.value,
                );

            // ======================================
            // Optimistic UI
            // ======================================

            card.dataset[
                fieldName
            ] =
                String(
                    value,
                );

            refreshNoteButtonsState();

            updateTotalNote();

            try {

                isSavingNotes =
                    true;

                refreshNoteButtonsState();

                debug(
                    'NOTE',
                    'save',
                    fieldName,
                    value,
                );

                const data =
                    await saveNotes(
                        fieldName,
                        value,
                    );

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

                updateTotalNote();

                refreshNoteButtonsState();

                showToast(
                    data.message
                    || '✓ Sauvegardé',
                    'success',
                );

            } catch (error) {

                debugError(
                    'NOTE',
                    error,
                );

                // ==================================
                // Rollback
                // ==================================

                card.dataset[
                    fieldName
                ] =
                    previousValue;

                updateTotalNote();

                refreshNoteButtonsState();

                showToast(
                    error instanceof Error
                        ? error.message
                        : 'Erreur',
                    'error',
                );

            } finally {

                isSavingNotes =
                    false;

                refreshNoteButtonsState();
            }
        },
    );

    // ==============================================
    // Sync after AJAX
    // ==============================================

    document.addEventListener(
        'ajax:page-loaded',
        () =>
        {
            refreshNoteButtonsState();

            updateTotalNote();
        },
    );

    refreshNoteButtonsState();

    updateTotalNote();

    debug(
        'NOTE',
        'initialized',
    );
}