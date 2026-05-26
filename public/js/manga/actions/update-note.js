// =========================================
// UPDATE NOTE
// =========================================

import {
    post,
} from '../../core/http.js';

import {
    $,
    $$,
    delegate,
} from '../../core/dom.js';

import {
    showToast,
} from '../../core/toast.js';

import {
    debug,
    debugError,
} from '../../core/debug.js';

// =========================================
// State
// =========================================

let initialized =
    false;

let isSavingNotes =
    false;

// =========================================
// Helpers
// =========================================

function getDetailCard()
{
    return $(
        '.js-detail-card',
    );
}

function getTotalNoteElement()
{
    return $(
        '#ajax-note-total',
    );
}

function refreshNoteButtonsState()
{
    const card =
        getDetailCard();

    if (!card) {
        return;
    }

    $$(
        '.ajax-note-group',
        card,
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

            $$(
                '.ajax-note-button',
                group,
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

// =========================================
// API
// =========================================

async function saveNotes(
    fieldName,
    value,
)
{
    const card =
        getDetailCard();

    if (!card) {
        return null;
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

    return post(
        `${basePath}manga/ajax/update-note/${slug}/${numero}`,
        {
            jacquette:
                fieldName === 'jacquette'
                    ? value
                    : (
                        card.dataset.jacquette
                        || 0
                    ),

            livre_note:
                fieldName === 'livreNote'
                    ? value
                    : (
                        card.dataset.livreNote
                        || 0
                    ),
        },
        {
            headers:
            {
                'Accept':
                    'application/json',
            },
        },
    );
}

// =========================================
// Update
// =========================================

async function updateNote(
    button,
)
{
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

    if (!group) {
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

    // =====================================
    // Optimistic UI
    // =====================================

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
            data?.data?.notes
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
            data?.message
            || '✓ Sauvegardé',
            'success',
        );

    } catch (error) {

        debugError(
            'NOTE',
            error,
        );

        // =================================
        // Rollback
        // =================================

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
}

// =========================================
// Init
// =========================================

export function initUpdateNote()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    // =====================================
    // Click
    // =====================================

    delegate(
        document,
        'click',
        '.ajax-note-button',
        (
            _,
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

            void updateNote(
                button,
            );
        },
    );

    // =====================================
    // Sync AJAX
    // =====================================

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