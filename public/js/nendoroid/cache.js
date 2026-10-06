// =================================================
// CACHE DES NENDOROIDS
// =================================================

import { appUrl } from '../core/url.js';

import { invalidatePage } from '../router/pages/invalidation.js';

// =================================================
// INVALIDATION
// =================================================

export function invalidateNendoroidPages()
{
    invalidatePage(appUrl(), {descendants: false});

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('nendoroid'));

    invalidatePage(window.location.pathname, {descendants: false});
}