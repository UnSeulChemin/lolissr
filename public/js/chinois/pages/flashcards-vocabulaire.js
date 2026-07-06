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
    updateHeaderUser,
} from '../../profil/header-user.js';

import {
    invalidateVocabularyPages,
} from '../chinois-cache.js';

// =========================================
// INIT
// =========================================

export function initFlashcardsVocabulairePage()
{
    const container =
        document.querySelector(
            '.chinois-vocab-panel',
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
            `${baseUri}chinois/flashcards/vocabulaire/modifier/${card.id}`;

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
                        `${baseUri}chinois/ajax/toggle-vocabulaire-maitrise`,
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

                updateHeaderUser(
                    data?.data?.level,
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