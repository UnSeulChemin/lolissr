// =========================================
// PAGE TRANSITIONS
// =========================================

import {
    debug,
} from './debug.js';

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
// OUT
// =========================================

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

    element.classList.add(
        'page-transition-out',
    );
}

// =========================================
// IN
// =========================================

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
        'page-transition-out',
    );

    element.classList.add(
        'page-transition-enter',
    );

    requestAnimationFrame(
        () =>
        {
            if (
                !element
                || !element.isConnected
            ) {
                return;
            }

            element.classList.add(
                'page-transition-visible',
            );
        },
    );
}

// =========================================
// SPA WRAPPER
// =========================================

export async function runPageTransition(
    callback,
)
{
    await Promise.resolve();

    callback();
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