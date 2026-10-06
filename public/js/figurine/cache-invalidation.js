// =================================================
// CACHE DES FIGURINES
// =================================================

import { appUrl } from '../core/url.js';

import { invalidatePages } from '../router/pages/invalidation.js';

// =================================================
// INVALIDATION
// =================================================

export function invalidateFigurinePages()
{
    invalidatePages([
        [appUrl(), {descendants: false}],
        [appUrl('profil')],
        [appUrl('figurine')],
        [window.location.pathname, {descendants: false}]
    ]);
}