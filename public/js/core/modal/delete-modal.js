// =================================================
// SUPPRESSION FENÊTRE MODALE
// =================================================

import { confirmModal } from './confirm-modal.js';

// =================================================
// SUPPRESSION
// =================================================

export function deleteModal(message, title = 'Suppression')
{
    return confirmModal(
        {
            title,

            message,

            confirmText: 'Supprimer',

            danger: true
        }
    );
}

export async function confirmDelete(button, message)
{
    if (button.disabled) return false;
    button.disabled = true;
    let confirmed = false;
    try
    {
        confirmed = await deleteModal(message) && button.isConnected;
        return confirmed;
    }
    finally
    {
        if (!confirmed)
        {
            button.disabled = false;
            if (button.isConnected && !document.querySelector('.confirm-modal-overlay')) button.focus();
        }
    }
}
