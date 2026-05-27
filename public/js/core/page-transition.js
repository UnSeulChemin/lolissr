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

    document.body.classList.add(
        'page-ready',
    );

    debug(
        'TRANSITION',
        'ready',
    );
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
    // DISABLE POINTERS
    // =====================================

    document.body.classList.add(
        'router-loading',
    );

    try {

        // =================================
        // SIMPLE DOM SWAP
        // =================================

        await callback();

    } finally {

        // =================================
        // RESTORE POINTERS
        // =================================

        requestAnimationFrame(
            () =>
            {
                document.body.classList.remove(
                    'router-loading',
                );
            },
        );
    }
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