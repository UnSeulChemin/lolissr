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

    invalidatePage('/lolissr/peluche');

    invalidatePage('/lolissr/peluche/waifus');
}