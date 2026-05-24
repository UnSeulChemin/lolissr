// ==================================================
// Series Grid Navigation
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
        .getComputedStyle(grid)
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

    if (
        activeIndex < 0
    ) {

        activeIndex = 0;
    }

    if (
        activeIndex >= cards.length
    ) {

        activeIndex =
            cards.length - 1;
    }

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
    | Prefetch
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
                    Math.max(
                        activeIndex - 1,
                        0,
                    );

            } else {

                activeIndex =
                    Math.min(
                        activeIndex + 1,
                        cards.length - 1,
                    );
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
                Math.min(
                    activeIndex + 1,
                    cards.length - 1,
                );

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
                Math.max(
                    activeIndex - 1,
                    0,
                );

            syncActiveState();

            break;

        /*
        |----------------------------------------------------------
        | DOWN
        |----------------------------------------------------------
        */

        case 'ArrowDown':

            event.preventDefault();

            activeIndex =
                Math.min(
                    activeIndex
                    + getGridColumns(),
                    cards.length - 1,
                );

            syncActiveState();

            break;

        /*
        |----------------------------------------------------------
        | UP
        |----------------------------------------------------------
        */

        case 'ArrowUp':

            event.preventDefault();

            activeIndex =
                Math.max(
                    activeIndex
                    - getGridColumns(),
                    0,
                );

            syncActiveState();

            break;

        /*
        |----------------------------------------------------------
        | ENTER
        |----------------------------------------------------------
        */

        case 'Enter':

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

export function initSeriesGridNavigation()
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
}