// =========================================
// ROUTE SCROLL
// =========================================

import {
    normalizeUrl,
} from '../core/navigation.js';

// =========================================
// STATE
// =========================================

const scrollPositions =
    new Map();

// =========================================
// SAVE
// =========================================

export function saveScrollPosition(
    href,
)
{
    const url =
        normalizeUrl(
            href,
        );

    scrollPositions.set(
        url,
        {
            x:
                window.scrollX,

            y:
                window.scrollY,
        },
    );
}

// =========================================
// RESTORE
// =========================================

export function restoreScrollPosition(
    href,
)
{
    const url =
        normalizeUrl(
            href,
        );

    const position =
        scrollPositions.get(
            url,
        );

    if (!position) {

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

export function clearScrollPosition(
    href,
)
{
    scrollPositions.delete(
        normalizeUrl(
            href,
        ),
    );
}