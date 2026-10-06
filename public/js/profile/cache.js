// =================================================
// CACHE DU PROFIL
// =================================================

import { appUrl } from '../core/url.js';

import { invalidatePage } from '../router/pages/invalidation.js';

// =================================================
// INVALIDATION
// =================================================

export function invalidateProfilePages()
{
    invalidatePage(appUrl('profil'));

}