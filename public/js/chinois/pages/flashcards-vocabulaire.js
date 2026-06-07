// =========================================
// FLASHCARDS VOCABULAIRE
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

    function renderCard()
    {
        const card =
            cards[currentIndex];

        if (! card)
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
            `${window.baseUri}chinois/vocabulaire/modifier/${card.id}`;
    }

    previousButton?.addEventListener(
        'click',
        () =>
        {
            currentIndex =
                currentIndex === 0
                    ? cards.length - 1
                    : currentIndex - 1;

            renderCard();
        },
    );

    nextButton?.addEventListener(
        'click',
        () =>
        {
            currentIndex =
                (currentIndex + 1)
                % cards.length;

            renderCard();
        },
    );

    renderCard();
}