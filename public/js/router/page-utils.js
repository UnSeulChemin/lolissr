// =========================================
// PAGE UTILS
// =========================================

import {
    invalidatePage,
} from './page-invalidation.js';

// =========================================
// INVALIDATE PAGES
// =========================================

export function invalidatePages(
    ...urls
)
{
    for (
        const url
        of urls
    )
    {
        invalidatePage(
            url,
        );
    }
}

// =========================================
// REFRESH CURRENT PAGE
// =========================================

export function refreshCurrentPage()
{
    invalidatePage(
        window.location.href,
    );
}