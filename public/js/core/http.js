// =========================================
// CORE : HTTP
// =========================================

import {
    debugError,
} from './debug/debug.js';

import {
    FrontendError,
} from './errors/FrontendError.js';

// =========================================
// CONFIG
// =========================================

const DEFAULT_TIMEOUT =
    15000;

// =========================================
// CSRF
// =========================================

function getCsrfToken()
{
    return (
        window.csrfToken
        || ''
    );
}

// =========================================
// HEADERS
// =========================================

function buildHeaders(
    custom = {},
)
{
    return {
        /*
        |--------------------------------------------------------------------------
        | AJAX
        |--------------------------------------------------------------------------
        */

        'X-Ajax':
            'true',

        'X-Requested-With':
            'XMLHttpRequest',

        /*
        |--------------------------------------------------------------------------
        | CSRF
        |--------------------------------------------------------------------------
        */

        'X-CSRF-TOKEN':
            getCsrfToken(),

        /*
        |--------------------------------------------------------------------------
        | ACCEPT
        |--------------------------------------------------------------------------
        */

        'Accept':
            'application/json',

        /*
        |--------------------------------------------------------------------------
        | CUSTOM
        |--------------------------------------------------------------------------
        */

        ...custom,
    };
}

// =========================================
// TIMEOUT
// =========================================

function createTimeoutController(
    timeout,
)
{
    const controller =
        new AbortController();

    const timer =
        window.setTimeout(
            () =>
            {
                controller.abort(
                    'timeout',
                );
            },
            timeout,
        );

    return {
        signal:
            controller.signal,

        clear:
            () =>
            {
                clearTimeout(
                    timer,
                );
            },
    };
}

// =========================================
// SIGNAL
// =========================================

function buildSignal(
    signal,
    timeoutSignal,
)
{
    if (
        signal
        && typeof AbortSignal.any
            === 'function'
    ) {

        return AbortSignal.any([
            signal,
            timeoutSignal,
        ]);
    }

    return timeoutSignal;
}

// =========================================
// RESPONSE TYPE
// =========================================

function isJsonResponse(
    response,
)
{
    const contentType =
        response.headers.get(
            'content-type',
        ) || '';

    return contentType.includes(
        'application/json',
    );
}

// =========================================
// PARSE RESPONSE
// =========================================

async function parseResponse(
    response,
)
{
    /*
    |--------------------------------------------------------------------------
    | EMPTY RESPONSE
    |--------------------------------------------------------------------------
    */

    if (
        response.status === 204
    ) {

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | JSON
    |--------------------------------------------------------------------------
    */

    if (
        isJsonResponse(
            response,
        )
    ) {

        try {

            return await response.json();

        } catch {

            throw new FrontendError(
                'Réponse JSON invalide',
                {
                    code:
                        'INVALID_JSON',

                    status:
                        response.status,
                },
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TEXT
    |--------------------------------------------------------------------------
    */

    return await response.text();
}

// =========================================
// HTTP ERROR
// =========================================

function createHttpError(
    response,
    data,
)
{
    return new FrontendError(
        data?.message
        || `HTTP ${response.status}`,
        {
            code:
                `HTTP_${response.status}`,

            status:
                response.status,

            details:
                data,
        },
    );
}

// =========================================
// JSON BODY
// =========================================

function createJsonRequest(
    method,
    url,
    body = {},
    options = {},
)
{
    return request(
        url,
        {
            method,

            body:
                JSON.stringify(
                    body,
                ),

            headers:
            {
                'Content-Type':
                    'application/json',

                ...(options.headers || {}),
            },

            ...options,
        },
    );
}

// =========================================
// REQUEST
// =========================================

export async function request(
    url,
    options = {},
)
{
    const timeout =
        options.timeout
        || DEFAULT_TIMEOUT;

    const timeoutController =
        createTimeoutController(
            timeout,
        );

    try {

        const response =
            await fetch(
                url,
                {
                    credentials:
                        'same-origin',

                    ...options,

                    signal:
                        buildSignal(
                            options.signal,
                            timeoutController.signal,
                        ),

                    headers:
                        buildHeaders(
                            options.headers,
                        ),
                },
            );

        const data =
            await parseResponse(
                response,
            );

        /*
        |--------------------------------------------------------------------------
        | HTTP ERROR
        |--------------------------------------------------------------------------
        */

        if (
            ! response.ok
        ) {

            throw createHttpError(
                response,
                data,
            );
        }

        /*
        |--------------------------------------------------------------------------
        | REDIRECT RESPONSE
        |--------------------------------------------------------------------------
        */

        if (
            data?.type
            === 'redirect'
        ) {

            return {
                ...data,

                redirected:
                    true,
            };
        }

        return data;

    } catch (error) {

        /*
        |------------------------------------------------------------------
        | ABORT (search cancel)
        |------------------------------------------------------------------
        */

        if (
            error?.name === 'AbortError'
            || options.signal?.aborted
        ) {

            throw error;
        }

        /*
        |------------------------------------------------------------------
        | TIMEOUT
        |------------------------------------------------------------------
        */

        if (
            timeoutController.signal.aborted
        ) {

            throw new FrontendError(
                `Request timeout (${timeout}ms)`,
                {
                    code:
                        'REQUEST_TIMEOUT',

                    status:
                        408,
                },
            );
        }

        debugError(
            'HTTP',
            error,
        );

        /*
        |--------------------------------------------------------------------------
        | NETWORK ERROR
        |--------------------------------------------------------------------------
        */

        if (
            error instanceof TypeError
        ) {

            throw new FrontendError(
                'Erreur réseau',
                {
                    code:
                        'NETWORK_ERROR',

                    status:
                        0,
                },
            );
        }

        /*
        |--------------------------------------------------------------------------
        | UNKNOWN ERROR
        |--------------------------------------------------------------------------
        */

        if (
            ! (
                error
                instanceof FrontendError
            )
        ) {

            throw new FrontendError(
                error?.message
                || 'Erreur inconnue',
                {
                    code:
                        'UNKNOWN_ERROR',
                },
            );
        }

        throw error;

    } finally {

        timeoutController.clear();
    }
}

// =========================================
// GET
// =========================================

export function get(
    url,
    options = {},
)
{
    return request(
        url,
        {
            method:
                'GET',

            ...options,
        },
    );
}

// =========================================
// POST
// =========================================

export function post(
    url,
    body = {},
    options = {},
)
{
    return createJsonRequest(
        'POST',
        url,
        body,
        options,
    );
}

// =========================================
// PUT
// =========================================

export function put(
    url,
    body = {},
    options = {},
)
{
    return createJsonRequest(
        'PUT',
        url,
        body,
        options,
    );
}

// =========================================
// PATCH
// =========================================

export function patch(
    url,
    body = {},
    options = {},
)
{
    return createJsonRequest(
        'PATCH',
        url,
        body,
        options,
    );
}

// =========================================
// DELETE
// =========================================

export function destroy(
    url,
    options = {},
)
{
    return request(
        url,
        {
            method:
                'DELETE',

            ...options,
        },
    );
}