// =========================================
// UPDATE NOTE
// =========================================

import {
    $,
    $$,
    delegate,
} from '../../core/dom.js';

import {
    debug,
    debugError,
} from '../../core/debug/debug.js';

import {
    post,
} from '../../core/http.js';

import {
    showToast,
} from '../../core/toast.js';

import {
    invalidateMangaPages,
} from '../manga-cache.js';

// =========================================
// CONFIG
// =========================================

const DETAIL_CARD_SELECTOR =
    '.js-detail-card';

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
        DETAIL_CARD_SELECTOR,
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

    const dataset =
        card.dataset;

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
                    dataset[
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

                    button.classList.toggle(
                        'active',
                        currentValue
                        === Number(
                            button.dataset.value,
                        ),
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

    const {
        jacquette,
        livreNote,
    } = card.dataset;

    totalElement.textContent =
        `${Number(jacquette ?? 0) + Number(livreNote ?? 0)}/10`;
}

function refreshNoteUi()
{
    refreshButtons();

    updateTotalNote();
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

    const {
        basePath,
        slug,
        numero,
        jacquette,
        livreNote,
    } = card.dataset;

    return post(
        `${basePath}manga/ajax/update-note/${slug}/${numero}`,
        {
            jacquette:
                fieldName === 'jacquette'
                    ? value
                    : Number(
                        jacquette,
                    ) || 0,

            livre_note:
                fieldName === 'livreNote'
                    ? value
                    : Number(
                        livreNote,
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

    const dataset =
        card.dataset;

    const previousValue =
        dataset[
            fieldName
        ] || '0';

    const value =
        Number(
            button.dataset.value,
        );

    dataset[
        fieldName
    ] =
        String(
            value,
        );

    refreshNoteUi();

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
            dataset.jacquette =
                String(
                    notes.jacquette,
                );
        }

        if (
            notes.livreNote
            !== undefined
        ) {
            dataset.livreNote =
                String(
                    notes.livreNote,
                );
        }

        refreshNoteUi();

        invalidateMangaPages();

        showToast(
            data?.message
            ?? 'Sauvegardé',
            'success',
        );

    } catch (error) {

        debugError(
            'NOTE',
            error,
        );

        dataset[
            fieldName
        ] =
            previousValue;

        refreshNoteUi();

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
        refreshNoteUi,
        {
            passive:
                true,
        },
    );

    refreshNoteUi();

    debug(
        'NOTE',
        'initialized',
    );
}