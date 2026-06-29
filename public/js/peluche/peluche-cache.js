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

    invalidatePage('/lolissr/peluches');

    invalidatePage('/lolissr/peluches/waifus');
}