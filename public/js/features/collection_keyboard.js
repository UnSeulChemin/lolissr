import { preloadUrl, preloadImage } from './prefetch.js';

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
        const container =
            document.querySelector('.collection-grid');

        if (!container)
        {
            return [];
        }

        return Array.from(
            container.querySelectorAll('.collection-card-link')
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

        activeCard.focus(); // ⭐ important

        preloadUrl(activeCard.href);

        const image =
            activeCard.querySelector('.card-image-portrait');

        if (image)
        {
            preloadImage(image.src);
        }
    }

    function openCard()
    {
        const cards = getCards();

        if (activeIndex < 0)
        {
            return;
        }

        const card = cards[activeIndex];

        if (!card)
        {
            return;
        }

        window.location.href = card.href;
    }

    function resetActive()
    {
        activeIndex = -1;

        getCards().forEach((card) =>
        {
            card.classList.remove('is-active');
        });
    }

    // 🎯 Si tu cliques une carte → elle devient active

    document.addEventListener('click', (event) =>
    {
        const card =
            event.target.closest('.collection-card-link');

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

    // 🎯 Navigation clavier

    document.addEventListener('keydown', (event) =>
    {
        const cards = getCards();

        if (cards.length === 0)
        {
            return;
        }

        // TAB = flèche droite

        if (event.key === 'Tab')
        {
            event.preventDefault();

            if (event.shiftKey)
            {
                // SHIFT+TAB = gauche

                activeIndex =
                    activeIndex > 0
                        ? activeIndex - 1
                        : cards.length - 1;
            }
            else
            {
                // TAB = droite

                activeIndex =
                    activeIndex < cards.length - 1
                        ? activeIndex + 1
                        : 0;
            }

            updateActive();
            return;
        }

        // flèches classiques

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

        if (event.key === 'Enter')
        {
            event.preventDefault();
            openCard();
        }

        if (event.key === 'Escape')
        {
            resetActive();
        }

        // ⬅️ BACKSPACE = retour

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