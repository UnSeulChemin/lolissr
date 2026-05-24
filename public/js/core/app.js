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

function getCards()
{
    return Array.from(
        document.querySelectorAll(
            '.collection-card-link',
        ),
    );
}

function getGrid()
{
    return document.querySelector(
        '.collection-grid',
    );
}

function getGridColumns()
{
    const grid =
        getGrid();

    if (!grid) {
        return 1;
    }

    const styles =
        window.getComputedStyle(
            grid,
        );

    return styles
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

function focusCard(card)
{
    if (!card) {
        return;
    }

    card.focus({
        preventScroll: true,
    });
}

function blurCurrentFocus()
{
    if (
        document.activeElement
        instanceof HTMLElement
    ) {

        document.activeElement.blur();
    }
}

/*
|------------------------------------------------------------------
| Active State
|------------------------------------------------------------------
*/

function clearActiveState()
{
    activeIndex = -1;

    blurCurrentFocus();

    getCards().forEach(
        card =>
        {
            card.classList.remove(
                'is-active',
            );
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
    | Native focus
    |--------------------------------------------------------------
    */

    focusCard(
        activeCard,
    );

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
| Navigation
|------------------------------------------------------------------
*/

function openActiveCard()
{
    const cards =
        getCards();

    const activeCard =
        cards[activeIndex];

    if (!activeCard) {
        return;
    }

    /*
    |--------------------------------------------------------------
    | Native navigation
    |--------------------------------------------------------------
    */

    window.location.href =
        activeCard.href;
}

function navigateBack()
{
    const pathname =
        window.location.pathname;

    /*
    |--------------------------------------------------------------
    | Manga detail
    |--------------------------------------------------------------
    */

    if (
        /^\/lolissr\/manga\/series\/[^/]+$/.test(
            pathname,
        )
    ) {

        window.location.href =
            '/lolissr/manga/series';

        return;
    }

    /*
    |--------------------------------------------------------------
    | Series list
    |--------------------------------------------------------------
    */

    if (
        pathname ===
        '/lolissr/manga/series'
    ) {

        window.location.href =
            '/lolissr/manga';

        return;
    }

    /*
    |--------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------
    */

    if (
        /^\/lolissr\/manga\/series\/page\/\d+$/.test(
            pathname,
        )
    ) {

        window.location.href =
            '/lolissr/manga';

        return;
    }

    /*
    |--------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------
    */

    window.history.back();
}

/*
|------------------------------------------------------------------
| Keyboard
|------------------------------------------------------------------
*/

function handleKeyboard(event)
{
    if (
        isTypingContext(
            event.target,
        )
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------
    | Global
    |--------------------------------------------------------------
    */

    if (
        event.key === 'Backspace'
    ) {

        navigateBack();

        return;
    }

    if (
        event.key === 'Escape'
    ) {

        clearActiveState();

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

            openActiveCard();

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

    /*
    |--------------------------------------------------------------
    | Keyboard
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'keyup',
        handleKeyboard,
    );

    /*
    |--------------------------------------------------------------
    | AJAX reset
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'ajax:series-loaded',
        clearActiveState,
    );

    /*
    |--------------------------------------------------------------
    | Browser cache restore
    |--------------------------------------------------------------
    */

    window.addEventListener(
        'pageshow',
        clearActiveState,
    );
}