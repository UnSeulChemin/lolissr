// ==================================================
// Series Keyboard Navigation
// ==================================================

import {
    prefetchPage,
} from './prefetch-series.js';

// ==================================================
// Config
// ==================================================

const SCROLL_THROTTLE =
    80;

// ==================================================
// State
// ==================================================

let initialized =
    false;

let activeIndex =
    -1;

let cachedColumns =
    1;

let lastScrollTime =
    0;

// ==================================================
// Helpers
// ==================================================

function getGrid()
{
    return document.querySelector(
        '.collection-grid',
    );
}

function getCards()
{
    return Array.from(
        document.querySelectorAll(
            '.collection-card-link',
        ),
    );
}

function updateGridColumns()
{
    const grid =
        getGrid();

    if (!grid) {

        cachedColumns =
            1;

        return;
    }

    const columns =
        window
            .getComputedStyle(
                grid,
            )
            .gridTemplateColumns
            .split(' ')
            .filter(Boolean)
            .length;

    cachedColumns =
        Math.max(
            1,
            columns,
        );
}

function isTypingContext(
    target,
)
{
    if (
        !(target instanceof Element)
    ) {
        return false;
    }

    return Boolean(
        target.closest(
            `
            input,
            textarea,
            select,
            [contenteditable="true"]
            `,
        ),
    );
}

function clampIndex(
    index,
    length,
)
{
    if (!length) {
        return -1;
    }

    if (index < 0) {
        return length - 1;
    }

    if (index >= length) {
        return 0;
    }

    return index;
}

function getNextPaginationLink()
{
    const next =
        document.querySelector(
            '.collection-pagination-link.active + .collection-pagination-link',
        );

    if (
        next
        instanceof HTMLAnchorElement
    ) {
        return next;
    }

    return null;
}

// ==================================================
// Active State
// ==================================================

function clearActiveState()
{
    activeIndex =
        -1;

    const cards =
        getCards();

    for (const card of cards) {

        card.classList.remove(
            'is-active',
        );

        if (
            document.activeElement
            === card
        ) {

            card.blur();
        }
    }
}

function updateCardsState(
    cards,
)
{
    for (
        let index = 0;
        index < cards.length;
        index++
    ) {

        cards[
            index
        ].classList.toggle(
            'is-active',
            index === activeIndex,
        );
    }
}

function scrollCardIntoView(
    card,
)
{
    const now =
        performance.now();

    if (
        now - lastScrollTime
        < SCROLL_THROTTLE
    ) {
        return;
    }

    lastScrollTime =
        now;

    card.scrollIntoView({
        behavior:
            'smooth',

        block:
            'center',

        inline:
            'nearest',
    });
}

function prefetchNearbyCards(
    cards,
)
{
    const nextCard =
        cards[
            activeIndex + 1
        ];

    const previousCard =
        cards[
            activeIndex - 1
        ];

    if (
        nextCard
        instanceof HTMLAnchorElement
    ) {

        prefetchPage(
            nextCard.href,
        );
    }

    if (
        previousCard
        instanceof HTMLAnchorElement
    ) {

        prefetchPage(
            previousCard.href,
        );
    }

    const nextPagination =
        getNextPaginationLink();

    if (nextPagination) {

        prefetchPage(
            nextPagination.href,
        );
    }
}

function syncActiveState()
{
    const cards =
        getCards();

    if (!cards.length) {

        clearActiveState();

        return;
    }

    activeIndex =
        clampIndex(
            activeIndex,
            cards.length,
        );

    updateCardsState(
        cards,
    );

    const activeCard =
        cards[
            activeIndex
        ];

    if (!activeCard) {
        return;
    }

    activeCard.focus({
        preventScroll:
            true,
    });

    scrollCardIntoView(
        activeCard,
    );

    prefetchNearbyCards(
        cards,
    );
}

// ==================================================
// Navigation
// ==================================================

function moveHorizontal(
    direction,
)
{
    activeIndex +=
        direction;

    syncActiveState();
}

function moveVertical(
    direction,
)
{
    activeIndex +=
        cachedColumns
        * direction;

    syncActiveState();
}

// ==================================================
// Keyboard
// ==================================================

function handleKeyboard(
    event,
)
{
    if (
        event.defaultPrevented
    ) {
        return;
    }

    if (!getGrid()) {
        return;
    }

    if (
        isTypingContext(
            event.target,
        )
    ) {
        return;
    }

    if (
        event.ctrlKey
        || event.metaKey
        || event.altKey
    ) {
        return;
    }

    const cards =
        getCards();

    if (!cards.length) {
        return;
    }

    switch (event.key) {

        // ==========================================
        // TAB
        // ==========================================

        case 'Tab':

            event.preventDefault();

            if (
                activeIndex === -1
            ) {

                activeIndex =
                    0;

            } else {

                activeIndex +=
                    event.shiftKey
                        ? -1
                        : 1;
            }

            syncActiveState();

            break;

        // ==========================================
        // RIGHT
        // ==========================================

        case 'ArrowRight':

            event.preventDefault();

            moveHorizontal(
                1,
            );

            break;

        // ==========================================
        // LEFT
        // ==========================================

        case 'ArrowLeft':

            event.preventDefault();

            moveHorizontal(
                -1,
            );

            break;

        // ==========================================
        // DOWN
        // ==========================================

        case 'ArrowDown':

            event.preventDefault();

            moveVertical(
                1,
            );

            break;

        // ==========================================
        // UP
        // ==========================================

        case 'ArrowUp':

            event.preventDefault();

            moveVertical(
                -1,
            );

            break;

        // ==========================================
        // HOME
        // ==========================================

        case 'Home':

            event.preventDefault();

            activeIndex =
                0;

            syncActiveState();

            break;

        // ==========================================
        // END
        // ==========================================

        case 'End':

            event.preventDefault();

            activeIndex =
                cards.length - 1;

            syncActiveState();

            break;

        // ==========================================
        // ENTER
        // ==========================================

        case 'Enter':

            if (
                activeIndex === -1
            ) {
                return;
            }

            event.preventDefault();

            cards[
                activeIndex
            ]?.click();

            break;

        // ==========================================
        // ESCAPE
        // ==========================================

        case 'Escape':

            event.preventDefault();

            clearActiveState();

            break;

        default:
            break;
    }
}

// ==================================================
// Resize
// ==================================================

function handleResize()
{
    updateGridColumns();
}

// ==================================================
// Init
// ==================================================

export function initSeriesKeyboardNavigation()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    updateGridColumns();

    window.addEventListener(
        'resize',
        handleResize,
        {
            passive:
                true,
        },
    );

    document.addEventListener(
        'keydown',
        handleKeyboard,
    );

    document.addEventListener(
        'ajax:page-loaded',
        () =>
        {
            clearActiveState();

            queueMicrotask(
                () =>
                {
                    updateGridColumns();
                },
            );
        },
    );

    window.addEventListener(
        'pageshow',
        clearActiveState,
    );
}