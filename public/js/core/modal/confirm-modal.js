import { registerCleanup } from '../../router/lifecycle/cleanup.js';

let activeClose = null;

// =================================================
// CONFIRMATION FENÊTRE MODALE
// =================================================

export function confirmModal(
    {
        title,
        message,
        confirmText = 'Confirmer',
        cancelText = 'Annuler',
        danger = false
    }
)
{
    return new Promise(
        (resolve) =>
        {
            activeClose?.(false);
            const previousOverflow = document.body.style.overflow;
            const previousFocus = document.activeElement;
            let closed = false;
            let unregister = () => {};
            const overlay = document.createElement('div');

            overlay.className = 'confirm-modal-overlay';

            overlay.innerHTML = `
                <div class="confirm-modal">

                    <h3>
                    </h3>

                    <p>
                    </p>

                    <div class="confirm-modal-actions">

                        <button
                            class="confirm-modal-secondary"
                            type="button"
                        >
                        </button>

                        <button
                            class="confirm-modal-primary"
                            type="button"
                        >
                        </button>

                    </div>

                </div>
            `;

            overlay.querySelector('h3').textContent = title ?? '';
            const dialog = overlay.querySelector('.confirm-modal');
            dialog.setAttribute('role', 'dialog');
            dialog.setAttribute('aria-modal', 'true');
            dialog.setAttribute('aria-label', title || 'Confirmation');
            overlay.querySelector('p').textContent = message ?? '';
            overlay.querySelector('.confirm-modal-secondary').textContent = cancelText;
            const confirmButton = overlay.querySelector('.confirm-modal-primary');
            confirmButton.textContent = confirmText;
            if (danger) confirmButton.className = 'confirm-modal-danger';

            const confirmSelector = danger
                    ? '.confirm-modal-danger'
                    : '.confirm-modal-primary';

            const close = (result) =>
                {
                    if (closed)
                    {

                        return;
                    }

                    closed = true;
                    unregister();
                    if (activeClose === close) activeClose = null;
                    document.body.style.overflow = previousOverflow;

                    document.removeEventListener('keydown', handleEscape, true);

                    overlay.remove();
                    if (previousFocus?.isConnected) previousFocus.focus();

                    resolve(result);
                };

            const handleEscape = (event) =>
                {
                    if (event.key === 'Escape')
                    {
                        event.preventDefault();
                        event.stopImmediatePropagation();
                        close(false);
                    }
                    if (event.key === 'Tab')
                    {
                        event.preventDefault();
                        event.stopImmediatePropagation();
                        const buttons = [...dialog.querySelectorAll('button:not(:disabled)')];
                        const index = buttons.indexOf(document.activeElement);
                        const next = index < 0 ? (event.shiftKey ? buttons.length - 1 : 0)
                            : (index + (event.shiftKey ? -1 : 1) + buttons.length) % buttons.length;
                        buttons[next]?.focus();
                    }
                };

            activeClose = close;
            unregister = registerCleanup(() => close(false));
            document.body.append(overlay);

            document.body.style.overflow = 'hidden';

            document.addEventListener('keydown', handleEscape, true);

            overlay
                .querySelector(confirmSelector)
                ?.focus();

            overlay
                .querySelector('.confirm-modal-secondary')
                ?.addEventListener('click', () => close( false ));

            overlay
                .querySelector(confirmSelector)
                ?.addEventListener('click', () => close( true ));

            overlay.addEventListener(
                'click',
                (event) =>
                {
                    if (event.target === overlay)
                    {

                        close(false);
                    }
                }
            );
        }
    );
}
