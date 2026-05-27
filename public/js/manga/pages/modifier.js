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
// CONFIG
// =========================================

const FORM_SELECTOR =
    '.form-layout[data-form-page="modifier"]';

// =========================================
// HELPERS
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
// INIT
// =========================================

export function initModifierPage()
{
    const form =
        $(
            FORM_SELECTOR,
        );

    if (!form) {
        return;
    }

    if (
        form.dataset.modifierPageInitialized
        === 'true'
    ) {
        return;
    }

    form.dataset.modifierPageInitialized =
        'true';

    const jacquetteInput =
        $('#jacquette');

    const livreNoteInput =
        $('#livre_note');

    const totalNoteInput =
        $('#note-total');

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

    jacquetteInput.addEventListener(
        'input',
        updateTotalNote,
        {
            passive:
                true,
        },
    );

    livreNoteInput.addEventListener(
        'input',
        updateTotalNote,
        {
            passive:
                true,
        },
    );

    updateTotalNote();

    debug(
        'MODIFIER',
        'initialized',
    );
}