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
    numero = null,
)
{
    invalidatePage('/lolissr/');

    invalidatePage('/lolissr/figurines');

    invalidatePage('/lolissr/figurines/waifus');
}