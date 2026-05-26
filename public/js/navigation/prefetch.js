import { request } from '../core/http.js';
import { normalizeUrl } from '../core/navigation.js';
import { debug, debugError } from '../core/debug.js';

const cache = new Map();
const inFlight = new Set();

// =========================
// CACHE GET
// =========================

export function getPrefetchedPage(url)
{
    return cache.get(normalizeUrl(url)) || null;
}

// =========================
// PREFETCH CORE
// =========================

export async function prefetchPage(url)
{
    const parsed =
        new URL(
            url,
            window.location.origin,
        );

    if (
        parsed.origin
        !== window.location.origin
    ) {
        return;
    }

    if (
        parsed.protocol !== 'http:'
        && parsed.protocol !== 'https:'
    ) {
        return;
    }

    const normalized =
        normalizeUrl(url);

    if (cache.has(normalized)) return;
    if (inFlight.has(normalized)) return;

    inFlight.add(normalized);

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 4000);

    try {

        const html = await request(normalized, {
            responseType: 'text',
            signal: controller.signal,
            headers: {
                'X-Prefetch': '1',
                Accept: 'text/html',
            },
        });

        if (typeof html === 'string' && html.length > 0) {
            cache.set(normalized, html);
            debug('PREFETCH', 'cached', normalized);
        }

    } catch (e) {

        if (e?.name !== 'AbortError') {
            debugError('PREFETCH', e);
        }

    } finally {

        clearTimeout(timeout);
        inFlight.delete(normalized);
    }
}

// =========================
// GLOBAL ACCESS (IMPORTANT)
// =========================

window.__prefetchPage = prefetchPage;

// =========================================
// INIT PREFETCH (OBLIGATOIRE POUR APP.JS)
// =========================================

export function initPrefetch()
{
    // IMPORTANT : gardé pour éviter ton crash app.js
    debug('PREFETCH', 'ready');
}