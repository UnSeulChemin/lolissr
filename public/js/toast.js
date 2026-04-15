export function showToast(message = '✓ Sauvegardé')
{
    const toast = document.getElementById('toast');

    if (!toast)
    {
        return;
    }

    toast.textContent = message;
    toast.classList.add('show');

    clearTimeout(window.__toastTimeout);

    window.__toastTimeout = setTimeout(() =>
    {
        toast.classList.remove('show');
    }, 2000);
}