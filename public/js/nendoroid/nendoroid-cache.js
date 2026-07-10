// =========================================
// NENDOROID CACHE
// =========================================

import {
    invalidatePage,
} from '../router/page-invalidation.js';

// =========================================
// INVALIDATE
// =========================================

export function invalidateNendoroidPages()
{
    invalidatePage('/lolissr/');

    invalidatePage('/lolissr/profil');

    invalidatePage('/lolissr/nendoroid');

    invalidatePage('/lolissr/nendoroid/waifus');

    invalidatePage(window.location.pathname);
}