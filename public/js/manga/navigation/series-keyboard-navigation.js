// ==================================================
// Series Keyboard Navigation
// ==================================================

import {
    prefetchSeriesPage,
} from './prefetch-series.js';

/*
|------------------------------------------------------------------
| State
|------------------------------------------------------------------
*/

let initialized = false;

let activeIndex = -1;

/*
|------------------------------------------------------------------
| Helpers
|------------------------------------------------------------------
*/

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

function getGridColumns()
{
    const grid =
        getGrid();

    if (!grid) {
        return 1;
    }

    return window
        .getComputedStyle(
            grid,
        )
        .gridTemplateColumns
        .split(' ')
        .filter(Boolean)
        .length;
}

function isTypingContext(target)
{
    if (!target) {
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

/*
|------------------------------------------------------------------
| Active State
|------------------------------------------------------------------
*/

function clearActiveState()
{
    activeIndex = -1;

    getCards().forEach(
        card =>
        {
            card.classList.remove(
                'is-active',
            );

            card.blur();
        },
    );
}

function syncActiveState()
{
    const cards =
        getCards();

    if (!cards.length) {
        return;
    }

    /*
    |--------------------------------------------------------------
    | Clamp
    |--------------------------------------------------------------
    */

    activeIndex = Math.max(
        0,
        Math.min(
            activeIndex,
            cards.length - 1,
        ),
    );

    /*
    |--------------------------------------------------------------
    | Active class
    |--------------------------------------------------------------
    */

    cards.forEach(
        (card, index) =>
        {
            card.classList.toggle(
                'is-active',
                index === activeIndex,
            );
        },
    );

    const activeCard =
        cards[activeIndex];

    if (!activeCard) {
        return;
    }

    /*
    |--------------------------------------------------------------
    | Focus
    |--------------------------------------------------------------
    */

    activeCard.focus({
        preventScroll: true,
    });

    /*
    |--------------------------------------------------------------
    | Scroll
    |--------------------------------------------------------------
    */

    activeCard.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'nearest',
    });

    /*
    |--------------------------------------------------------------
    | Prefetch next page
    |--------------------------------------------------------------
    */

    const nextPagination =
        document.querySelector(
            '.collection-pagination-link.active + .collection-pagination-link',
        );

    if (nextPagination) {

        prefetchSeriesPage(
            nextPagination.href,
        );
    }
}

/*
|------------------------------------------------------------------
| Keyboard
|------------------------------------------------------------------
*/

function handleKeyboard(event)
{
    /*
    |--------------------------------------------------------------
    | Only collection page
    |--------------------------------------------------------------
    */

    if (!getGrid()) {
        return;
    }

    /*
    |--------------------------------------------------------------
    | Ignore typing
    |--------------------------------------------------------------
    */

    if (
        isTypingContext(
            event.target,
        )
    ) {
        return;
    }

    const cards =
        getCards();

    if (!cards.length) {
        return;
    }

    switch (event.key) {

        /*
        |----------------------------------------------------------
        | TAB
        |----------------------------------------------------------
        */

        case 'Tab':

            event.preventDefault();

            if (
                activeIndex === -1
            ) {

                activeIndex = 0;

            } else if (
                event.shiftKey
            ) {

                activeIndex =
                    activeIndex > 0
                        ? activeIndex - 1
                        : cards.length - 1;

            } else {

                activeIndex =
                    activeIndex < cards.length - 1
                        ? activeIndex + 1
                        : 0;
            }

            syncActiveState();

            break;

        /*
        |----------------------------------------------------------
        | RIGHT
        |----------------------------------------------------------
        */

        case 'ArrowRight':

            event.preventDefault();

            activeIndex =
                activeIndex < cards.length - 1
                    ? activeIndex + 1
                    : 0;

            syncActiveState();

            break;

        /*
        |----------------------------------------------------------
        | LEFT
        |----------------------------------------------------------
        */

        case 'ArrowLeft':

            event.preventDefault();

            activeIndex =
                activeIndex > 0
                    ? activeIndex - 1
                    : cards.length - 1;

            syncActiveState();

            break;

        /*
        |----------------------------------------------------------
        | DOWN
        |----------------------------------------------------------
        */

        case 'ArrowDown':

            event.preventDefault();

            activeIndex +=
                getGridColumns();

            if (
                activeIndex >= cards.length
            ) {

                activeIndex =
                    activeIndex % cards.length;
            }

            syncActiveState();

            break;

        /*
        |----------------------------------------------------------
        | UP
        |----------------------------------------------------------
        */

        case 'ArrowUp':

            event.preventDefault();

            activeIndex -=
                getGridColumns();

            if (
                activeIndex < 0
            ) {

                activeIndex =
                    cards.length + activeIndex;
            }

            syncActiveState();

            break;

        /*
        |----------------------------------------------------------
        | ENTER
        |----------------------------------------------------------
        */

        case 'Enter':

            event.preventDefault();

            if (
                activeIndex === -1
            ) {
                return;
            }

            cards[
                activeIndex
            ]?.click();

            break;

        /*
        |----------------------------------------------------------
        | ESCAPE
        |----------------------------------------------------------
        */

        case 'Escape':

            event.preventDefault();

            clearActiveState();

            break;

        default:
            break;
    }
}

/*
|------------------------------------------------------------------
| Init
|------------------------------------------------------------------
*/

export function initSeriesKeyboardNavigation()
{
    if (initialized) {
        return;
    }

    initialized = true;

    document.addEventListener(
        'keydown',
        handleKeyboard,
    );

    document.addEventListener(
        'ajax:series-loaded',
        clearActiveState,
    );

    window.addEventListener(
        'pageshow',
        clearActiveState,
    );
}