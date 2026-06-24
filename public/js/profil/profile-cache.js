// =========================================
// PROFILE CACHE
// =========================================

import {
    invalidatePage,
} from '../router/page-invalidation.js';

// =========================================
// INVALIDATE
// =========================================

export function invalidateProfilePages()
{
    invalidatePage(
        '/lolissr/profil',
    );

    invalidatePage(
        '/lolissr/profil/personnalisation',
    );
}