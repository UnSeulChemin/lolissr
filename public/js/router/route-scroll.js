// =========================================
// ROUTE SCROLL
// =========================================

import {
    normalizeRouteUrl,
} from '../core/navigation.js';

// =========================================
// STATE
// =========================================

const scrollPositions = new Map();

// =========================================
// SAVE
// =========================================

export function saveScrollPosition(href)
{
    const url = normalizeRouteUrl(
        href,
    );

    scrollPositions.set(
        url,
        {
            x: window.scrollX,
            y: window.scrollY,
        },
    );
}

// =========================================
// RESTORE
// =========================================

export function restoreScrollPosition(href)
{
    const url = normalizeRouteUrl(
        href,
    );

    const position = scrollPositions.get(
        url,
    );

    if (! position)
    {
        window.scrollTo(
            0,
            0,
        );

        return;
    }

    requestAnimationFrame(
        () =>
        {
            window.scrollTo(
                position.x,
                position.y,
            );
        },
    );
}

// =========================================
// CLEAR
// =========================================

export function clearScrollPosition(href)
{
    scrollPositions.delete(
        normalizeRouteUrl(href),
    );
}