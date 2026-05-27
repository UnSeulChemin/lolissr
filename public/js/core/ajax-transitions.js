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
// CONFIG
// =========================================

const TRANSITION_TIMEOUT =
    config.transitions
        .duration;

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
        'out',
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
// IN
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
        'in',
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
// VIEW TRANSITION
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