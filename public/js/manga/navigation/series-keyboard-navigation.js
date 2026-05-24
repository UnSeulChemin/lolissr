// ==================================================
// Series Keyboard Navigation
// ==================================================

import {
    prefetchPage,
} from './prefetch-series.js';

/*
|------------------------------------------------------------------
| State
|------------------------------------------------------------------
*/

let initialized =
    false;

let activeIndex =
    -1;

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

function isTypingContext(
    target,
)
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

        activeIndex =
            cards.length - 1;
    }

    if (
        activeIndex >= cards.length
    ) {

        activeIndex = 0;
    }

    /*
    |--------------------------------------------------------------
    | Active class
    |--------------------------------------------------------------
    */

    cards.forEach(
        (
            card,
            index,
        ) =>
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

        prefetchPage(
            nextPagination.href,
        );
    }
}

/*
|------------------------------------------------------------------
| Keyboard
|------------------------------------------------------------------
*/

function handleKeyboard(
    event,
)
{
    /*
    |--------------------------------------------------------------
    | Only collection pages
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

                activeIndex--;

            } else {

                activeIndex++;
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

            activeIndex++;

            syncActiveState();

            break;

        /*
        |----------------------------------------------------------
        | LEFT
        |----------------------------------------------------------
        */

        case 'ArrowLeft':

            event.preventDefault();

            activeIndex--;

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