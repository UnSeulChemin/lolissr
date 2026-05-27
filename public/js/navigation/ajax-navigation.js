// =========================================
// AJAX NAVIGATION (SPA CORE CLEAN)
// =========================================

import {
    getPrefetchedPage,
} from './prefetch.js';

import {
    normalizeUrl,
} from '../core/navigation.js';

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

// =========================================
// ACTIVE NAVIGATION
// =========================================

function updateActiveNavigation()
{
    const current =
        new URL(
            location.href,
        ).pathname.replace(
            /\/+$/,
            '/',
        );

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
                link.href,
            ).pathname.replace(
                /\/+$/,
                '/',
            );

        link.classList.remove(
            'active',
        );

        if (href === '/') {

            if (current === '/') {

                link.classList.add(
                    'active',
                );
            }

            continue;
        }

        if (
            current === href
            || current.startsWith(
                href + '/',
            )
        ) {

            link.classList.add(
                'active',
            );
        }
    }
}

// =========================================
// NAVIGATION CORE
// =========================================

export async function navigateTo(
    href,
    options = {},
)
{
    // =====================================
    // LOCK
    // =====================================

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

        // =================================
        // SPA NAVIGATION START
        // =================================

        document.dispatchEvent(
            new CustomEvent(
                'app:navigation-start',
            ),
        );

        // =================================
        // PREFETCH CACHE
        // =================================

        let html =
            getPrefetchedPage(
                target,
            );

        const instant =
            Boolean(
                html,
            );

        // =================================
        // FETCH
        // =================================

        if (!html) {

            html =
                await fetchPageHtml(
                    target,
                    {
                        signal:
                            controller.signal,
                    },
                );
        }

        // =================================
        // VALIDATE
        // =================================

        if (
            typeof html !== 'string'
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
                    if (
                        currentId
                        !== navigationId
                    ) {
                        return;
                    }

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
        // EVENTS
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
    if (window.__SPA__) {
        return;
    }

    window.__SPA__ =
        true;

    // =====================================
    // CLICK NAVIGATION
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
                !(
                    link
                    instanceof HTMLAnchorElement
                )
            ) {
                return;
            }

            // =================================
            // NO AJAX
            // =================================

            if (
                link.dataset.noAjax
                !== undefined
            ) {
                return;
            }

            // =================================
            // EXTERNAL
            // =================================

            const url =
                new URL(
                    link.href,
                    window.location.origin,
                );

            if (
                url.origin
                !== window.location.origin
            ) {
                return;
            }

            // =================================
            // NEW TAB
            // =================================

            if (
                link.target === '_blank'
            ) {
                return;
            }

            // =================================
            // DOWNLOAD
            // =================================

            if (
                link.hasAttribute(
                    'download',
                )
            ) {
                return;
            }

            // =================================
            // HASH ONLY
            // =================================

            if (
                url.pathname
                === location.pathname
                && url.hash
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
    // BROWSER HISTORY
    // =====================================

    window.addEventListener(
        'popstate',
        async () =>
        {
            controller?.abort();

            controller =
                null;

            locked =
                false;

            delete document.body.dataset.ajaxNavigating;

            try {

                document.dispatchEvent(
                    new CustomEvent(
                        'app:navigation-start',
                    ),
                );

                const html =
                    await fetchPageHtml(
                        location.href,
                    );

                if (
                    typeof html !== 'string'
                    || html.length === 0
                ) {
                    return;
                }

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

    // =====================================
    // INIT
    // =====================================

    updateActiveNavigation();

    debug(
        'AJAX',
        'SPA ready',
    );
}