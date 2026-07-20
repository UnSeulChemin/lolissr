// =========================================
// MANGA CACHE
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

export function invalidateMangaPages()
{
    invalidatePage(appUrl());

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('manga'));

    invalidatePage(appUrl('manga/series'));

    invalidatePage(appUrl('manga/series/notes'));

    invalidatePage(appUrl('manga/series/a-lire'));

    invalidatePage(appUrl('manga/artbooks'));

    invalidatePage(window.location.pathname);
}