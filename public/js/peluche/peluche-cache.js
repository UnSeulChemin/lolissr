// =========================================
// PELUCHE CACHE
// =========================================

import {
    invalidatePage,
} from '../router/page-invalidation.js';

// =========================================
// INVALIDATE
// =========================================

export function invalidatePeluchePages()
{
    invalidatePage('/lolissr/');

    invalidatePage('/lolissr/profil');

    invalidatePage('/lolissr/peluche');

    invalidatePage('/lolissr/peluche/waifus');

    invalidatePage(window.location.pathname);
}