// =========================================
// PAGE TRANSITIONS
// =========================================

import {
    debug,
} from './debug.js';

// =========================================
// CONFIG
// =========================================

const CONTENT_SELECTOR =
    '.app-content';

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
// NATIVE VIEW TRANSITION
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
    const currentContent =
        document.querySelector(
            CONTENT_SELECTOR,
        );

    // =====================================
    // NO CONTENT
    // =====================================

    if (
        !currentContent
    ) {

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
    // OUT TRANSITION
    // =====================================

    currentContent.classList.remove(
        'page-transition-in',
    );

    currentContent.classList.add(
        'page-transition-out',
    );

    // =====================================
    // DOM UPDATE
    // =====================================

    await callback();

    // =====================================
    // GET NEW CONTENT
    // =====================================

    const nextContent =
        document.querySelector(
            CONTENT_SELECTOR,
        );

    if (
        !nextContent
    ) {
        return;
    }

    // =====================================
    // RESET STATES
    // =====================================

    nextContent.classList.remove(
        'page-transition-out',
    );

    // =====================================
    // IN TRANSITION
    // =====================================

    nextContent.classList.add(
        'page-transition-in',
    );

    requestAnimationFrame(
        () =>
        {
            if (
                !nextContent.isConnected
            ) {
                return;
            }

            nextContent.classList.remove(
                'page-transition-in',
            );
        },
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