// =========================================
// PAGE TRANSITIONS
// =========================================

import {
    debug,
} from './debug.js';

import {
    config,
} from './config.js';

// =========================================
// CONFIG
// =========================================

const CONTENT_SELECTOR =
    '.app-content';

const TRANSITION_DURATION =
    config.transitions
        .duration;

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

            let finished =
                false;

            function cleanup()
            {
                if (finished) {
                    return;
                }

                finished =
                    true;

                clearTimeout(
                    timeoutId,
                );

                element.removeEventListener(
                    'transitionend',
                    handleTransitionEnd,
                );

                resolve();
            }

            function handleTransitionEnd(
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

            const timeoutId =
                window.setTimeout(
                    cleanup,
                    TRANSITION_DURATION,
                );

            element.addEventListener(
                'transitionend',
                handleTransitionEnd,
            );
        },
    );
}

// =========================================
// OUT
// =========================================

async function animateOut(
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

async function animateIn(
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

async function runNativeViewTransition(
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

    } catch (error) {

        debug(
            'TRANSITION',
            'fallback',
            error,
        );

        await callback();
    }
}

// =========================================
// PAGE TRANSITION
// =========================================

export async function runPageTransition(
    callback,
)
{
    const content =
        document.querySelector(
            CONTENT_SELECTOR,
        );

    // =====================================
    // NO CONTENT
    // =====================================

    if (!content) {

        await callback();

        return;
    }

    // =====================================
    // NATIVE VIEW TRANSITIONS
    // =====================================

    if (
        typeof document.startViewTransition
        === 'function'
    ) {

        await runNativeViewTransition(
            callback,
        );

        return;
    }

    // =====================================
    // FALLBACK
    // =====================================

    await animateOut(
        content,
    );

    await callback();

    await animateIn(
        content,
    );
}

// =========================================
// SCROLL TOP
// =========================================

export function scrollTop(
    smooth = false,
)
{
    window.scrollTo({
        top:
            0,

        behavior:
            smooth
                ? 'smooth'
                : 'auto',
    });
}