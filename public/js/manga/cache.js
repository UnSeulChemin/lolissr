// =================================================
// CACHE DES MANGAS
// =================================================

import { appUrl } from '../core/url.js';

import { invalidatePage } from '../router/pages/invalidation.js';

// =================================================
// INVALIDATION
// =================================================

export function invalidateMangaPages()
{
    invalidatePage(appUrl(), {descendants: false});

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('manga'));

    invalidatePage(window.location.pathname, {descendants: false});
}