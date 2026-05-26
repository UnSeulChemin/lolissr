// =========================================
// PREFETCH SYSTEM (PAGE ONLY CLEAN)
// =========================================

import { request } from '../core/http.js';
import { normalizeUrl } from '../core/navigation.js';
import { debug, debugError } from '../core/debug.js';
import { config } from '../core/config.js';

const {
    cooldown = 800,
    timeout = 4000,
    cacheLimit = 50,
} = config.prefetch;

// =========================
// STATE
// =========================

const cache = new Map();
const pending = new Map();
const last = new Map();

let initialized = false;

// =========================
// CLEAN CACHE
// =========================

function cleanup()
{
    while (cache.size > cacheLimit) {
        const key = cache.keys().next().value;
        cache.delete(key);
    }
}

// =========================
// COOLDOWN
// =========================

function isCooldown(url)
{
    const t = last.get(url);
    return t && (performance.now() - t) < cooldown;
}

// =========================
// PUBLIC CACHE
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
    const normalized = normalizeUrl(url);

    if (!normalized) return;
    if (cache.has(normalized)) return;
    if (pending.has(normalized)) return;
    if (isCooldown(normalized)) return;

    last.set(normalized, performance.now());

    const controller = new AbortController();
    pending.set(normalized, controller);

    const timer = setTimeout(() =>
    {
        controller.abort();
    }, timeout);

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
            cleanup();
            cache.set(normalized, html);
            debug('PREFETCH', 'cached', normalized);
        }

    } catch (e) {

        if (e?.name !== 'AbortError') {
            debugError('PREFETCH', e);
        }

    } finally {

        clearTimeout(timer);
        pending.delete(normalized);
    }
}

// =========================
// INIT (NO EVENTS)
// =========================

export function initPrefetch()
{
    if (initialized) return;
    initialized = true;

    debug('PREFETCH', 'ready (page-only mode)');
}