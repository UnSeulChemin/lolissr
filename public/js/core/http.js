// =========================================
// CORE : HTTP
// =========================================

/**
 * CSRF TOKEN
 */
function getCsrfToken()
{
    return window.csrfToken || '';
}

/**
 * BUILD HEADERS
 */
function buildHeaders(custom = {})
{
    return {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCsrfToken(),
        ...custom,
    };
}

/**
 * SAFE RESPONSE PARSER
 */
async function parseResponse(res, type = 'text')
{
    try {

        // TEXT MODE
        if (type === 'text') {
            return await res.text();
        }

        // JSON MODE SAFE
        const text = await res.text();

        try {
            return JSON.parse(text);
        } catch {
            return null;
        }

    } catch {
        return null;
    }
}

/**
 * MAIN REQUEST
 */
export async function request(url, options = {})
{
    const res = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: buildHeaders(options.headers),
    });

    const data = await parseResponse(
        res,
        options.responseType || 'text'
    );

    if (!res.ok) {
        throw {
            status: res.status,
            data,
        };
    }

    return data;
}

/**
 * GET
 */
export function get(url, options = {})
{
    return request(url, {
        method: 'GET',
        ...options,
    });
}

/**
 * POST (IMPORTANT POUR TON PROJET)
 */
export function post(url, body = {}, options = {})
{
    return request(url, {
        method: 'POST',
        body: JSON.stringify(body),
        headers: {
            'Content-Type': 'application/json',
            ...(options.headers || {}),
        },
        ...options,
    });
}