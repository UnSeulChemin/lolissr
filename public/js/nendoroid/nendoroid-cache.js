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

    invalidatePage('/lolissr/nendoroid');

    invalidatePage('/lolissr/nendoroid/waifus');
}