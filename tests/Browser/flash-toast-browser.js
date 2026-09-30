export async function testPageStyles()
{
    const toast = document.createElement('div');
    toast.id = 'toast';
    document.body.append(toast);
    window.flashToast = {message: 'Message unique', type: 'success'};
    const {initFlashToast, showToast} = await import('./js/core/toast.js');
    if (toast.textContent !== '') throw new Error('Import displayed a flash toast');
    initFlashToast();
    if (!toast.textContent.includes('Message unique') || window.flashToast !== undefined)
        throw new Error('Flash was not displayed and consumed');
    const content = toast.querySelector('.toast-content');
    initFlashToast();
    if (content !== toast.querySelector('.toast-content')) throw new Error('Flash displayed twice');
    showToast('Action SPA', 'info');
    if (!toast.textContent.includes('Action SPA')) throw new Error('SPA notifications broken');
    return ['no import side effect', 'flash consumed once', 'SPA notifications preserved'];
}
