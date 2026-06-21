// =========================================
// PAGE CACHE
// =========================================

import {
    setPrefetchedPage,
} from './prefetch/prefetch-cache.js';

// =========================================
// CACHE PAGE
// =========================================

export function cachePage(
    href,
    response,
)
{
    if (
        response?.type
        !== 'page'
    ) {

        return;
    }

    setPrefetchedPage(
        href,
        response,
    );
}