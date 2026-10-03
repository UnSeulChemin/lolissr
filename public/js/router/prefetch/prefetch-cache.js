// =================================================
// PRÉCHARGEMENT CACHE
// =================================================

import { config } from '../../core/config.js';

import { debug } from '../../core/debug/debug.js';

import { normalizeCacheKey } from '../../core/navigation.js';

import { cache, inFlight, invalidated } from './prefetch-state.js';

// =================================================
// UTILITAIRES
// =================================================

// Conserver l’âge de l’instantané si la même réponse est affichée ou remise en cache.
// Les clés faibles libèrent les pages évincées ; une nouvelle réponse reçoit un nouvel âge.
const snapshotTimes = new WeakMap();

function isExpired(entry)
{
    return Date.now() - entry.timestamp >= config.prefetch.cacheDuration;
}

function trimCache()
{
    while (cache.size > config.prefetch.cacheLimit)
    {
        const oldestKey = cache.keys().next().value;

        if (! oldestKey)
        {
            return;
        }

        cache.delete(oldestKey);
    }
}

// =================================================
// CACHE
// =================================================

export function getPrefetchedPage(href)
{
    const url = normalizeCacheKey(href);

    if (invalidated.has(url))
    {
        return null;
    }

    const cached = cache.get(url);

    if (! cached)
    {
        return null;
    }

    if (isExpired(cached))
    {
        cache.delete(url);

        return null;
    }

    // --------------------------------------------------------------------------
    // ACTUALISATION DU CACHE LRU
    // --------------------------------------------------------------------------

    cache.delete(url);

    cache.set(url, cached);

    return {
        type: 'page',
        page: cached.page
    };
}

export function setPrefetchedPage(href, response)
{
    const url = normalizeCacheKey(href);

    const timestamp = snapshotTimes.get(response.page) ?? Date.now();
    snapshotTimes.set(response.page, timestamp);
    if (isExpired({timestamp})) return;

    cache.delete(url);

    cache.set(
        url,
        {
            page: response.page,
            timestamp
        }
    );

    invalidated.delete(url);

    trimCache();
}

// =================================================
// INVALIDATION
// =================================================

export function invalidatePrefetch(href, {descendants = true} = {})
{
    const url = normalizeCacheKey(href);

    const target = new URL(url);
    const targetPath = target.pathname.replace(/\/+$/, '') || '/';
    const keys = new Set([url, ...cache.keys(), ...inFlight.keys()]);

    for (const key of keys)
    {
        const candidate = new URL(key);
        const candidatePath = candidate.pathname.replace(/\/+$/, '') || '/';

        if (
            candidate.origin !== target.origin
            || (
                candidatePath !== targetPath
                && (! descendants || ! candidatePath.startsWith(targetPath === '/' ? '/' : `${targetPath}/`))
            )
        )
        {
            continue;
        }

        invalidated.delete(key);
        invalidated.add(key);
        cache.delete(key);

        const entry = inFlight.get(key);

        entry?.controller.abort();
        inFlight.delete(key);
    }

    // Les requêtes annulées vérifient leur signal avant toute écriture, même après éviction.
    const invalidationLimit = Math.max(1, config.prefetch.cacheLimit * 4);
    while (invalidated.size > invalidationLimit)
    {
        invalidated.delete(invalidated.values().next().value);
    }

    debug('PREFETCH', 'invalidate', url);
}

// =================================================
// EN COURS
// =================================================

export function getInFlightPrefetch(href)
{
    const url = normalizeCacheKey(href);

    if (invalidated.has(url))
    {
        return null;
    }

    return inFlight.get(url)?.promise ?? null;
}
