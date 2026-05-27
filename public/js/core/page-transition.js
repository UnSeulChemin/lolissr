// =========================================
// PAGE TRANSITIONS
// =========================================

import {
    debug,
} from './debug.js';

// =========================================
// CONFIG
// =========================================

const TRANSITION_TIMEOUT =
    350;

// =========================================
// STATE
// =========================================

let initialized =
    false;

// =========================================
// INIT
// =========================================

export function initPageTransitions()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    requestAnimationFrame(
        () =>
        {
            document.body.classList.add(
                'page-ready',
            );

            debug(
                'TRANSITION',
                'ready',
            );
        },
    );
}

// =========================================
// HELPERS
// =========================================

function forceReflow(
    element,
)
{
    void element.offsetWidth;
}

function waitTransitionEnd(
    element,
    timeout =
        TRANSITION_TIMEOUT,
)
{
    return new Promise(
        (
            resolve,
        ) =>
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

            function cleanup()
            {
                if (resolved) {
                    return;
                }

                resolved =
                    true;

                clearTimeout(
                    fallbackTimeout,
                );

                element.removeEventListener(
                    'transitionend',
                    handleEnd,
                );

                resolve();
            }

            function handleEnd(
                event,
            )
            {
                if (
                    event.target
                    !== element
                ) {
                    return;
                }

                cleanup();
            }

            const fallbackTimeout =
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

// =========================================
// OUT
// =========================================

export async function transitionOut(
    element,
)
{
    if (
        !element
        || !element.isConnected
    ) {
        return;
    }

    debug(
        'TRANSITION',
        'out',
    );

    element.classList.remove(
        'page-transition-in',
        'page-transition-visible',
    );

    forceReflow(
        element,
    );

    element.classList.add(
        'page-transition-out',
    );

    await waitTransitionEnd(
        element,
    );
}

// =========================================
// IN
// =========================================

export async function transitionIn(
    element,
)
{
    if (
        !element
        || !element.isConnected
    ) {
        return;
    }

    debug(
        'TRANSITION',
        'in',
    );

    element.classList.remove(
        'page-transition-out',
    );

    element.classList.add(
        'page-transition-in',
    );

    forceReflow(
        element,
    );

    requestAnimationFrame(
        () =>
        {
            if (
                !element.isConnected
            ) {
                return;
            }

            element.classList.add(
                'page-transition-visible',
            );
        },
    );

    await waitTransitionEnd(
        element,
    );

    if (
        !element.isConnected
    ) {
        return;
    }

    element.classList.remove(
        'page-transition-in',
        'page-transition-visible',
    );
}

// =========================================
// VIEW TRANSITION
// =========================================

async function runViewTransition(
    callback,
)
{
    if (
        typeof document.startViewTransition
        !== 'function'
    ) {

        await callback();

        return;
    }

    try {

        const transition =
            document.startViewTransition(
                () =>
                    Promise.resolve(
                        callback(),
                    ),
            );

        await transition.finished;

    } catch (
        error
    ) {

        debug(
            'TRANSITION',
            'fallback',
            error,
        );

        await callback();
    }
}

// =========================================
// PAGE WRAPPER
// =========================================

export async function runPageTransition(
    callback,
)
{
    const content =
        document.querySelector(
            '.ajax-content',
        );

    // =====================================
    // NO CONTENT
    // =====================================

    if (!content) {

        await callback();

        return;
    }

    // =====================================
    // VIEW TRANSITION
    // =====================================

    if (
        typeof document.startViewTransition
        === 'function'
    ) {

        await runViewTransition(
            callback,
        );

        return;
    }

    // =====================================
    // FALLBACK
    // =====================================

    await transitionOut(
        content,
    );

    await callback();

    await transitionIn(
        content,
    );
}

// =========================================
// SCROLL
// =========================================

export function scrollTop(
    smooth = false,
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