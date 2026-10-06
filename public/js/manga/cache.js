// =================================================
// CACHE DES MANGAS
// =================================================

import { appUrl } from '../core/url.js';

import { invalidatePages } from '../router/pages/invalidation.js';

// =================================================
// INVALIDATION
// =================================================

export function invalidateMangaPages()
{
    invalidatePages([
        [appUrl(), {descendants: false}],
        [appUrl('profil')],
        [appUrl('manga')],
        [window.location.pathname, {descendants: false}]
    ]);
}