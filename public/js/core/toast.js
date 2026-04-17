let toastHideTimeout = null;

/**
 * Affiche un toast temporaire.
 */
export function showToast(
    message = 'Sauvegardé',
    type = 'success'
)
{
    const toastElement = document.getElementById('toast');

    if (!toastElement)
    {
        console.warn('Toast introuvable (#toast)');
        return;
    }

    /*
    |------------------------------------------------------------------
    | Reset des classes
    |------------------------------------------------------------------
    */

    toastElement.classList.remove(
        'toast-success',
        'toast-error',
        'show'
    );

    /*
    |------------------------------------------------------------------
    | Type
    |------------------------------------------------------------------
    */

    const toastTypeClass = type === 'error'
        ? 'toast-error'
        : 'toast-success';

    toastElement.classList.add(toastTypeClass);
    toastElement.textContent = message;

    /*
    |------------------------------------------------------------------
    | Force reflow pour relancer l'animation
    |------------------------------------------------------------------
    */

    void toastElement.offsetWidth;

    toastElement.classList.add('show');

    /*
    |------------------------------------------------------------------
    | Timer précédent
    |------------------------------------------------------------------
    */

    if (toastHideTimeout)
    {
        clearTimeout(toastHideTimeout);
    }

    /*
    |------------------------------------------------------------------
    | Fermeture automatique
    |------------------------------------------------------------------
    */

    toastHideTimeout = setTimeout(() =>
    {
        toastElement.classList.remove('show');
    }, 2500);
}