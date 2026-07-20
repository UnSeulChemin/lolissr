// =========================================
// PROFILE CACHE
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

export function invalidateProfilePages()
{
    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('profil/personnalisation'));
}