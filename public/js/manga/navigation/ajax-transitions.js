// ==================================================
// AJAX Transitions
// ==================================================

function waitTransitionEnd(
    element,
    timeout = 250,
)
{
    return new Promise(
        (resolve) =>
        {
            let resolved =
                false;

            const cleanup =
                () =>
                {
                    if (resolved) {
                        return;
                    }

                    resolved = true;

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
            );
        },
    );
}

/*
|------------------------------------------------------------------
| Animate Out
|------------------------------------------------------------------
*/

export async function animateContentOut(
    content,
)
{
    content.classList.remove(
        'page-transition-in',
        'page-transition-visible',
    );

    // Force reflow
    void content.offsetWidth;

    content.classList.add(
        'page-transition-out',
    );

    await waitTransitionEnd(
        content,
    );
}

/*
|------------------------------------------------------------------
| Animate In
|------------------------------------------------------------------
*/

export async function animateContentIn(
    content,
)
{
    content.classList.remove(
        'page-transition-out',
    );

    content.classList.add(
        'page-transition-in',
    );

    // Force reflow
    void content.offsetWidth;

    requestAnimationFrame(
        () =>
        {
            content.classList.add(
                'page-transition-visible',
            );
        },
    );

    await waitTransitionEnd(
        content,
    );

    content.classList.remove(
        'page-transition-in',
        'page-transition-visible',
    );
}