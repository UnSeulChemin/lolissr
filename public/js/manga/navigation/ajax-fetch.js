// ==================================================
// AJAX Fetch
// ==================================================

import {
    getPrefetchedPage,
} from './prefetch-series.js';

// ==================================================
// Config
// ==================================================

const AJAX_CONTAINER_CLASS =
    'ajax-content';

const FETCH_TIMEOUT =
    10000;

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
    return (
        typeof html
            === 'string'
        && html.includes(
            AJAX_CONTAINER_CLASS,
        )
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

    const cached =
        getPrefetchedPage(
            normalizedUrl,
        );

    if (
        isValidHtmlResponse(
            cached,
        )
    ) {

        return cached;
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
                `[AJAX] ${response.status}`,
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

        return html;

    } finally {

        cleanup();
    }
}