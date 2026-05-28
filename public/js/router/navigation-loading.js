// =========================================
// NAVIGATION LOADING
// =========================================

import {
    debug,
} from '../core/debug.js';

import {
    NAVIGATION_START,
    NAVIGATION_READY,
    NAVIGATION_ERROR,
    NAVIGATION_ABORT,
} from '../core/navigation-protocol.js';

// =========================================
// STATE
// =========================================

let loadingTimer =
    null;

// =========================================
// HELPERS
// =========================================

function showLoading()
{
    document.body.classList.add(
        'is-routing',
    );
}

function hideLoading()
{
    document.body.classList.remove(
        'is-routing',
    );
}

// =========================================
// START
// =========================================

function handleNavigationStart()
{
    clearTimeout(
        loadingTimer,
    );

    loadingTimer =
        setTimeout(
            () =>
            {
                showLoading();
            },
            80,
        );

    debug(
        'NAV_LOADING',
        'start',
    );
}

// =========================================
// END
// =========================================

function handleNavigationEnd()
{
    clearTimeout(
        loadingTimer,
    );

    hideLoading();

    debug(
        'NAV_LOADING',
        'end',
    );
}

// =========================================
// INIT
// =========================================

export function initNavigationLoading()
{
    document.addEventListener(
        NAVIGATION_START,
        handleNavigationStart,
    );

    document.addEventListener(
        NAVIGATION_READY,
        handleNavigationEnd,
    );

    document.addEventListener(
        NAVIGATION_ERROR,
        handleNavigationEnd,
    );

    document.addEventListener(
        NAVIGATION_ABORT,
        handleNavigationEnd,
    );

    debug(
        'NAV_LOADING',
        'initialized',
    );
}