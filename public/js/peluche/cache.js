// =================================================
// CACHE DES PELUCHES
// =================================================

import { appUrl } from '../core/url.js';

import { invalidatePage } from '../router/pages/invalidation.js';

// =================================================
// INVALIDATION
// =================================================

export function invalidatePeluchePages()
{
    invalidatePage(appUrl(), {descendants: false});

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('peluche'));

    invalidatePage(window.location.pathname, {descendants: false});
}