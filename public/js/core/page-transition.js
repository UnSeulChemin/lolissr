export async function runPageTransition(
    callback,
)
{
    if (
        document.startViewTransition
    ) {

        return document
            .startViewTransition(
                callback,
            )
            .finished;
    }

    await callback();
}

export function transitionOut(
    element,
)
{
    if (!element) {
        return;
    }

    element.classList.add(
        'page-transitioning',
    );
}

export function transitionIn(
    element,
)
{
    if (!element) {
        return;
    }

    element.classList.remove(
        'page-transitioning',
    );

    element.classList.add(
        'page-transition-enter',
    );

    setTimeout(
        () =>
        {
            element.classList.remove(
                'page-transition-enter',
            );
        },
        220,
    );
}

export function smoothScrollTop()
{
    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
}

// ==================================================
// Page Transitions
// ==================================================

export function initPageTransitions()
{
    document.body.classList.add(
        'page-ready',
    );
}