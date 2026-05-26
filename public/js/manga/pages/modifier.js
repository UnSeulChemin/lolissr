// ==================================================
// Modifier Page
// ==================================================

import {
    debug,
} from '../../core/debug.js';

// ==================================================
// Config
// ==================================================

const FORM_SELECTOR =
    '.form-layout[data-form-page="modifier"]';

// ==================================================
// Helpers
// ==================================================

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

// ==================================================
// Init
// ==================================================

export function initModifierPage()
{
    // ==============================================
    // Form
    // ==============================================

    const form =
        document.querySelector(
            FORM_SELECTOR,
        );

    if (!form) {
        return;
    }

    // ==============================================
    // Prevent double init
    // ==============================================

    if (
        form.dataset.modifierPageInitialized
        === 'true'
    ) {
        return;
    }

    form.dataset.modifierPageInitialized =
        'true';

    // ==============================================
    // Inputs
    // ==============================================

    const jacquetteInput =
        document.getElementById(
            'jacquette',
        );

    const livreNoteInput =
        document.getElementById(
            'livre_note',
        );

    const totalNoteInput =
        document.getElementById(
            'note-total',
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

    // ==============================================
    // Update total note
    // ==============================================

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

    // ==============================================
    // Events
    // ==============================================

    jacquetteInput.addEventListener(
        'input',
        updateTotalNote,
    );

    livreNoteInput.addEventListener(
        'input',
        updateTotalNote,
    );

    // ==============================================
    // Initial state
    // ==============================================

    updateTotalNote();

    debug(
        'MODIFIER',
        'initialized',
    );
}