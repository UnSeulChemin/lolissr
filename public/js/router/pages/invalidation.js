// =================================================
// INVALIDATION DES PAGES
// =================================================

import { invalidateRoute } from './route-invalidation.js';
import { invalidateSearchCache } from '../../search/cache.js';

import { invalidatePrefetch } from '../prefetch/prefetch-cache.js';

// =================================================
// INVALIDATION PAGE
// =================================================

export function invalidatePage(href, options = {})
{
    invalidatePages([[href, options]]);
}

export function invalidatePages(pages)
{
    invalidateSearchCache();
    for (const [href, options = {}] of pages)
    {
        invalidateRoute(href, options);

        invalidatePrefetch(href, options);
    }
}
