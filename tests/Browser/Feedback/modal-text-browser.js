export async function runBrowserScenario()
{
    const {confirmModal} = await import('./js/core/modal/confirm-modal.js');
    const {alertModal} = await import('./js/core/modal/alert-modal.js');
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
    for (const mode of ['button', 'escape', 'outside'])
    {
        const pending = alertModal({title: text, message: text, buttonText: text});
        const overlay = inspect(['h3', 'p', '.confirm-modal-primary']);
        check(document.activeElement === overlay.querySelector('button'), 'Alert button not focused');
        if (mode === 'button') overlay.querySelector('button').click();
        else if (mode === 'escape') document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape'}));
        else overlay.click();
        await pending;
        closed();
    }
    return ['HTML-like labels stay literal in confirm/alert modals', 'normal and danger confirmation', 'cancel, Escape and outside click', 'defaults, focus and scroll restoration'];
}
