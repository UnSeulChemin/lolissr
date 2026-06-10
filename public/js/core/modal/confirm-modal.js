// =========================================
// CONFIRM MODAL
// =========================================

export function confirmModal(
    {
        title,
        message,
        confirmText = 'Confirmer',
        cancelText = 'Annuler',
        danger = false,
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

                    <h3>
                        ${title}
                    </h3>

                    <p>
                        ${message}
                    </p>

                    <div class="confirm-modal-actions">

                        <button
                            class="confirm-modal-secondary"
                            type="button"
                        >
                            ${cancelText}
                        </button>

                        <button
                            class="${
                                danger
                                    ? 'confirm-modal-danger'
                                    : 'confirm-modal-primary'
                            }"
                            type="button"
                        >
                            ${confirmText}
                        </button>

                    </div>

                </div>
            `;

            const confirmSelector =
                danger
                    ? '.confirm-modal-danger'
                    : '.confirm-modal-primary';

            const close =
                (
                    result,
                ) =>
                {
                    if (
                        ! document.body.contains(
                            overlay,
                        )
                    ) {

                        return;
                    }

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
                        event.key
                        === 'Escape'
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
                    confirmSelector,
                )
                ?.focus();

            overlay
                .querySelector(
                    '.confirm-modal-secondary',
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
                    confirmSelector,
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