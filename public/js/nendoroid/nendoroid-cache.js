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
    invalidatePage(appUrl(), {descendants: false});

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('nendoroid'));

    invalidatePage(window.location.pathname, {descendants: false});
}