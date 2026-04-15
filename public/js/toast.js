export function showToast(
    message = 'Sauvegardé',
    type = 'success'
)
{
    const toast = document.getElementById('toast');

    if (!toast)
    {
        return;
    }

    // Reset classes
    toast.classList.remove(
        'toast-success',
        'toast-error'
    );

    // Applique type
    toast.classList.add(
        type === 'error'
            ? 'toast-error'
            : 'toast-success'
    );

    toast.textContent = message;

    toast.classList.add('show');

    clearTimeout(window.__toastTimeout);

    window.__toastTimeout = setTimeout(() =>
    {
        toast.classList.remove('show');
    }, 2500);
}