// =========================================
// SERIES KEYBOARD NAVIGATION
// =========================================

import {
    $,
    $$,
} from '../../core/dom.js';

import {
    prefetchPage,
} from '../../navigation/prefetch.js';

import {
    debug,
} from '../../core/debug.js';

// =========================================
// Config
// =========================================

const GRID_SELECTOR =
    '.collection-grid';

const CARD_SELECTOR =
    '.collection-card-link';

const ACTIVE_CLASS =
    'is-active';

const SCROLL_THROTTLE =
    80;

const TYPING_SELECTOR =
`
input,
textarea,
select,
[contenteditable="true"]
`;

// =========================================
// State
// =========================================

let initialized =
    false;

let activeIndex =
    -1;

let cachedColumns =
    1;

let lastScrollTime =
    0;

// =========================================
// Helpers
// =========================================

function getGrid()
{
    return $(
        GRID_SELECTOR,
    );
}

function getCards()
{
    return $$(
        CARD_SELECTOR,
    );
}

function isTypingContext(
    target,
)
{
    return (
        target instanceof Element
        && Boolean(
            target.closest(
                TYPING_SELECTOR,
            ),
        )
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

function getNextPaginationLink()
{
    const link =
        $(
            '.collection-pagination-link.active + .collection-pagination-link',
        );

    return (
        link
        instanceof HTMLAnchorElement
    )
        ? link
        : null;
}

// =========================================
// Active State
// =========================================

function clearActiveState()
{
    activeIndex =
        -1;

    getCards().forEach(
        (
            card,
        ) =>
        {
            card.classList.remove(
                ACTIVE_CLASS,
            );

            if (
                document.activeElement
                === card
            ) {

                card.blur();
            }
        },
    );
}

function updateCardsState(
    cards,
)
{
    cards.forEach(
        (
            card,
            index,
        ) =>
        {
            card.classList.toggle(
                ACTIVE_CLASS,
                index === activeIndex,
            );
        },
    );
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
    const nearbyCards =
        [
            cards[
                activeIndex - 1
            ],

            cards[
                activeIndex + 1
            ],
        ];

    nearbyCards.forEach(
        (
            card,
        ) =>
        {
            if (
                card
                instanceof HTMLAnchorElement
            ) {

                prefetchPage(
                    card.href,
                );
            }
        },
    );

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

// =========================================
// Navigation
// =========================================

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

// =========================================
// Keyboard
// =========================================

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

        case 'ArrowRight':

            event.preventDefault();

            moveHorizontal(
                1,
            );

            break;

        case 'ArrowLeft':

            event.preventDefault();

            moveHorizontal(
                -1,
            );

            break;

        case 'ArrowDown':

            event.preventDefault();

            moveVertical(
                1,
            );

            break;

        case 'ArrowUp':

            event.preventDefault();

            moveVertical(
                -1,
            );

            break;

        case 'Home':

            event.preventDefault();

            activeIndex =
                0;

            syncActiveState();

            break;

        case 'End':

            event.preventDefault();

            activeIndex =
                cards.length - 1;

            syncActiveState();

            break;

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

        case 'Escape':

            event.preventDefault();

            clearActiveState();

            break;

        default:
            break;
    }
}

// =========================================
// Resize
// =========================================

function handleResize()
{
    updateGridColumns();
}

// =========================================
// Init
// =========================================

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

    debug(
        'KEYBOARD',
        'initialized',
    );
}