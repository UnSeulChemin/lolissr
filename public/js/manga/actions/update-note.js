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
// STATE
// =========================================

let initialized =
    false;

let isSavingNotes =
    false;

// =========================================
// HELPERS
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

    return post(
        `${card.dataset.basePath}manga/ajax/update-note/${card.dataset.slug}/${card.dataset.numero}`,
        {
            jacquette:
                fieldName === 'jacquette'
                    ? value
                    : Number(
                        card.dataset.jacquette,
                    ) || 0,

            livre_note:
                fieldName === 'livreNote'
                    ? value
                    : Number(
                        card.dataset.livreNote,
                    ) || 0,
        },
        {
            headers: {
                Accept:
                    'application/json',
            },
        },
    );
}

// =========================================
// UPDATE
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
            || 'Sauvegardé',
            'success',
        );

    } catch (error) {

        debugError(
            'NOTE',
            error,
        );

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
// INIT
// =========================================

export function initUpdateNote()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

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

    document.addEventListener(
        'ajax:page-loaded',
        () =>
        {
            refreshNoteButtonsState();

            updateTotalNote();
        },
        {
            passive: true,
        },
    );

    refreshNoteButtonsState();

    updateTotalNote();

    debug(
        'NOTE',
        'initialized',
    );
}