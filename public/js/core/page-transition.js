// =========================================
// PAGE TRANSITION
// =========================================

import {
    debug,
} from './debug.js';

import {
    config,
} from './config.js';

// =========================================
// Config
// =========================================

const TRANSITION_TIMEOUT =
    config.transitions
        .duration;

// =========================================
// Helpers
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

            const cleanup =
                () =>
                {
                    if (
                        resolved
                    ) {
                        return;
                    }

                    resolved =
                        true;

                    element.removeEventListener(
                        'transitionend',
                        handleEnd,
                    );

                    clearTimeout(
                        fallbackTimeout,
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

            const fallbackTimeout =
                window.setTimeout(
                    cleanup,
                    timeout,
                );

            element.addEventListener(
                'transitionend',
                handleEnd,
                {
                    once:
                        true,
                },
            );
        },
    );
}

// =========================================
// Run Page Transition
// =========================================

export async function runPageTransition(
    callback,
)
{
    // =====================================
    // Native View Transition API
    // =====================================

    if (
        typeof document.startViewTransition
        === 'function'
    ) {

        try {

            const transition =
                document.startViewTransition(
                    async () =>
                    {
                        await callback();
                    },
                );

            await transition.finished;

            return;

        } catch (
            error
        ) {

            debug(
                'TRANSITION',
                'view-transition fallback',
                error,
            );
        }
    }

    // =====================================
    // Fallback
    // =====================================

    await callback();
}

// =========================================
// Transition Out
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

    element.classList.remove(
        'page-transition-enter',
        'page-transition-visible',
    );

    forceReflow(
        element,
    );

    element.classList.add(
        'page-transitioning',
    );

    await waitTransitionEnd(
        element,
    );
}

// =========================================
// Transition In
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

    element.classList.remove(
        'page-transitioning',
    );

    forceReflow(
        element,
    );

    element.classList.add(
        'page-transition-enter',
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
        'page-transition-enter',
        'page-transition-visible',
    );
}

// =========================================
// Scroll
// =========================================

export function scrollTop(
    smooth =
        false,
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
        top:
            0,

        behavior:
            smooth
                ? 'smooth'
                : 'auto',
    });
}

// =========================================
// Init
// =========================================

export function initPageTransitions()
{
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

                            debug(
                                'TRANSITION',
                                'initialized',
                            );
                        },
                    );
                },
            );
        },
        {
            once:
                true,
        },
    );
}