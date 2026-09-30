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
    invalidatePage(appUrl(), {descendants: false});

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('manga'));

    invalidatePage(window.location.pathname, {descendants: false});
}