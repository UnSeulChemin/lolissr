// =========================================
// MANGA CACHE
// =========================================

import {
    invalidatePage,
} from '../router/page-invalidation.js';

// =========================================
// INVALIDATE
// =========================================

export function invalidateMangaPages()
{
    invalidatePage(
        '/lolissr/manga/series',
    );
}
