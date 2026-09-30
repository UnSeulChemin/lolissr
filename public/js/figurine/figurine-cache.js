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
    invalidatePage(appUrl(), {descendants: false});

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('figurine'));

    invalidatePage(window.location.pathname, {descendants: false});
}