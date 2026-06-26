// =========================================
// UPDATE NOTE
// =========================================

import {
    invalidatePage,
} from '../../router/page-invalidation.js';

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
} from '../../core/debug/debug.js';

// =========================================
// CONFIG
// =========================================

const NOTE_GROUP_SELECTOR =
    '.js-note-group';

const NOTE_BUTTON_SELECTOR =
    '.js-note-button';

const TOTAL_NOTE_SELECTOR =
    '#js-note-total';

// =========================================
// STATE
// =========================================

let initialized =
    false;

let isSaving =
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
        TOTAL_NOTE_SELECTOR,
    );
}

function refreshButtons()
{
    const card =
        getDetailCard();

    if (!card) {
        return;
    }

    $$(
        NOTE_GROUP_SELECTOR,
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
                NOTE_BUTTON_SELECTOR,
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
                        isSaving;
                },
            );
        },
    );
}

function updateTotalNote()
{
    const card =
        getDetailCard();

    const totalElement =
        getTotalNoteElement();

    if (
        !card
        || !totalElement
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

    totalElement.textContent =
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
            headers:
            {
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
    if (isSaving) {
        return;
    }

    const card =
        getDetailCard();

    if (!card) {
        return;
    }

    const group =
        button.closest(
            NOTE_GROUP_SELECTOR,
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
    // OPTIMISTIC UPDATE
    // =====================================

    card.dataset[
        fieldName
    ] =
        String(
            value,
        );

    refreshButtons();

    updateTotalNote();

    try {

        isSaving =
            true;

        refreshButtons();

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

    refreshButtons();

    invalidatePage(
        window.location.pathname,
    );

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

        refreshButtons();

        showToast(
            error instanceof Error
                ? error.message
                : 'Erreur',
            'error',
        );

    } finally {

        isSaving =
            false;

        refreshButtons();
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
        NOTE_BUTTON_SELECTOR,
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
        'router:loaded',
        () =>
        {
            refreshButtons();

            updateTotalNote();
        },
        {
            passive:
                true,
        },
    );

    refreshButtons();

    updateTotalNote();

    debug(
        'NOTE',
        'initialized',
    );
}