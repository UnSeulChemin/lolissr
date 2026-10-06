// =================================================
// CACHE DES FIGURINES
// =================================================

import { appUrl } from '../core/url.js';

import { invalidatePage } from '../router/pages/invalidation.js';

// =================================================
// INVALIDATION
// =================================================

export function invalidateFigurinePages()
{
    invalidatePage(appUrl(), {descendants: false});

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('figurine'));

    invalidatePage(window.location.pathname, {descendants: false});
}