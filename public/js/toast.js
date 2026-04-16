export function showToast(
    message = 'Sauvegardé',
    type = 'success'
)
{
    const toast = document.getElementById('toast');

    if (!toast)
    {
        console.warn('Toast introuvable (#toast)');
        return;
    }

    toast.classList.remove(
        'toast-success',
        'toast-error',
        'show'
    );

    const classType =
        type === 'error'
            ? 'toast-error'
            : 'toast-success';

    toast.classList.add(classType);

    toast.textContent = message;

    toast.offsetHeight;

    toast.classList.add('show');

    if (window.__toastTimeout)
    {
        clearTimeout(window.__toastTimeout);
    }

    window.__toastTimeout = setTimeout(() =>
    {
        toast.classList.remove('show');
    }, 2500);
}