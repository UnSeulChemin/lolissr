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

let seriesKeyboardNavigationInitialized =
    false;

let seriesActiveCardIndex = -1;

/*
|------------------------------------------------------------------
| Helpers
|------------------------------------------------------------------
*/

function getSeriesGrid()
{
    return document.querySelector(
        '.collection-grid',
    );
}

function getSeriesCardLinks()
{
    return Array.from(
        document.querySelectorAll(
            '.collection-card-link',
        ),
    );
}

function getSeriesGridColumnCount()
{
    const grid =
        getSeriesGrid();

    if (!grid) {
        return 1;
    }

    const styles =
        window.getComputedStyle(
            grid,
        );

    const columns =
        styles.gridTemplateColumns
            .split(' ')
            .filter(Boolean);

    return columns.length || 1;
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

function blurActiveElement()
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

function clearSeriesActiveState()
{
    seriesActiveCardIndex = -1;

    blurActiveElement();

    getSeriesCardLinks().forEach(
        card =>
        {
            card.classList.remove(
                'is-active',
            );

            card.blur();
        },
    );
}

function syncSeriesActiveState()
{
    const cards =
        getSeriesCardLinks();

    if (!cards.length) {

        seriesActiveCardIndex = -1;

        return;
    }

    /*
    |--------------------------------------------------------------
    | Clamp index
    |--------------------------------------------------------------
    */

    if (
        seriesActiveCardIndex
        >= cards.length
    ) {
        seriesActiveCardIndex =
            cards.length - 1;
    }

    if (
        seriesActiveCardIndex < 0
    ) {
        seriesActiveCardIndex = 0;
    }

    const activeCard =
        cards[
            seriesActiveCardIndex
        ];

    if (!activeCard) {
        return;
    }

    /*
    |--------------------------------------------------------------
    | Smart scroll
    |--------------------------------------------------------------
    */

    const rect =
        activeCard.getBoundingClientRect();

    const viewportPadding =
        120;

    if (
        rect.bottom
            > window.innerHeight
                - viewportPadding
        || rect.top
            < viewportPadding
    ) {

        activeCard.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'nearest',
        });
    }

    /*
    |--------------------------------------------------------------
    | Prefetch pagination only
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

function openActiveSeriesCard()
{
    const cards =
        getSeriesCardLinks();

    const activeCard =
        cards[
            seriesActiveCardIndex
        ];

    if (!activeCard) {
        return;
    }

    blurActiveElement();

    window.location.href =
        activeCard.href;
}

function moveSeriesActiveIndexToNext(
    cards,
)
{
    seriesActiveCardIndex =
        seriesActiveCardIndex
            < cards.length - 1
            ? seriesActiveCardIndex + 1
            : 0;
}

function moveSeriesActiveIndexToPrevious(
    cards,
)
{
    seriesActiveCardIndex =
        seriesActiveCardIndex > 0
            ? seriesActiveCardIndex - 1
            : cards.length - 1;
}

function moveSeriesActiveIndexDown(
    cards,
)
{
    const columnCount =
        getSeriesGridColumnCount();

    if (
        seriesActiveCardIndex
        === -1
    ) {

        seriesActiveCardIndex = 0;

        return;
    }

    seriesActiveCardIndex =
        Math.min(
            seriesActiveCardIndex
                + columnCount,
            cards.length - 1,
        );
}

function moveSeriesActiveIndexUp()
{
    const columnCount =
        getSeriesGridColumnCount();

    if (
        seriesActiveCardIndex
        === -1
    ) {

        seriesActiveCardIndex = 0;

        return;
    }

    seriesActiveCardIndex =
        Math.max(
            seriesActiveCardIndex
                - columnCount,
            0,
        );
}

/*
|------------------------------------------------------------------
| Init
|------------------------------------------------------------------
*/

export function initSeriesKeyboardNavigation()
{
    if (
        seriesKeyboardNavigationInitialized
    ) {
        return;
    }

    seriesKeyboardNavigationInitialized =
        true;

    /*
    |--------------------------------------------------------------
    | Reset after AJAX
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'ajax:series-loaded',
        () =>
        {
            clearSeriesActiveState();
        },
    );

    /*
    |--------------------------------------------------------------
    | Keyboard navigation
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        event =>
        {
            if (
                isTypingContext(
                    event.target,
                )
            ) {
                return;
            }

            const cards =
                getSeriesCardLinks();

            if (!cards.length) {
                return;
            }

            switch (event.key) {

                case 'Tab':

                    event.preventDefault();

                    if (
                        seriesActiveCardIndex
                        === -1
                    ) {

                        seriesActiveCardIndex = 0;

                    } else if (
                        event.shiftKey
                    ) {

                        moveSeriesActiveIndexToPrevious(
                            cards,
                        );

                    } else {

                        moveSeriesActiveIndexToNext(
                            cards,
                        );
                    }

                    syncSeriesActiveState();

                    break;

                case 'ArrowRight':

                    event.preventDefault();

                    moveSeriesActiveIndexToNext(
                        cards,
                    );

                    syncSeriesActiveState();

                    break;

                case 'ArrowLeft':

                    event.preventDefault();

                    moveSeriesActiveIndexToPrevious(
                        cards,
                    );

                    syncSeriesActiveState();

                    break;

                case 'ArrowDown':

                    event.preventDefault();

                    moveSeriesActiveIndexDown(
                        cards,
                    );

                    syncSeriesActiveState();

                    break;

                case 'ArrowUp':

                    event.preventDefault();

                    moveSeriesActiveIndexUp();

                    syncSeriesActiveState();

                    break;

                case 'Enter':

                    event.preventDefault();

                    openActiveSeriesCard();

                    break;

                case 'Escape':

                    event.preventDefault();

                    clearSeriesActiveState();

                    break;

                case 'Backspace':

                    event.preventDefault();

                    blurActiveElement();

                    window.history.back();

                    break;

                default:
                    break;
            }
        },
    );
}