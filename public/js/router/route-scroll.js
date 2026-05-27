// =========================================
// ROUTE SCROLL
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
    scrollPositions.set(
        href,
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
    const position =
        scrollPositions.get(
            href,
        );

    if (!position) {

        window.scrollTo(
            0,
            0,
        );

        return;
    }

    window.scrollTo(
        position.x,
        position.y,
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
        href,
    );
}