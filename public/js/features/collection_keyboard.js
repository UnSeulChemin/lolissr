import { preloadUrl, preloadImage } from './prefetch-navigation.js';

let keyboardInitialized = false;
let activeIndex = -1;

export function initCollectionKeyboardNavigation()
{
    if (keyboardInitialized)
    {
        return;
    }

    keyboardInitialized = true;

    function getCards()
    {
        const container = document.querySelector('.collection-grid');

        if (!container)
        {
            return [];
        }

        return Array.from(
            container.querySelectorAll('.collection-card-link')
        );
    }

    function getColumns()
    {
        const cards = getCards();

        if (cards.length === 0)
        {
            return 1;
        }

        const firstTop = cards[0].offsetTop;
        let columns = 0;

        for (const card of cards)
        {
            if (card.offsetTop !== firstTop)
            {
                break;
            }

            columns++;
        }

        return columns || 1;
    }

    function isTypingContext(target)
    {
        const tag = target.tagName?.toLowerCase();

        return (
            tag === 'input'
            || tag === 'textarea'
            || tag === 'select'
            || target.isContentEditable
        );
    }

    function updateActive()
    {
        const cards = getCards();

        cards.forEach((card, index) =>
        {
            card.classList.toggle(
                'is-active',
                index === activeIndex
            );
        });

        if (activeIndex < 0 || !cards[activeIndex])
        {
            return;
        }

        const activeCard = cards[activeIndex];

        activeCard.focus();

        preloadUrl(activeCard.href);

        const image = activeCard.querySelector('.card-image-portrait');

        if (image)
        {
            preloadImage(image.src);
        }
    }

    function openCard()
    {
        const cards = getCards();

        if (activeIndex < 0 || !cards[activeIndex])
        {
            return;
        }

        window.location.href = cards[activeIndex].href;
    }

    function resetActive()
    {
        const cards = getCards();

        activeIndex = -1;

        cards.forEach((card) =>
        {
            card.classList.remove('is-active');
            card.blur();
        });
    }

    document.addEventListener('click', (event) =>
    {
        const card = event.target.closest('.collection-card-link');

        if (!card)
        {
            return;
        }

        const cards = getCards();
        const index = cards.indexOf(card);

        if (index !== -1)
        {
            activeIndex = index;
            updateActive();
        }
    });

    document.addEventListener('keydown', (event) =>
    {
        if (isTypingContext(event.target))
        {
            return;
        }

        const cards = getCards();

        if (cards.length === 0)
        {
            return;
        }

        if (event.key === 'Tab')
        {
            event.preventDefault();

            if (activeIndex === -1)
            {
                activeIndex = 0;
            }
            else if (event.shiftKey)
            {
                activeIndex = activeIndex > 0
                    ? activeIndex - 1
                    : cards.length - 1;
            }
            else
            {
                activeIndex = activeIndex < cards.length - 1
                    ? activeIndex + 1
                    : 0;
            }

            updateActive();
            return;
        }

        if (event.key === 'ArrowRight')
        {
            event.preventDefault();

            activeIndex = activeIndex < cards.length - 1
                ? activeIndex + 1
                : 0;

            updateActive();
            return;
        }

        if (event.key === 'ArrowLeft')
        {
            event.preventDefault();

            activeIndex = activeIndex > 0
                ? activeIndex - 1
                : cards.length - 1;

            updateActive();
            return;
        }

        if (event.key === 'ArrowDown')
        {
            event.preventDefault();

            const columns = getColumns();

            if (activeIndex === -1)
            {
                activeIndex = 0;
            }
            else
            {
                activeIndex = activeIndex + columns < cards.length
                    ? activeIndex + columns
                    : cards.length - 1;
            }

            updateActive();
            return;
        }

        if (event.key === 'ArrowUp')
        {
            event.preventDefault();

            const columns = getColumns();

            if (activeIndex === -1)
            {
                activeIndex = 0;
            }
            else
            {
                activeIndex = activeIndex - columns >= 0
                    ? activeIndex - columns
                    : 0;
            }

            updateActive();
            return;
        }

        if (event.key === 'Enter')
        {
            event.preventDefault();
            openCard();
            return;
        }

        if (event.key === 'Escape')
        {
            event.preventDefault();
            resetActive();
            return;
        }

        if (event.key === 'Backspace')
        {
            event.preventDefault();

            const backButton =
                document.querySelector('.collection-back-button');

            if (backButton)
            {
                window.location.href = backButton.href;
            }
            else
            {
                window.history.back();
            }

            return;
        }
    });
}