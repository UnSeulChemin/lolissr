// =========================================
// SERIES KEYBOARD NAVIGATION
// =========================================

import {
    $,
    $$,
} from '../../core/dom.js';

import {
    navigateTo,
} from '../../router/router.js';

import {
    registerCleanup,
} from '../../router/router-cleanup.js';

import {
    debug,
} from '../../core/debug.js';

// =========================================
// CONFIG
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
// STATE
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
// HELPERS
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
}

// =========================================
// MOVEMENT
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
// KEYBOARD
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

    switch (event.key)
    {
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

            {
                const activeCard =
                    cards[
                        activeIndex
                    ];

                if (
                    activeCard
                    instanceof HTMLAnchorElement
                ) {

                    void navigateTo(
                        activeCard.href,
                    );
                }
            }

            break;

        case 'Escape':
        case 'Backspace':

            event.preventDefault();

            clearActiveState();

            break;

        default:
            break;
    }
}

// =========================================
// RESIZE
// =========================================

function handleResize()
{
    updateGridColumns();
}

// =========================================
// ROUTER LOADED
// =========================================

function handleRouteLoaded()
{
    clearActiveState();

    queueMicrotask(
        () =>
        {
            updateGridColumns();
        },
    );
}

// =========================================
// INIT
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
        'router:loaded',
        handleRouteLoaded,
    );

    registerCleanup(
        clearActiveState,
    );

    debug(
        'KEYBOARD',
        'initialized',
    );
}