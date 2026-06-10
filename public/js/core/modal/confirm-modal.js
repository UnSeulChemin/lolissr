// =========================================
// CONFIRM MODAL
// =========================================

export function confirmModal(
    {
        title,
        message,
        confirmText = 'Confirmer',
        cancelText = 'Annuler',
    },
)
{
    return new Promise(
        (
            resolve,
        ) =>
        {
            const overlay =
                document.createElement(
                    'div',
                );

            overlay.className =
                'confirm-modal-overlay';

            overlay.innerHTML = `
                <div class="confirm-modal">
                    <h3>${title}</h3>

                    <p>${message}</p>

                    <div class="confirm-modal-actions">

                        <button
                            class="confirm-modal-cancel"
                            type="button"
                        >
                            ${cancelText}
                        </button>

                        <button
                            class="confirm-modal-confirm"
                            type="button"
                        >
                            ${confirmText}
                        </button>

                    </div>
                </div>
            `;

            const close =
                (
                    result,
                ) =>
                {
                    document.body.style.overflow =
                        '';

                    document.removeEventListener(
                        'keydown',
                        handleEscape,
                    );

                    overlay.remove();

                    resolve(
                        result,
                    );
                };

            const handleEscape =
                (
                    event,
                ) =>
                {
                    if (
                        event.key === 'Escape'
                    ) {

                        close(
                            false,
                        );
                    }
                };

            document.body.append(
                overlay,
            );

            document.body.style.overflow =
                'hidden';

            document.addEventListener(
                'keydown',
                handleEscape,
            );

            overlay
                .querySelector(
                    '.confirm-modal-confirm',
                )
                ?.focus();

            overlay
                .querySelector(
                    '.confirm-modal-cancel',
                )
                ?.addEventListener(
                    'click',
                    () =>
                        close(
                            false,
                        ),
                );

            overlay
                .querySelector(
                    '.confirm-modal-confirm',
                )
                ?.addEventListener(
                    'click',
                    () =>
                        close(
                            true,
                        ),
                );

            overlay.addEventListener(
                'click',
                (
                    event,
                ) =>
                {
                    if (
                        event.target
                        === overlay
                    ) {

                        close(
                            false,
                        );
                    }
                },
            );
        },
    );
}