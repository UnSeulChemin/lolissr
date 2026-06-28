// =========================================
// FIGURINE CACHE
// =========================================

import {
    invalidatePage,
} from '../router/page-invalidation.js';

// =========================================
// INVALIDATE
// =========================================

export function invalidateFigurinePages(
    slug = null,
)
{
    invalidatePage('/lolissr/');

    invalidatePage('/lolissr/figurines');

    invalidatePage('/lolissr/figurines/waifus');

    if (slug)
    {
        invalidatePage(
            `/lolissr/figurines/waifus/${slug}`,
        );
    }
}