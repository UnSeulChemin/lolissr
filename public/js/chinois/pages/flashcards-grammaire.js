// =========================================
// FLASHCARDS GRAMMAIRE
// =========================================

import {
    post,
} from '../../core/http.js';

import {
    showToast,
} from '../../core/toast.js';

import {
    invalidatePage,
} from '../../router/page-invalidation.js';

// =========================================
// CACHE
// =========================================

function invalidateGrammarPages(baseUri)
{
    const routes = [
        'hsk1',
        'hsk2',
        'hsk3',
        'hsk4',
    ];

    for (
        const route
        of routes
    ) {

        invalidatePage(
            `${baseUri}chinois/grammaire/${route}`,
        );
    }
}

// =========================================
// INIT
// =========================================

export function initFlashcardsGrammairePage()
{
    const container =
        document.querySelector(
            '.grammar-main-section',
        );

    if (!container)
    {
        return;
    }

    const cards =
        JSON.parse(
            container.dataset.flashcards
            ?? '[]',
        );

    const baseUri =
        container.dataset.baseUri
        ?? '/';

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

    const titreElement =
        document.getElementById(
            'flashcard-titre',
        );

    const structureElement =
        document.getElementById(
            'flashcard-structure',
        );

    const phraseElement =
        document.getElementById(
            'flashcard-phrase',
        );

    const pinyinElement =
        document.getElementById(
            'flashcard-pinyin',
        );

    const traductionElement =
        document.getElementById(
            'flashcard-traduction',
        );

    const explicationElement =
        document.getElementById(
            'flashcard-explication',
        );

    const editElement =
        document.getElementById(
            'flashcard-edit',
        );

    const masteredButton =
        document.getElementById(
            'flashcard-mastered',
        );

    const deleteButton =
    document.getElementById(
        'flashcard-delete',
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

        titreElement.textContent =
            card.titre;

        structureElement.textContent =
            card.structure;

        phraseElement.textContent =
            card.phrase;

        pinyinElement.textContent =
            card.pinyin ?? '';

        traductionElement.textContent =
            card.traduction;

        explicationElement.textContent =
            card.explication ?? '';

        editElement.href =
            `${baseUri}chinois/flashcards/grammaire/modifier/${card.id}`;

        if (
            deleteButton
            instanceof HTMLButtonElement
        ) {
            deleteButton.dataset.id =
                String(card.id);
        }

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
                        `${baseUri}chinois/ajax/toggle-grammaire-maitrise`,
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

                invalidatePage(
                    window.location.href,
                );

                invalidateGrammarPages(baseUri);

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