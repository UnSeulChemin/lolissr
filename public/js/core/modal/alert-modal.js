// =========================================
// ALERT MODAL
// =========================================

export function alertModal(
    {
        title,
        message,
        buttonText = 'OK',
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
                            class="confirm-modal-primary"
                            type="button"
                        >
                            ${buttonText}
                        </button>

                    </div>

                </div>
            `;

            const close =
                () =>
                {
                    document.body.style.overflow =
                        '';

                    document.removeEventListener(
                        'keydown',
                        handleEscape,
                    );

                    overlay.remove();

                    resolve();
                };

            const handleEscape =
                (
                    event,
                ) =>
                {
                    if (
                        event.key === 'Escape'
                    ) {

                        close();
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
                    '.confirm-modal-primary',
                )
                ?.focus();

            overlay
                .querySelector(
                    '.confirm-modal-primary',
                )
                ?.addEventListener(
                    'click',
                    close,
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

                        close();
                    }
                },
            );
        },
    );
}