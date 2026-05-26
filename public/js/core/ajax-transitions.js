// ==================================================
// AJAX Transitions
// ==================================================

const TRANSITION_TIMEOUT =
    250;

// ==================================================
// Helpers
// ==================================================

function waitTransitionEnd(
    element,
    timeout = TRANSITION_TIMEOUT,
)
{
    return new Promise(
        (resolve) =>
        {
            if (
                !element
                || !element.isConnected
            ) {

                resolve();

                return;
            }

            let resolved =
                false;

            const cleanup =
                () =>
                {
                    if (resolved) {
                        return;
                    }

                    resolved =
                        true;

                    element.removeEventListener(
                        'transitionend',
                        handleEnd,
                    );

                    clearTimeout(
                        fallback,
                    );

                    resolve();
                };

            const handleEnd =
                (
                    event,
                ) =>
                {
                    if (
                        event.target
                        !== element
                    ) {
                        return;
                    }

                    cleanup();
                };

            const fallback =
                window.setTimeout(
                    cleanup,
                    timeout,
                );

            element.addEventListener(
                'transitionend',
                handleEnd,
                {
                    once: true,
                },
            );
        },
    );
}

function forceReflow(
    element,
)
{
    void element.offsetWidth;
}

// ==================================================
// Animate Out
// ==================================================

export async function animateContentOut(
    content,
)
{
    if (
        !content
        || !content.isConnected
    ) {
        return;
    }

    content.classList.remove(
        'page-transition-in',
        'page-transition-visible',
    );

    forceReflow(
        content,
    );

    content.classList.add(
        'page-transition-out',
    );

    await waitTransitionEnd(
        content,
    );
}

// ==================================================
// Animate In
// ==================================================

export async function animateContentIn(
    content,
)
{
    if (
        !content
        || !content.isConnected
    ) {
        return;
    }

    content.classList.remove(
        'page-transition-out',
    );

    content.classList.add(
        'page-transition-in',
    );

    forceReflow(
        content,
    );

    requestAnimationFrame(
        () =>
        {
            if (
                !content.isConnected
            ) {
                return;
            }

            content.classList.add(
                'page-transition-visible',
            );
        },
    );

    await waitTransitionEnd(
        content,
    );

    if (
        !content.isConnected
    ) {
        return;
    }

    content.classList.remove(
        'page-transition-in',
        'page-transition-visible',
    );
}