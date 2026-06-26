// =========================================
// MANGA CACHE
// =========================================

import {
    invalidatePage,
} from '../router/page-invalidation.js';

// =========================================
// INVALIDATE
// =========================================

// =========================================
// INVALIDATE
// =========================================

export function invalidateMangaPages()
{
    invalidatePage(
        '/lolissr/',
    );

    invalidatePage(
        '/lolissr/manga',
    );

    invalidatePage(
        '/lolissr/manga/series',
    );

    invalidatePage(
        '/lolissr/manga/series/notes',
    );

    invalidatePage(
        '/lolissr/manga/series/a-lire',
    );

    invalidatePage(
        '/lolissr/manga/artbooks',
    );
}
