let toastTimeout = null;

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

    const classType = type === 'error'
        ? 'toast-error'
        : 'toast-success';

    toast.classList.add(classType);
    toast.textContent = message;

    void toast.offsetWidth;

    toast.classList.add('show');

    if (toastTimeout)
    {
        clearTimeout(toastTimeout);
    }

    toastTimeout = setTimeout(() =>
    {
        toast.classList.remove('show');
    }, 2500);
}