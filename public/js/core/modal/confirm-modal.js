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
                    if (! document.body.contains( overlay ))
                    {

                        return;
                    }

                    document.body.style.overflow = '';

                    document.removeEventListener('keydown', handleEscape);

                    overlay.remove();

                    resolve(result);
                };

            const handleEscape = (event) =>
                {
                    if (event.key === 'Escape')
                    {

                        close(false);
                    }
                };

            document.body.append(overlay);

            document.body.style.overflow = 'hidden';

            document.addEventListener('keydown', handleEscape);

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
