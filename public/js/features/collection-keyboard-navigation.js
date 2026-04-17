import {
    prefetchCollectionPage,
    prefetchCollectionImage
} from './prefetch-collection.js';

let collectionKeyboardNavigationInitialized = false;
let collectionActiveCardIndex = -1;

function getCollectionGrid()
{
    return document.querySelector('.collection-grid');
}

function getCollectionCardLinks()
{
    const grid = getCollectionGrid();

    if (!grid)
    {
        return [];
    }

    return Array.from(
        grid.querySelectorAll('.collection-card-link')
    );
}

function getCollectionGridColumnCount()
{
    const cards = getCollectionCardLinks();

    if (cards.length === 0)
    {
        return 1;
    }

    const firstRowTop = cards[0].offsetTop;
    let columnCount = 0;

    for (const card of cards)
    {
        if (card.offsetTop !== firstRowTop)
        {
            break;
        }

        columnCount++;
    }

    return columnCount || 1;
}

function isTypingContext(target)
{
    if (!target)
    {
        return false;
    }

    return Boolean(
        target.closest('input, textarea, select, [contenteditable="true"]')
    );
}

function clearCollectionActiveState()
{
    const cards = getCollectionCardLinks();

    collectionActiveCardIndex = -1;

    cards.forEach((card) =>
    {
        card.classList.remove('is-active');
        card.blur();
    });
}

function syncCollectionActiveState()
{
    const cards = getCollectionCardLinks();

    if (cards.length === 0)
    {
        collectionActiveCardIndex = -1;
        return;
    }

    if (collectionActiveCardIndex >= cards.length)
    {
        collectionActiveCardIndex = cards.length - 1;
    }

    cards.forEach((card, index) =>
    {
        card.classList.toggle('is-active', index === collectionActiveCardIndex);
    });

    if (collectionActiveCardIndex < 0 || !cards[collectionActiveCardIndex])
    {
        return;
    }

    const activeCard = cards[collectionActiveCardIndex];

    activeCard.focus({ preventScroll: true });

    prefetchCollectionPage(activeCard.href);

    const image = activeCard.querySelector('.card-image-portrait');

    if (image)
    {
        prefetchCollectionImage(image.src);
    }
}

function openActiveCollectionCard()
{
    const cards = getCollectionCardLinks();

    if (collectionActiveCardIndex < 0 || !cards[collectionActiveCardIndex])
    {
        return;
    }

    window.location.href = cards[collectionActiveCardIndex].href;
}

function moveCollectionActiveIndexToNext(cards)
{
    collectionActiveCardIndex = collectionActiveCardIndex < cards.length - 1
        ? collectionActiveCardIndex + 1
        : 0;
}

function moveCollectionActiveIndexToPrevious(cards)
{
    collectionActiveCardIndex = collectionActiveCardIndex > 0
        ? collectionActiveCardIndex - 1
        : cards.length - 1;
}

function moveCollectionActiveIndexDown(cards)
{
    const columnCount = getCollectionGridColumnCount();

    if (collectionActiveCardIndex === -1)
    {
        collectionActiveCardIndex = 0;
        return;
    }

    collectionActiveCardIndex = collectionActiveCardIndex + columnCount < cards.length
        ? collectionActiveCardIndex + columnCount
        : cards.length - 1;
}

function moveCollectionActiveIndexUp()
{
    const columnCount = getCollectionGridColumnCount();

    if (collectionActiveCardIndex === -1)
    {
        collectionActiveCardIndex = 0;
        return;
    }

    collectionActiveCardIndex = collectionActiveCardIndex - columnCount >= 0
        ? collectionActiveCardIndex - columnCount
        : 0;
}

function handleCollectionBackNavigation()
{
    const backButton = document.querySelector('.collection-back-button');

    if (backButton)
    {
        window.location.href = backButton.href;
        return;
    }

    window.history.back();
}

export function initCollectionKeyboardNavigation()
{
    if (collectionKeyboardNavigationInitialized)
    {
        return;
    }

    collectionKeyboardNavigationInitialized = true;

    document.addEventListener('click', (event) =>
    {
        const clickedCard = event.target.closest('.collection-card-link');
        const grid = getCollectionGrid();

        if (!clickedCard || !grid || !grid.contains(clickedCard))
        {
            return;
        }

        const cards = getCollectionCardLinks();
        const clickedCardIndex = cards.indexOf(clickedCard);

        if (clickedCardIndex === -1)
        {
            return;
        }

        collectionActiveCardIndex = clickedCardIndex;
        syncCollectionActiveState();
    });

    document.addEventListener('keydown', (event) =>
    {
        if (isTypingContext(event.target))
        {
            return;
        }

        const grid = getCollectionGrid();
        const cards = getCollectionCardLinks();

        if (!grid || cards.length === 0)
        {
            return;
        }

        if (event.key === 'Tab')
        {
            event.preventDefault();

            if (collectionActiveCardIndex === -1)
            {
                collectionActiveCardIndex = 0;
            }
            else if (event.shiftKey)
            {
                moveCollectionActiveIndexToPrevious(cards);
            }
            else
            {
                moveCollectionActiveIndexToNext(cards);
            }

            syncCollectionActiveState();
            return;
        }

        if (event.key === 'ArrowRight')
        {
            event.preventDefault();
            moveCollectionActiveIndexToNext(cards);
            syncCollectionActiveState();
            return;
        }

        if (event.key === 'ArrowLeft')
        {
            event.preventDefault();
            moveCollectionActiveIndexToPrevious(cards);
            syncCollectionActiveState();
            return;
        }

        if (event.key === 'ArrowDown')
        {
            event.preventDefault();
            moveCollectionActiveIndexDown(cards);
            syncCollectionActiveState();
            return;
        }

        if (event.key === 'ArrowUp')
        {
            event.preventDefault();
            moveCollectionActiveIndexUp();
            syncCollectionActiveState();
            return;
        }

        if (event.key === 'Enter')
        {
            event.preventDefault();
            openActiveCollectionCard();
            return;
        }

        if (event.key === 'Escape')
        {
            event.preventDefault();
            clearCollectionActiveState();
            return;
        }

        if (event.key === 'Backspace')
        {
            event.preventDefault();
            handleCollectionBackNavigation();
        }
    });
}