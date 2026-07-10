// =========================================
// FIGURINE CACHE
// =========================================

import {
    invalidatePage,
} from '../router/page-invalidation.js';

// =========================================
// INVALIDATE
// =========================================

export function invalidateFigurinePages()
{
    invalidatePage('/lolissr/');

    invalidatePage('/lolissr/profil');

    invalidatePage('/lolissr/figurine');

    invalidatePage('/lolissr/figurine/waifus');

    invalidatePage(window.location.pathname);
}