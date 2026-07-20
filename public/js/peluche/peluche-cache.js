// =========================================
// PELUCHE CACHE
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

export function invalidatePeluchePages()
{
    invalidatePage(appUrl());

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('peluche'));

    invalidatePage(appUrl('peluche/waifus'));

    invalidatePage(window.location.pathname);
}