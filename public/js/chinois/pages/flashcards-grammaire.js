// =========================================
// FLASHCARDS GRAMMAIRE
// =========================================

export function initFlashcardsGrammairePage()
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

        titreElement.textContent =
            card.titre;

        structureElement.textContent =
            card.structure;

        phraseElement.textContent =
            card.phrase;

        pinyinElement.textContent =
            card.pinyin;

        traductionElement.textContent =
            card.traduction;

        explicationElement.textContent =
            card.explication;

        editElement.href =
            `${window.baseUri}chinois/grammaire/modifier/${card.id}`;
    }

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

    renderCard();
}