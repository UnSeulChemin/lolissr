// =========================================
// MODIFIER PAGE
// =========================================

import {
    $,
} from '../../core/dom.js';

import {
    debug,
} from '../../core/debug.js';

// =========================================
// Config
// =========================================

const FORM_SELECTOR =
    '.form-layout[data-form-page="modifier"]';

// =========================================
// Helpers
// =========================================

function getNumberValue(
    input,
)
{
    return Number(
        input.value || 0,
    );
}

function formatNote(
    value,
)
{
    return `${value}/10`;
}

// =========================================
// Init
// =========================================

export function initModifierPage()
{
    // =====================================
    // Form
    // =====================================

    const form =
        $(
            FORM_SELECTOR,
        );

    if (!form) {
        return;
    }

    // =====================================
    // Prevent Double Init
    // =====================================

    if (
        form.dataset.modifierPageInitialized
        === 'true'
    ) {
        return;
    }

    form.dataset.modifierPageInitialized =
        'true';

    // =====================================
    // Inputs
    // =====================================

    const jacquetteInput =
        $(
            '#jacquette',
        );

    const livreNoteInput =
        $(
            '#livre_note',
        );

    const totalNoteInput =
        $(
            '#note-total',
        );

    if (
        !(
            jacquetteInput
            instanceof HTMLInputElement
        )
        || !(
            livreNoteInput
            instanceof HTMLInputElement
        )
        || !(
            totalNoteInput
            instanceof HTMLInputElement
        )
    ) {

        debug(
            'MODIFIER',
            'missing inputs',
        );

        return;
    }

    // =====================================
    // Update Total
    // =====================================

    function updateTotalNote()
    {
        if (
            jacquetteInput.value === ''
            || livreNoteInput.value === ''
        ) {

            totalNoteInput.value =
                'Non calculée';

            return;
        }

        const total =
            getNumberValue(
                jacquetteInput,
            )
            + getNumberValue(
                livreNoteInput,
            );

        totalNoteInput.value =
            formatNote(
                total,
            );
    }

    // =====================================
    // Events
    // =====================================

    jacquetteInput.addEventListener(
        'input',
        updateTotalNote,
    );

    livreNoteInput.addEventListener(
        'input',
        updateTotalNote,
    );

    // =====================================
    // Initial State
    // =====================================

    updateTotalNote();

    debug(
        'MODIFIER',
        'initialized',
    );
}