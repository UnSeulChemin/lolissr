// =================================================
// SOCLE : HTTP
// =================================================

import { debugError } from './debug/debug.js';

import { FrontendError } from './errors/FrontendError.js';

// =================================================
// CONFIGURATION
// =================================================

const DEFAULT_TIMEOUT = 15000;

// =================================================
// CSRF
// =================================================

function getCsrfToken()
{
    return (window.csrfToken || '');
}

// =================================================
// EN-TÊTES
// =================================================

function buildHeaders(custom = {})
{
    return {
        // --------------------------------------------------------------------------
        // AJAX
        // --------------------------------------------------------------------------

        'X-Ajax': 'true',

        'X-Requested-With': 'XMLHttpRequest',

        // --------------------------------------------------------------------------
        // CSRF
        // --------------------------------------------------------------------------

        'X-CSRF-TOKEN': getCsrfToken(),

        // --------------------------------------------------------------------------
        // ACCEPTATION
        // --------------------------------------------------------------------------

        'Accept': 'application/json',

        // --------------------------------------------------------------------------
        // PERSONNALISATION
        // --------------------------------------------------------------------------

        ...custom
    };
}

// =================================================
// DÉLAI
// =================================================

function createTimeoutController(timeout)
{
    const controller = new AbortController();

    const timer = window.setTimeout(
            () =>
            {
                controller.abort('timeout');
            },
            timeout
        );

    return {
        signal: controller.signal,

        clear: () =>
            {
                clearTimeout(timer);
            }
    };
}

// =================================================
// SIGNAL
// =================================================

function buildSignal(signal, timeoutSignal)
{
    const noop = () =>
    {};
    if (!signal) return { signal: timeoutSignal, clear: noop };
    if (signal && typeof AbortSignal.any === 'function')
    {

        return { signal: AbortSignal.any([ signal, timeoutSignal ]), clear: noop };
    }

    const controller = new AbortController();
    const sources = [signal, timeoutSignal];
    const abort = event => controller.abort(event.target.reason);
    for (const source of sources)
    {
        if (source.aborted)
        {
            controller.abort(source.reason);
            break;
        }
        source.addEventListener('abort', abort, { once: true });
    }
    return {
        signal: controller.signal,
        clear: () => sources.forEach(source => source.removeEventListener('abort', abort))
    };
}

// =================================================
// TYPE DE RÉPONSE
// =================================================

function isJsonResponse(response)
{
    const contentType = response.headers.get('content-type') || '';

    return contentType.includes('application/json');
}

// =================================================
// ANALYSE RÉPONSE
// =================================================

async function parseResponse(response)
{
    // --------------------------------------------------------------------------
    // RÉPONSE VIDE
    // --------------------------------------------------------------------------

    if (response.status === 204)
    {

        return null;
    }

    // --------------------------------------------------------------------------
    // JSON
    // --------------------------------------------------------------------------

    if (isJsonResponse( response ))
    {

        try
        {

            return await response.json();

        } catch {

            throw new FrontendError(
                'Réponse JSON invalide',
                {
                    code: 'INVALID_JSON',

                    status: response.status
                }
            );
        }
    }

    // --------------------------------------------------------------------------
    // TEXTE
    // --------------------------------------------------------------------------

    return await response.text();
}

// =================================================
// ERREUR HTTP
// =================================================

function createHttpError(response, data)
{
    return new FrontendError(
        data?.message
        || `HTTP ${response.status}`,
        {
            code: `HTTP_${response.status}`,

            status: response.status,

            details: data
        }
    );
}

// =================================================
// CORPS JSON
// =================================================

function createJsonRequest(method, url, body = {}, options = {})
{
    return request(
        url,
        {
            method,

            body: JSON.stringify(body),

            ...options,

            headers: {
                'Content-Type': 'application/json',

                ...(options.headers || {})
            }

        }
    );
}

// =================================================
// REQUÊTE
// =================================================

export async function request(url, options = {})
{
    const timeout = options.timeout
        || DEFAULT_TIMEOUT;

    const timeoutController = createTimeoutController(timeout);

    const combinedSignal = buildSignal(options.signal, timeoutController.signal);

    try
    {

        const response = await fetch(
                url,
                {
                    credentials: 'same-origin',

                    ...options,

                    signal: combinedSignal.signal,

                    headers: buildHeaders(options.headers)
                }
            );

        const data = await parseResponse(response);

        // --------------------------------------------------------------------------
        // ERREUR HTTP
        // --------------------------------------------------------------------------

        if (! response.ok)
        {

            throw createHttpError(response, data);
        }

        // --------------------------------------------------------------------------
        // REDIRECTION RÉPONSE
        // --------------------------------------------------------------------------

        if (data?.type === 'redirect')
        {

            return {
                ...data,

                redirected: true
            };
        }

        return data;

    } catch (error)
    {

        // ------------------------------------------------------------------
        // ANNULATION DE LA RECHERCHE
        // ------------------------------------------------------------------

        if (error?.name === 'AbortError' || options.signal?.aborted)
        {

            throw error;
        }

        // ------------------------------------------------------------------
        // DÉLAI
        // ------------------------------------------------------------------

        if (timeoutController.signal.aborted)
        {

            throw new FrontendError(
                `Request timeout (${timeout}ms)`,
                {
                    code: 'REQUEST_TIMEOUT',

                    status: 408
                }
            );
        }

        debugError('HTTP', error);

        // --------------------------------------------------------------------------
        // ERREUR RÉSEAU
        // --------------------------------------------------------------------------

        if (error instanceof TypeError)
        {

            throw new FrontendError(
                'Erreur réseau',
                {
                    code: 'NETWORK_ERROR',

                    status: 0
                }
            );
        }

        // --------------------------------------------------------------------------
        // ERREUR INCONNUE
        // --------------------------------------------------------------------------

        if (! ( error instanceof FrontendError ))
        {

            throw new FrontendError(
                error?.message
                || 'Erreur inconnue',
                {
                    code: 'UNKNOWN_ERROR'
                }
            );
        }

        throw error;

    } finally
    {

        combinedSignal.clear();
        timeoutController.clear();
    }
}

// =================================================
// LECTURE
// =================================================

export function get(url, options = {})
{
    return request(
        url,
        {
            method: 'GET',

            ...options
        }
    );
}

// =================================================
// ENVOI
// =================================================

export function post(url, body = {}, options = {})
{
    return createJsonRequest('POST', url, body, options);
}

// =================================================
// MISE À JOUR
// =================================================

export function put(url, body = {}, options = {})
{
    return createJsonRequest('PUT', url, body, options);
}
