// =========================================
// FIGURINE CACHE
// =========================================

import {
    appUrl,
} from '../core/url.js';

import {
    invalidatePage,
} from '../router/page-invalidation.js';

// =========================================
// INVALIDATE
// =========================================

export function invalidateFigurinePages()
{
    invalidatePage(appUrl());

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('figurine'));

    invalidatePage(appUrl('figurine/waifus'));

    invalidatePage(window.location.pathname);
}