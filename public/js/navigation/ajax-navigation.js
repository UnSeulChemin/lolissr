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

let initialized =
    false;

let locked =
    false;

let navigationId =
    0;

let controller =
    null;

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

    const normalizedCurrent =
        currentPath.endsWith('/')
            ? currentPath
            : `${currentPath}/`;

    document
        .querySelectorAll(
            '.nav-link-icon',
        )
        .forEach(
            (
                link,
            ) =>
            {
                if (
                    !(
                        link
                        instanceof HTMLAnchorElement
                    )
                ) {
                    return;
                }

                const path =
                    new URL(
                        normalizeUrl(
                            link.href,
                        ),
                    ).pathname;

                const normalizedPath =
                    path.endsWith('/')
                        ? path
                        : `${path}/`;

                const active =
                    normalizedPath
                    === '/lolissr/'
                        ? (
                            normalizedCurrent
                            === normalizedPath
                        )
                        : (
                            normalizedCurrent
                                === normalizedPath
                            || normalizedCurrent.startsWith(
                                normalizedPath,
                            )
                        );

                link.classList.toggle(
                    'active',
                    active,
                );
            },
        );
}

// =========================================
// NAVIGATE
// =========================================

export async function navigateTo(
    href,
    options = {},
)
{
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
    // LOCK
    // =====================================

    if (
        locked
        && options.force !== true
    ) {
        return;
    }

    locked =
        true;

    const currentNavigationId =
        ++navigationId;

    // =====================================
    // EVENT START
    // =====================================

    document.dispatchEvent(
        new CustomEvent(
            'app:navigation-start',
            {
                detail:
                {
                    href:
                        target,
                },
            },
        ),
    );

    // =====================================
    // ABORT
    // =====================================

    controller?.abort();

    controller =
        new AbortController();

    try {

        let html =
            getPrefetchedPage(
                target,
            );

        // =================================
        // PREFETCH REUSE
        // =================================

        if (!html)
        {
            const inFlight =
                getInFlightPrefetch(
                    target,
                );

            if (inFlight) {

                html =
                    await inFlight;

            } else {

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
        // RACE CONDITION
        // =================================

        if (
            currentNavigationId
            !== navigationId
        ) {
            return;
        }

        // =================================
        // VALIDATION
        // =================================

        if (
            typeof html
            !== 'string'
        ) {

            throw new Error(
                'Invalid HTML',
            );
        }

        // =================================
        // TRANSITION
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

        await runPageTransition(
            async () =>
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

            scrollTop();
        }

        // =================================
        // EVENT LOADED
        // =================================

        document.dispatchEvent(
            new CustomEvent(
                'ajax:page-loaded',
                {
                    detail:
                    {
                        href:
                            target,
                    },
                },
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
            currentNavigationId
            === navigationId
        ) {

            locked =
                false;

            controller =
                null;
        }
    }
}

// =========================================
// CLICK
// =========================================

function handleClick(
    event,
)
{
    if (
        event.defaultPrevented
    ) {
        return;
    }

    // =====================================
    // LEFT CLICK ONLY
    // =====================================

    if (
        event.button !== 0
    ) {
        return;
    }

    // =====================================
    // MODIFIERS
    // =====================================

    if (
        event.ctrlKey
        || event.metaKey
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

    event.preventDefault();

    void navigateTo(
        link.href,
    );
}

// =========================================
// POPSTATE
// =========================================

async function handlePopState()
{
    await navigateTo(
        location.href,
        {
            updateHistory:
                false,

            scrollTop:
                false,

            force:
                true,
        },
    );
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