// =================================================
// CACHE DES PAGES
// =================================================

import {
    setPrefetchedPage,
} from './prefetch/prefetch-cache.js';

// =================================================
// MISE EN CACHE DE LA PAGE
// =================================================

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