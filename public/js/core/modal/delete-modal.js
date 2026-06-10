// =========================================
// DELETE MODAL
// =========================================

import {
    confirmModal,
} from './confirm-modal.js';

// =========================================
// DELETE
// =========================================

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