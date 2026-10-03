// =================================================
// RÉSOLUTION PAGE
// =================================================

import { debug } from '../../core/debug/debug.js';

import { end, start } from '../../core/debug/profiler.js';

import { getInFlightPrefetch, getPrefetchedPage } from '../prefetch/prefetch-cache.js';

import { fetchPage } from '../router-fetch.js';

// =================================================
// RÉSOLUTION
// =================================================

export async function resolvePage(target, forceRefresh, signal)
{
    start('resolve');

    // --------------------------------------------------------------------------
    // ACTUALISATION FORCÉE
    // --------------------------------------------------------------------------

    if (forceRefresh)
    {

        debug('ROUTER', 'force-refresh', target);

        start('network');

        const response = await fetchPage(
                target,
                {
                    signal
                }
            );

        end('network');

        end('resolve');

        return response;
    }

    // --------------------------------------------------------------------------
    // PRÉCHARGEMENT CACHE
    // --------------------------------------------------------------------------

    start('cache');

    const cached = getPrefetchedPage(target);

    end('cache');

    if (cached && !cached.page.requiresFreshNavigation)
    {

        debug('ROUTER', 'cache-hit', target);

        end('resolve');

        return cached;
    }

    // --------------------------------------------------------------------------
    // PRÉCHARGEMENT EN COURS
    // --------------------------------------------------------------------------

    start('prefetch');

    const inFlight = getInFlightPrefetch(target);

    end('prefetch');

    if (inFlight)
    {

        debug('ROUTER', 'reuse-prefetch', target);

        const prefetched = await inFlight;
        if (signal?.aborted)
        {
            throw new DOMException('Navigation aborted', 'AbortError');
        }
        if (prefetched?.type === 'page' && typeof prefetched.page?.html === 'string' && !prefetched.page.requiresFreshNavigation)
        {
            end('resolve');
            return prefetched;
        }
    }

    if (signal?.aborted)
    {
        throw new DOMException('Navigation aborted', 'AbortError');
    }

    // --------------------------------------------------------------------------
    // RÉSEAU CHARGEMENT
    // --------------------------------------------------------------------------

    debug('ROUTER', 'network-fetch', target);

    start('network');

    const response = await fetchPage(
            target,
            {
                signal
            }
        );

    end('network');

    end('resolve');

    return response;
}
