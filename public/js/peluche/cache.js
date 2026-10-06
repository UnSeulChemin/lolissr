// =================================================
// CACHE DES PELUCHES
// =================================================

import { appUrl } from '../core/url.js';

import { invalidatePages } from '../router/pages/invalidation.js';

// =================================================
// INVALIDATION
// =================================================

export function invalidatePeluchePages()
{
    invalidatePages([
        [appUrl(), {descendants: false}],
        [appUrl('profil')],
        [appUrl('peluche')],
        [window.location.pathname, {descendants: false}]
    ]);
}