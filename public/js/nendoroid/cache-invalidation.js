// =================================================
// CACHE DES NENDOROIDS
// =================================================

import { appUrl } from '../core/url.js';

import { invalidatePages } from '../router/pages/invalidation.js';

// =================================================
// INVALIDATION
// =================================================

export function invalidateNendoroidPages()
{
    invalidatePages([
        [appUrl(), {descendants: false}],
        [appUrl('profil')],
        [appUrl('nendoroid')],
        [window.location.pathname, {descendants: false}]
    ]);
}