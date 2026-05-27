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
    markNavigationPrefetch,
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
// ACTIVE NAV
// =========================================

function updateActiveNavigation()
{
    const current =
        new URL(
            normalizeUrl(
                location.href,
            ),
        );

    const currentPath =
        current.pathname;

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

        const href =
            new URL(
                normalizeUrl(
                    link.href,
                ),
            );

        const hrefPath =
            href.pathname;

        let active =
            false;

        if (
            hrefPath
            === '/lolissr/'
        ) {

            active =
                currentPath
                === '/lolissr/';

        } else {

            active =
                currentPath === hrefPath
                || currentPath.startsWith(
                    hrefPath,
                );
        }

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

    const currentId =
        ++navigationId;

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

    // =====================================
    // PREVENT PREFETCH AFTER CLICK
    // =====================================

    markNavigationPrefetch(
        target,
    );

    // =====================================
    // ABORT
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

        let instant =
            false;

        // =================================
        // CACHE
        // =================================

        const cached =
            getPrefetchedPage(
                target,
            );

        if (cached) {

            html =
                cached;

            instant =
                true;

        } else {

            // =============================
            // PREFETCH REUSE
            // =============================

            const prefetchPromise =
                getInFlightPrefetch(
                    target,
                );

            if (prefetchPromise) {

                html =
                    await prefetchPromise;

                instant =
                    true;

            } else {

                // =========================
                // FETCH
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
                'Empty HTML',
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
        // DOM SWAP
        // =================================

        if (instant) {

            replaceContent(
                html,
            );

            updateActiveNavigation();

        } else {

            await runPageTransition(
                () =>
                {
                    replaceContent(
                        html,
                    );

                    updateActiveNavigation();
                },
            );
        }

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
// INIT
// =========================================

export function initAjaxNavigation()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    // =====================================
    // CLICK
    // =====================================

    document.addEventListener(
        'click',
        (event) =>
        {
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
                shouldIgnoreLink(
                    link,
                )
            ) {
                return;
            }

            event.preventDefault();

            void navigateTo(
                link.href,
            );
        },
    );

    // =====================================
    // POPSTATE
    // =====================================

    window.addEventListener(
        'popstate',
        async () =>
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
        },
    );

    updateActiveNavigation();

    debug(
        'AJAX',
        'ready',
    );
}