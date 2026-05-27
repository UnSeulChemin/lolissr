// =========================================
// AJAX NAVIGATION
// =========================================

import {
    normalizeUrl,
    shouldIgnoreLink,
} from '../core/navigation.js';

import {
    getPrefetchedPage,
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

        // =================================
        // EXACT ROOT
        // =================================

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
// NAVIGATION
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

    if (
        target === current
        && options.force !== true
    ) {
        return;
    }

    controller?.abort();

    controller =
        new AbortController();

    locked =
        true;

    document.body.dataset.ajaxNavigating =
        '1';

    try {

        document.dispatchEvent(
            new CustomEvent(
                'app:navigation-start',
            ),
        );

        let html =
            getPrefetchedPage(
                target,
            );

        const instant =
            Boolean(
                html,
            );

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

        if (
            currentId
            !== navigationId
        ) {
            return;
        }

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

        if (
            options.scrollTop
            !== false
        ) {

            scrollTop(
                false,
            );
        }

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