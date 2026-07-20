// =========================================
// NENDOROID CACHE
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

export function invalidateNendoroidPages()
{
    invalidatePage(appUrl());

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('nendoroid'));

    invalidatePage(appUrl('nendoroid/waifus'));

    invalidatePage(window.location.pathname);
}