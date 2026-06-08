// =========================================
// FLASHCARDS VOCABULAIRE
// =========================================

import {
    post,
} from '../../core/http.js';

import {
    showToast,
} from '../../core/toast.js';

import {
    invalidateRoute,
} from '../../router/route-invalidation.js';

import {
    invalidatePrefetch,
} from '../../router/prefetch/prefetch-cache.js';

// =========================================
// CACHE
// =========================================

function invalidateVocabularyPages()
{
    const routes = [
        'mandarin',
        'jinyu',
    ];

    for (
        const route
        of routes
    ) {

        const url =
            `${window.baseUri}chinois/${route}`;

        invalidateRoute(
            url,
        );

        invalidatePrefetch(
            url,
        );
    }
}

// =========================================
// INIT
// =========================================

export function initFlashcardsVocabulairePage()
{
    const cards =
        window.flashcards ?? [];

    if (
        cards.length === 0
    ) {
        return;
    }

    let currentIndex =
        0;

    const previousButton =
        document.getElementById(
            'flashcard-previous',
        );

    const nextButton =
        document.getElementById(
            'flashcard-next',
        );

    const counterElement =
        document.getElementById(
            'flashcard-counter',
        );

    const motElement =
        document.getElementById(
            'flashcard-mot',
        );

    const pinyinElement =
        document.getElementById(
            'flashcard-pinyin',
        );

    const traductionElement =
        document.getElementById(
            'flashcard-traduction',
        );

    const exempleElement =
        document.getElementById(
            'flashcard-exemple',
        );

    const editElement =
        document.getElementById(
            'flashcard-edit',
        );

    const masteredButton =
        document.getElementById(
            'flashcard-mastered',
        );

    // =========================================
    // RENDER
    // =========================================

    function renderCard()
    {
        const card =
            cards[currentIndex];

        if (!card)
        {
            return;
        }

        counterElement.textContent =
            `Carte ${currentIndex + 1} / ${cards.length}`;

        motElement.textContent =
            card.mot;

        pinyinElement.textContent =
            card.pinyin;

        traductionElement.textContent =
            card.traduction;

        if (exempleElement)
        {
            exempleElement.textContent =
                card.exemple ?? '';
        }

        editElement.href =
            `${window.baseUri}chinois/vocabulaire/modifier/${card.id}?return_to=chinois/flashcards/vocabulaire`;

        if (
            masteredButton
            instanceof HTMLButtonElement
        ) {
            masteredButton.dataset.id =
                String(card.id);

            masteredButton.dataset.maitrise =
                '0';

            masteredButton.classList.remove(
                'active',
            );

            masteredButton.setAttribute(
                'aria-pressed',
                'false',
            );
        }
    }

    // =========================================
    // NAVIGATION
    // =========================================

    previousButton?.addEventListener(
        'click',
        () =>
        {
            currentIndex =
                (
                    currentIndex - 1
                    + cards.length
                )
                % cards.length;

            renderCard();
        },
    );

    nextButton?.addEventListener(
        'click',
        () =>
        {
            currentIndex =
                (
                    currentIndex + 1
                )
                % cards.length;

            renderCard();
        },
    );

    // =========================================
    // VALIDATION
    // =========================================

    masteredButton?.addEventListener(
        'click',
        async () =>
        {
            const card =
                cards[currentIndex];

            if (!card)
            {
                return;
            }

            try
            {
                const data =
                    await post(
                        `${window.baseUri}chinois/ajax/toggle-vocabulaire-maitrise`,
                        {
                            id:
                                card.id,
                        },
                    );

                if (!data?.success)
                {
                    showToast(
                        'Erreur',
                        'error',
                    );

                    return;
                }

                invalidateRoute(
                    window.location.href,
                );

                invalidatePrefetch(
                    window.location.href,
                );

                invalidateVocabularyPages();

                cards.splice(
                    currentIndex,
                    1,
                );

                if (
                    cards.length === 0
                ) {
                    location.reload();

                    return;
                }

                if (
                    currentIndex
                    >= cards.length
                ) {
                    currentIndex =
                        0;
                }

                renderCard();

                showToast(
                    'Carte validée',
                    'success',
                );

            } catch {

                showToast(
                    'Erreur réseau',
                    'error',
                );
            }
        },
    );

    // =========================================
    // START
    // =========================================

    renderCard();
}