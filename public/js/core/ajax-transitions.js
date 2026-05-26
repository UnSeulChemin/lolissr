// =========================================
// AJAX TRANSITIONS
// =========================================

import {
    debug,
} from '../core/debug.js';

import {
    config,
} from '../core/config.js';

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
// Animate Out
// =========================================

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

    debug(
        'TRANSITION',
        'animate out',
    );

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

// =========================================
// Animate In
// =========================================

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

    debug(
        'TRANSITION',
        'animate in',
    );

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

// =========================================
// View Transition API
// =========================================

export async function runViewTransition(
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
                async () =>
                {
                    await callback();
                },
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