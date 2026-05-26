// =========================================
// CORE : HTTP
// =========================================

/**
 * CSRF Token
 */
function getCsrfToken()
{
    return window.csrfToken || '';
}

/**
 * Build Headers
 */
function buildHeaders(
    customHeaders = {},
)
{
    return {
        'X-Requested-With':
            'XMLHttpRequest',

        'X-CSRF-TOKEN':
            getCsrfToken(),

        ...customHeaders,
    };
}

/**
 * Parse Response
 */
async function parseResponse(
    response,
    responseType = 'json',
)
{
    try {

        if (
            responseType
            === 'text'
        ) {

            return await response.text();
        }

        return await response.json();

    } catch {

        return null;
    }
}

/**
 * Main Request
 */
export async function request(
    url,
    options = {},
)
{
    const response =
        await fetch(url, {
            credentials:
                'same-origin',

            ...options,

            headers:
                buildHeaders(
                    options.headers,
                ),
        });

    const data =
        await parseResponse(
            response,
            options.responseType,
        );

    if (!response.ok)
    {
        throw {
            status:
                response.status,

            data,
        };
    }

    return data;
}

/**
 * GET
 */
export function get(
    url,
    options = {},
)
{
    return request(url, {
        method: 'GET',
        ...options,
    });
}

/**
 * POST
 */
export function post(
    url,
    body = {},
    options = {},
)
{
    return request(url, {
        method: 'POST',

        body:
            new URLSearchParams(body),

        headers: {
            'Content-Type':
                'application/x-www-form-urlencoded',
        },

        ...options,
    });
}