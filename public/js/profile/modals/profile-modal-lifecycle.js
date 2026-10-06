import { registerCleanup } from '../../router/lifecycle/cleanup.js';

let activeClose = null;

export function mountProfileModal(overlay, resolve)
{
    activeClose?.();
    const previousFocus = document.activeElement;
    const previousOverflow = document.body.style.overflow;
    const dialog = overlay.querySelector('.confirm-modal');
    dialog.setAttribute('role', 'dialog');
    dialog.setAttribute('aria-modal', 'true');
    dialog.setAttribute('aria-label', dialog.querySelector('h3')?.textContent.trim() ?? 'Personnalisation');
    dialog.tabIndex = -1;
    let closed = false;
    let unregister = () =>
    {};
    const close = (result = null) =>
    {
        if (closed) return;
        closed = true;
        unregister();
        document.removeEventListener('keydown', onKey, true);
        overlay.remove();
        document.body.style.overflow = previousOverflow;
        if (activeClose === close) activeClose = null;
        if (previousFocus?.isConnected) previousFocus.focus();
        resolve(result);
    };
    const onKey = event =>
    {
        if (event.key === 'Escape')
        {
            event.preventDefault();
            event.stopImmediatePropagation();
            close();
        }
        if (event.key === 'Tab')
        {
            const buttons = [...dialog.querySelectorAll('button:not(:disabled)')];
            const index = buttons.indexOf(document.activeElement);
            event.preventDefault();
            if (!buttons.length) dialog.focus();
            else buttons[(index + (event.shiftKey ? -1 : 1) + buttons.length) % buttons.length].focus();
        }
    };
    activeClose = close;
    unregister = registerCleanup(() => close());
    document.body.append(overlay);
    document.body.style.overflow = 'hidden';
    document.addEventListener('keydown', onKey, true);
    (dialog.querySelector('button') ?? dialog).focus();
    return close;
}
