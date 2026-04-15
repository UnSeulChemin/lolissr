export function initCollectionKeyboardNavigation()
{
    const container = document.querySelector('.collection-grid');

    if (!container)
    {
        return;
    }

    let activeIndex = -1;

    function getCards()
    {
        return Array.from(
            container.querySelectorAll('.collection-card')
        );
    }

    function getColumns()
    {
        const style = getComputedStyle(container);
        const columns = style.gridTemplateColumns.split(' ').length;

        return columns || 4;
    }

    function resetActive()
    {
        activeIndex = -1;

        getCards().forEach((card) =>
        {
            card.classList.remove('is-active');
        });
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

        if (activeIndex >= 0 && cards[activeIndex])
        {
            cards[activeIndex].scrollIntoView({
                block: 'nearest'
            });
        }
    }

    function openCard()
    {
        const cards = getCards();

        if (activeIndex < 0 || !cards[activeIndex])
        {
            return;
        }

        const link =
            cards[activeIndex].querySelector('a');

        if (link)
        {
            window.location.href = link.href;
        }
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

        const columns = getColumns();

        if (event.key === 'ArrowRight')
        {
            event.preventDefault();

            activeIndex =
                activeIndex < cards.length - 1
                    ? activeIndex + 1
                    : 0;

            updateActive();
        }

        if (event.key === 'ArrowLeft')
        {
            event.preventDefault();

            activeIndex =
                activeIndex > 0
                    ? activeIndex - 1
                    : cards.length - 1;

            updateActive();
        }

        if (event.key === 'ArrowDown')
        {
            event.preventDefault();

            activeIndex =
                activeIndex + columns < cards.length
                    ? activeIndex + columns
                    : activeIndex % columns;

            updateActive();
        }

        if (event.key === 'ArrowUp')
        {
            event.preventDefault();

            activeIndex =
                activeIndex - columns >= 0
                    ? activeIndex - columns
                    : cards.length -
                      (columns - activeIndex % columns);

            updateActive();
        }

        if (event.key === 'Enter')
        {
            event.preventDefault();
            openCard();
        }

        if (event.key === 'Escape')
        {
            resetActive();
        }
    });
}