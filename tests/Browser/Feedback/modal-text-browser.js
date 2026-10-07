export async function runBrowserScenario()
{
    const {confirmModal} = await import('./js/core/modal/confirm-modal.js');
    const {runCleanup} = await import('./js/router/lifecycle/cleanup.js');
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    const text = '<img src="x" onerror="window.modalInjected=true"> & <b>texte</b> "test"';
    const inspect = (labels) =>
    {
        const overlay = document.querySelector('.confirm-modal-overlay');
        check(overlay !== null && overlay.querySelector('img, b') === null, 'Labels were interpreted as HTML');
        for (const selector of labels) check(overlay.querySelector(selector).textContent === text, 'Label text changed');
        check(document.body.style.overflow === 'hidden', 'Scroll was not locked');
        return overlay;
    };
    const closed = () =>
    {
        check(!document.querySelector('.confirm-modal-overlay'), 'Overlay remained after closing');
        check(document.body.style.overflow === '', 'Scroll remained locked');
        check(window.modalInjected !== true, 'Injected handler executed');
    };
    for (const danger of [false, true])
    {
        const pending = confirmModal({title: text, message: text, confirmText: text, cancelText: text, danger});
        const selector = danger ? '.confirm-modal-danger' : '.confirm-modal-primary';
        const overlay = inspect(['h3', 'p', '.confirm-modal-secondary', selector]);
        check(document.activeElement === overlay.querySelector(selector), 'Confirm button not focused');
        const dialog = overlay.querySelector('.confirm-modal');
        check(dialog.getAttribute('role') === 'dialog' && dialog.getAttribute('aria-modal') === 'true', 'Missing dialog semantics');
        const tab = (shiftKey) => document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Tab', shiftKey, bubbles: true, cancelable: true}));
        check(tab(false) === false && document.activeElement === overlay.querySelector('.confirm-modal-secondary'), 'Tab escaped modal');
        tab(false);
        check(document.activeElement === overlay.querySelector(selector), 'Tab did not wrap');
        tab(true);
        check(document.activeElement === overlay.querySelector('.confirm-modal-secondary'), 'Shift+Tab escaped modal');
        overlay.querySelector(selector).click();
        check(await pending === true, 'Confirm did not resolve true');
        closed();
    }
    for (const mode of ['cancel', 'escape', 'outside'])
    {
        const pending = confirmModal({title: text, message: text});
        const overlay = inspect(['h3', 'p']);
        check(overlay.querySelector('.confirm-modal-primary').textContent === 'Confirmer', 'Default confirm label changed');
        check(overlay.querySelector('.confirm-modal-secondary').textContent === 'Annuler', 'Default cancel label changed');
        if (mode === 'cancel') overlay.querySelector('.confirm-modal-secondary').click();
        else if (mode === 'escape') document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape'}));
        else overlay.click();
        check(await pending === false, 'Cancel did not resolve false');
        closed();
    }
    const focus = document.createElement('button');
    document.body.append(focus);
    focus.focus();
    document.body.style.overflow = 'auto';
    const pending = confirmModal({title: 'Navigation', message: 'Pending'});
    runCleanup();
    check(await pending === false && !document.querySelector('.confirm-modal-overlay'), 'Cleanup failed to cancel confirmation');
    check(document.body.style.overflow === 'auto' && document.activeElement === focus, 'Previous scroll/focus was not restored');
    document.body.style.overflow = '';
    focus.remove();
    const first = confirmModal({title: 'First'});
    const second = confirmModal({title: 'Second'});
    check(await first === false && document.querySelectorAll('.confirm-modal-overlay').length === 1, 'Overlapping modals were not reconciled');
    document.querySelector('.confirm-modal-secondary').click();
    check(await second === false, 'Second modal did not close');
    return ['HTML-like labels stay literal in confirmation modals', 'normal and danger confirmation', 'cancel, Escape and outside click', 'defaults, focus and scroll restoration', 'navigation cancels pending confirmation', 'single active confirmation'];
}
