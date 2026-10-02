// =================================================
// SUPPRESSION FENÊTRE MODALE
// =================================================

import {
    confirmModal,
} from './confirm-modal.js';

// =================================================
// SUPPRESSION
// =================================================

export function deleteModal(
    message,
    title = 'Suppression',
)
{
    return confirmModal(
        {
            title,

            message,

            confirmText:
                'Supprimer',

            danger:
                true,
        },
    );
}