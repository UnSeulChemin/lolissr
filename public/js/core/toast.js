let toastHideTimeout =
    null;

/**
 * Affiche un toast premium.
 */
export function showToast(
    message = 'Sauvegardé',
    type = 'success',
)
{
    const toastElement =
        document.getElementById(
            'toast',
        );

    if (!toastElement) {

        console.warn(
            'Toast introuvable (#toast)',
        );

        return;
    }

    /*
    |--------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------
    */

    toastElement.classList.remove(
        'toast-success',
        'toast-error',
        'show',
    );

    /*
    |--------------------------------------------------------------
    | Type
    |--------------------------------------------------------------
    */

    const toastTypeClass =
        type === 'error'
            ? 'toast-error'
            : type === 'info'
                ? 'toast-info'
                : 'toast-success';

    toastElement.classList.add(
        toastTypeClass,
    );

    /*
    |--------------------------------------------------------------
    | Content
    |--------------------------------------------------------------
    */

    const icon =
        type === 'error'
            ? '✕'
            : type === 'info'
                ? '✦'
                : '✓';

    toastElement.innerHTML = `
        <span class="toast-wing toast-wing-left"></span>

        <div class="toast-content">

            <span class="toast-icon">
                ${icon}
            </span>

            <span class="toast-message">
                ${message}
            </span>

        </div>

        <span class="toast-wing toast-wing-right"></span>

        <span class="toast-shine"></span>
    `;

    /*
    |--------------------------------------------------------------
    | Restart animation
    |--------------------------------------------------------------
    */

    void toastElement.offsetWidth;

    toastElement.classList.add(
        'show',
    );

    /*
    |--------------------------------------------------------------
    | Clear previous timer
    |--------------------------------------------------------------
    */

    if (toastHideTimeout) {

        clearTimeout(
            toastHideTimeout,
        );
    }

    /*
    |--------------------------------------------------------------
    | Auto hide
    |--------------------------------------------------------------
    */

    toastHideTimeout =
        setTimeout(
            () =>
            {
                toastElement.classList.remove(
                    'show',
                );
            },
            2400,
        );
}