// ==================================================
// Page Transition
// ==================================================

let transitionId =
    0;

/*
|------------------------------------------------------------------
| Run Page Transition
|------------------------------------------------------------------
*/

export async function runPageTransition(
    callback,
)
{
    const currentId =
        ++transitionId;

    // ==============================================
    // View Transition API
    // ==============================================

    if (
        typeof document
            .startViewTransition
        === 'function'
    ) {

        let transition;

        try {

            transition =
                document
                    .startViewTransition(
                        async () =>
                        {
                            if (
                                currentId
                                !== transitionId
                            ) {
                                return;
                            }

                            await callback();
                        },
                    );

        } catch {

            await callback();

            return;
        }

        try {

            await transition.finished;

        } catch {

            // Browser aborted transition
            // Ignore silently
        }

        return;
    }

    // ==============================================
    // Fallback
    // ==============================================

    await callback();
}

// ==================================================
// Transition Out
// ==================================================

export function transitionOut(
    element,
)
{
    if (
        !element
        || !element.isConnected
    ) {
        return;
    }

    element.classList.remove(
        'page-transition-enter',
    );

    // Force reflow

    void element.offsetWidth;

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
    if (
        !element
        || !element.isConnected
    ) {
        return;
    }

    element.classList.remove(
        'page-transitioning',
    );

    // Force reflow

    void element.offsetWidth;

    element.classList.add(
        'page-transition-enter',
    );

    let cleaned =
        false;

    const cleanup =
        (
            event,
        ) =>
        {
            if (cleaned) {
                return;
            }

            if (
                event
                && event.target
                    !== element
            ) {
                return;
            }

            cleaned =
                true;

            if (
                !element.isConnected
            ) {
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

    // Fallback timeout

    const timeout =
        window.setTimeout(
            cleanup,
            250,
        );

    element.addEventListener(
        'transitionend',
        (
            event,
        ) =>
        {
            clearTimeout(
                timeout,
            );

            cleanup(
                event,
            );
        },
        {
            once: true,
        },
    );
}

// ==================================================
// Scroll
// ==================================================

export function scrollTop(
    smooth = false,
)
{
    if (
        document.body.dataset
            .ajaxNavigating
        === 'true'
    ) {
        return;
    }

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
    // Prevent first paint transition bugs

    window.addEventListener(
        'load',
        () =>
        {
            requestAnimationFrame(
                () =>
                {
                    requestAnimationFrame(
                        () =>
                        {
                            document.body.classList.add(
                                'page-ready',
                            );
                        },
                    );
                },
            );
        },
        {
            once: true,
        },
    );
}