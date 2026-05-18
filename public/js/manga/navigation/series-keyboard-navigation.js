import {
    prefetchSeriesPage,
    prefetchSeriesImage
} from './prefetch-series.js';

let seriesKeyboardNavigationInitialized = false;

let seriesActiveCardIndex = -1;

function getSeriesGrid()
{
    return document.querySelector(
        '.collection-grid'
    );
}

function getSeriesCardLinks()
{
    const grid = getSeriesGrid();

    if (!grid)
    {
        return [];
    }

    return Array.from(
        grid.querySelectorAll(
            '.collection-card-link'
        )
    );
}

function getSeriesGridColumnCount()
{
    const grid = getSeriesGrid();

    if (!grid)
    {
        return 1;
    }

    const styles =
        window.getComputedStyle(grid);

    const columns =
        styles.gridTemplateColumns
            .split(' ')
            .filter(Boolean);

    return columns.length || 1;
}

function isTypingContext(target)
{
    if (!target)
    {
        return false;
    }

    return Boolean(
        target.closest(
            'input, textarea, select, [contenteditable="true"]'
        )
    );
}

function clearSeriesActiveState()
{
    const cards = getSeriesCardLinks();

    seriesActiveCardIndex = -1;

    cards.forEach((card) =>
    {
        card.classList.remove(
            'is-active'
        );

        card.blur();
    });
}

function syncSeriesActiveState()
{
    const cards = getSeriesCardLinks();

    if (cards.length === 0)
    {
        seriesActiveCardIndex = -1;

        return;
    }

    if (
        seriesActiveCardIndex
        >= cards.length
    )
    {
        seriesActiveCardIndex =
            cards.length - 1;
    }

    cards.forEach((card, index) =>
    {
        card.classList.toggle(
            'is-active',
            index === seriesActiveCardIndex
        );
    });

    if (
        seriesActiveCardIndex < 0
        || !cards[seriesActiveCardIndex]
    )
    {
        return;
    }

    const activeCard =
        cards[seriesActiveCardIndex];

    activeCard.focus({
        preventScroll: true
    });

    const rect =
        activeCard.getBoundingClientRect();

    const viewportPadding = 120;

    if (
        rect.bottom
            > window.innerHeight
            - viewportPadding
        || rect.top < viewportPadding
    )
    {
        activeCard.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'nearest'
        });
    }

    prefetchSeriesPage(
        activeCard.href
    );

    const image =
        activeCard.querySelector(
            '.card-image-portrait'
        );

    if (image)
    {
        prefetchSeriesImage(
            image.src
        );
    }
}

function openActiveSeriesCard()
{
    const cards =
        getSeriesCardLinks();

    if (
        seriesActiveCardIndex < 0
        || !cards[seriesActiveCardIndex]
    )
    {
        return;
    }

    window.location.href =
        cards[seriesActiveCardIndex].href;
}

function moveSeriesActiveIndexToNext(cards)
{
    seriesActiveCardIndex =
        seriesActiveCardIndex
            < cards.length - 1
            ? seriesActiveCardIndex + 1
            : 0;
}

function moveSeriesActiveIndexToPrevious(cards)
{
    seriesActiveCardIndex =
        seriesActiveCardIndex > 0
            ? seriesActiveCardIndex - 1
            : cards.length - 1;
}

function moveSeriesActiveIndexDown(cards)
{
    const columnCount =
        getSeriesGridColumnCount();

    if (seriesActiveCardIndex === -1)
    {
        seriesActiveCardIndex = 0;

        return;
    }

    seriesActiveCardIndex =
        seriesActiveCardIndex
            + columnCount
            < cards.length
                ? seriesActiveCardIndex
                    + columnCount
                : cards.length - 1;
}

function moveSeriesActiveIndexUp()
{
    const columnCount =
        getSeriesGridColumnCount();

    if (seriesActiveCardIndex === -1)
    {
        seriesActiveCardIndex = 0;

        return;
    }

    seriesActiveCardIndex =
        seriesActiveCardIndex
            - columnCount >= 0
                ? seriesActiveCardIndex
                    - columnCount
                : 0;
}

function handleSeriesBackNavigation()
{
    const backButton =
        document.querySelector(
            '.collection-back-button'
        );

    if (backButton)
    {
        window.location.href =
            backButton.href;

        return;
    }

    window.history.back();
}

export function initSeriesKeyboardNavigation()
{
    if (
        seriesKeyboardNavigationInitialized
    )
    {
        return;
    }

    seriesKeyboardNavigationInitialized =
        true;

    document.addEventListener(
        'click',
        (event) =>
        {
            const clickedCard =
                event.target.closest(
                    '.collection-card-link'
                );

            const grid =
                getSeriesGrid();

            if (
                !clickedCard
                || !grid
                || !grid.contains(
                    clickedCard
                )
            )
            {
                return;
            }

            const cards =
                getSeriesCardLinks();

            const clickedCardIndex =
                cards.indexOf(
                    clickedCard
                );

            if (clickedCardIndex === -1)
            {
                return;
            }

            seriesActiveCardIndex =
                clickedCardIndex;

            syncSeriesActiveState();
        }
    );

    document.addEventListener(
        'keydown',
        (event) =>
        {
            if (
                isTypingContext(
                    event.target
                )
            )
            {
                return;
            }

            const grid =
                getSeriesGrid();

            const cards =
                getSeriesCardLinks();

            if (
                !grid
                || cards.length === 0
            )
            {
                return;
            }

            if (event.key === 'Tab')
            {
                event.preventDefault();

                if (
                    seriesActiveCardIndex
                    === -1
                )
                {
                    seriesActiveCardIndex = 0;
                }
                else if (event.shiftKey)
                {
                    moveSeriesActiveIndexToPrevious(
                        cards
                    );
                }
                else
                {
                    moveSeriesActiveIndexToNext(
                        cards
                    );
                }

                syncSeriesActiveState();

                return;
            }

            if (event.key === 'ArrowRight')
            {
                event.preventDefault();

                moveSeriesActiveIndexToNext(
                    cards
                );

                syncSeriesActiveState();

                return;
            }

            if (event.key === 'ArrowLeft')
            {
                event.preventDefault();

                moveSeriesActiveIndexToPrevious(
                    cards
                );

                syncSeriesActiveState();

                return;
            }

            if (event.key === 'ArrowDown')
            {
                event.preventDefault();

                moveSeriesActiveIndexDown(
                    cards
                );

                syncSeriesActiveState();

                return;
            }

            if (event.key === 'ArrowUp')
            {
                event.preventDefault();

                moveSeriesActiveIndexUp();

                syncSeriesActiveState();

                return;
            }

            if (event.key === 'Enter')
            {
                event.preventDefault();

                openActiveSeriesCard();

                return;
            }

            if (event.key === 'Escape')
            {
                event.preventDefault();

                clearSeriesActiveState();

                return;
            }

            if (event.key === 'Backspace')
            {
                event.preventDefault();

                handleSeriesBackNavigation();
            }
        }
    );
}