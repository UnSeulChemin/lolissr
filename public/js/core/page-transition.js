// ==================================================
// Page Transition
// ==================================================

export async function runPageTransition(
    callback,
)
{
    if (
        typeof document.startViewTransition
        === 'function'
    ) {

        return document
            .startViewTransition(
                callback,
            )
            .finished;
    }

    await callback();
}

// ==================================================
// Transition Out
// ==================================================

export function transitionOut(
    element,
)
{
    if (! element) {
        return;
    }

    element.classList.remove(
        'page-transition-enter',
    );

    element.classList.add(
        'page-transitioning',
    );
}

// ==================================================
// Transition In
// ==================================================

export function transitionIn(
    element,
)
{
    if (! element) {
        return;
    }

    element.classList.remove(
        'page-transitioning',
    );

    element.classList.add(
        'page-transition-enter',
    );

    const cleanup = (
        event,
    ) =>
    {
        if (event.target !== element) {
            return;
        }

        element.classList.remove(
            'page-transition-enter',
        );

        element.removeEventListener(
            'transitionend',
            cleanup,
        );
    };

    element.addEventListener(
        'transitionend',
        cleanup,
    );
}

// ==================================================
// Scroll
// ==================================================

export function scrollTop(
    smooth = true,
)
{
    window.scrollTo({
        top: 0,
        behavior:
            smooth
                ? 'smooth'
                : 'auto',
    });
}

// ==================================================
// Page Ready
// ==================================================

export function initPageTransitions()
{
    requestAnimationFrame(
        () =>
        {
            document.body.classList.add(
                'page-ready',
            );
        },
    );
}