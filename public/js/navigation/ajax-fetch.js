// ==================================================
// AJAX Fetch
// ==================================================

import {
    getPrefetchedPage,
} from './prefetch.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

import {
    config,
} from '../core/config.js';

// ==================================================
// Config
// ==================================================

const AJAX_CONTAINER_SELECTOR =
    '.ajax-content';

const FETCH_TIMEOUT =
    config.ajax.timeout;

// ==================================================
// Helpers
// ==================================================

function normalizeUrl(
    href,
)
{
    const url =
        new URL(
            href,
            window.location.origin,
        );

    let pathname =
        url.pathname;

    if (
        !pathname.endsWith('/')
        && !pathname.includes('.')
    ) {

        pathname += '/';
    }

    return (
        pathname
        + url.search
    );
}

function isValidHtmlResponse(
    html,
)
{
    if (
        typeof html
        !== 'string'
    ) {
        return false;
    }

    return html.includes(
        AJAX_CONTAINER_SELECTOR,
    );
}

function createTimeoutSignal(
    signal,
    timeout = FETCH_TIMEOUT,
)
{
    const controller =
        new AbortController();

    const timeoutId =
        window.setTimeout(
            () =>
            {
                controller.abort(
                    'timeout',
                );
            },
            timeout,
        );

    if (signal) {

        signal.addEventListener(
            'abort',
            () =>
            {
                controller.abort();
            },
            {
                once: true,
            },
        );
    }

    return {
        signal:
            controller.signal,

        cleanup:
            () =>
            {
                clearTimeout(
                    timeoutId,
                );
            },
    };
}

// ==================================================
// Fetch Page HTML
// ==================================================

export async function fetchPageHtml(
    href,
    options = {},
)
{
    const normalizedUrl =
        normalizeUrl(
            href,
        );

    // ==============================================
    // Prefetch cache
    // ==============================================

    const cachedPage =
        getPrefetchedPage(
            normalizedUrl,
        );

    if (
        isValidHtmlResponse(
            cachedPage,
        )
    ) {

        debug(
            'FETCH',
            'cache-hit',
            normalizedUrl,
        );

        return cachedPage;
    }

    // ==============================================
    // Timeout signal
    // ==============================================

    const {
        signal,
        cleanup,
    } = createTimeoutSignal(
        options.signal,
    );

    try {

        debug(
            'FETCH',
            'request',
            normalizedUrl,
        );

        const response =
            await fetch(
                normalizedUrl,
                {
                    signal,

                    credentials:
                        'same-origin',

                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest',

                        'X-Partial':
                            'true',

                        'Accept':
                            'text/html',
                    },
                },
            );

        if (!response.ok) {

            throw new Error(
                `[AJAX] HTTP ${response.status}`,
            );
        }

        const html =
            await response.text();

        if (
            !isValidHtmlResponse(
                html,
            )
        ) {

            throw new Error(
                '[AJAX] Invalid HTML response',
            );
        }

        debug(
            'FETCH',
            'success',
            normalizedUrl,
        );

        return html;

    } catch (error) {

        if (
            error instanceof Error
            && error.name
                === 'AbortError'
        ) {

            debug(
                'FETCH',
                'aborted',
                normalizedUrl,
            );

            throw error;
        }

        debugError(
            'FETCH',
            error,
        );

        throw error;

    } finally {

        cleanup();
    }
}