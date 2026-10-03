// =================================================
// COPIE
// =================================================

import { showToast } from '../toast.js';

// =================================================
// COPIE DU TEXTE
// =================================================

export async function copyText(text)
{
    if (!text)
    {

        return false;
    }

    try
    {

        await navigator.clipboard.writeText(text);

        showToast('Copié !', 'success');

        return true;

    } catch {

        showToast('Impossible de copier', 'error');

        return false;
    }
}