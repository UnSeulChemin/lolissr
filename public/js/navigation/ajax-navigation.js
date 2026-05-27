// =========================================
// AJAX NAVIGATION
// =========================================

import {
    normalizeUrl,
    shouldIgnoreLink,
} from '../core/navigation.js';

import {
    getPrefetchedPage,
    getInFlightPrefetch,
} from './prefetch.js';

import {
    fetchPageHtml,
} from './ajax-fetch.js';

import {
    replaceContent,
} from './ajax-dom.js';

import {
    runPageTransition,
    scrollTop,
} from '../core/page-transition.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

// =========================================
// STATE
// =========================================

let navigationId =
    0;

let controller =
    null;

let locked =
    false;

let initialized =
    false;

// =========================================
// ACTIVE NAVIGATION
// =========================================

function updateActiveNavigation()
{
    const currentPath =
        new URL(
            normalizeUrl(
                location.href,
            ),
        ).pathname;

    const links =
        document.querySelectorAll(
            '.nav-link-icon',
        );

    for (const link of links)
    {
        if (
            !(
                link
                instanceof HTMLAnchorElement
            )
        ) {
            continue;
        }

        const hrefPath =
            new URL(
                normalizeUrl(
                    link.href,
                ),
            ).pathname;

        const isRoot =
            hrefPath === '/lolissr/';

        const active =
            isRoot
                ? currentPath === hrefPath
                : (
                    currentPath === hrefPath
                    || currentPath.startsWith(
                        hrefPath,
                    )
                );

        link.classList.toggle(
            'active',
            active,
        );
    }
}

// =========================================
// NAVIGATE
// =========================================

export async function navigateTo(
    href,
    options = {},
)
{
    if (
        locked
        && options.force !== true
    ) {
        return;
    }

    const target =
        normalizeUrl(
            href,
        );

    const current =
        normalizeUrl(
            location.href,
        );

    // =====================================
    // SAME URL
    // =====================================

    if (
        target === current
        && options.force !== true
    ) {
        return;
    }

    const currentId =
        ++navigationId;

    // =====================================
    // ABORT PREVIOUS
    // =====================================

    controller?.abort();

    controller =
        new AbortController();

    locked =
        true;

    document.body.dataset.ajaxNavigating =
        '1';

    try {

        let html =
            null;

        // =================================
        // CACHE
        // =================================

        const cached =
            getPrefetchedPage(
                target,
            );

        if (cached) {

            debug(
                'AJAX',
                'cache-hit',
                target,
            );

            html =
                cached;

        } else {

            // =============================
            // PREFETCH REUSE
            // =============================

            const inFlight =
                getInFlightPrefetch(
                    target,
                );

            if (inFlight) {

                debug(
                    'AJAX',
                    'reuse-prefetch',
                    target,
                );

                html =
                    await inFlight;

            } else {

                // =========================
                // NETWORK
                // =========================

                html =
                    await fetchPageHtml(
                        target,
                        {
                            signal:
                                controller.signal,
                        },
                    );
            }
        }

        // =================================
        // INVALID
        // =================================

        if (
            typeof html
            !== 'string'
            || html.length === 0
        ) {

            throw new Error(
                'Invalid HTML',
            );
        }

        // =================================
        // RACE CONDITION
        // =================================

        if (
            currentId
            !== navigationId
        ) {
            return;
        }

        // =================================
        // HISTORY
        // =================================

        if (
            options.updateHistory
            !== false
        ) {

            history.pushState(
                {},
                '',
                target,
            );
        }

        // =================================
        // TRANSITION
        // =================================

        await runPageTransition(
            () =>
            {
                replaceContent(
                    html,
                );

                updateActiveNavigation();
            },
        );

        // =================================
        // SCROLL
        // =================================

        if (
            options.scrollTop
            !== false
        ) {

            scrollTop(
                false,
            );
        }

        // =================================
        // EVENT
        // =================================

        document.dispatchEvent(
            new CustomEvent(
                'ajax:page-loaded',
            ),
        );

        debug(
            'AJAX',
            'done',
            target,
        );

    } catch (error) {

        if (
            error?.name
            !== 'AbortError'
        ) {

            debugError(
                'AJAX',
                error,
            );
        }

    } finally {

        if (
            currentId
            === navigationId
        ) {

            locked =
                false;

            controller =
                null;

            delete document.body.dataset.ajaxNavigating;
        }
    }
}

// =========================================
// CLICK HANDLER
// =========================================

function handleClick(
    event,
)
{
    // =====================================
    // LEFT CLICK ONLY
    // =====================================

    if (
        event.button !== 0
    ) {
        return;
    }

    // =====================================
    // MODIFIER KEYS
    // =====================================

    if (
        event.metaKey
        || event.ctrlKey
        || event.shiftKey
        || event.altKey
    ) {
        return;
    }

    const target =
        event.target;

    if (
        !(
            target
            instanceof Element
        )
    ) {
        return;
    }

    const link =
        target.closest(
            'a[href]',
        );

    if (
        !(
            link
            instanceof HTMLAnchorElement
        )
    ) {
        return;
    }

    // =====================================
    // IGNORE
    // =====================================

    if (
        shouldIgnoreLink(
            link,
        )
    ) {
        return;
    }

    const normalized =
        normalizeUrl(
            link.href,
        );

    // =====================================
    // SAME URL
    // =====================================

    if (
        normalized
        === normalizeUrl(
            location.href,
        )
    ) {

        event.preventDefault();

        return;
    }

    // =====================================
    // LOCK
    // =====================================

    if (
        locked
    ) {

        event.preventDefault();

        return;
    }

    event.preventDefault();

    void navigateTo(
        normalized,
    );
}

// =========================================
// POPSTATE
// =========================================

async function handlePopState()
{
    try {

        const html =
            await fetchPageHtml(
                location.href,
            );

        replaceContent(
            html,
        );

        updateActiveNavigation();

        document.dispatchEvent(
            new CustomEvent(
                'ajax:page-loaded',
            ),
        );

    } catch (error) {

        debugError(
            'POPSTATE',
            error,
        );
    }
}

// =========================================
// INIT
// =========================================

export function initAjaxNavigation()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    document.addEventListener(
        'click',
        handleClick,
    );

    window.addEventListener(
        'popstate',
        handlePopState,
    );

    updateActiveNavigation();

    debug(
        'AJAX',
        'ready',
    );
}