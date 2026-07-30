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
    updateHeaderUser,
} from '../../profil/header-user.js';

import {
    invalidateGrammarPages,
} from '../chinois-cache.js';

// =========================================
// INIT
// =========================================

export function initFlashcardsGrammairePage()
{
    const container = document.querySelector('.grammar-main-section');

    if (! container)
    {
        return;
    }

    const cards = JSON.parse(container.dataset.flashcards ?? '[]');
    const baseUri = container.dataset.baseUri ?? '/';

    if (cards.length === 0)
    {
        return;
    }

    const previousButton = document.getElementById('flashcard-previous');
    const nextButton = document.getElementById('flashcard-next');
    const counterElement = document.getElementById('flashcard-counter');
    const titreElement = document.getElementById('flashcard-titre');
    const structureElement = document.getElementById('flashcard-structure');
    const phraseElement = document.getElementById('flashcard-phrase');
    const pinyinElement = document.getElementById('flashcard-pinyin');
    const traductionElement = document.getElementById('flashcard-traduction');
    const explicationElement = document.getElementById('flashcard-explication');
    const editElement = document.getElementById('flashcard-edit');
    const masteredButton = document.getElementById('flashcard-mastered');
    const deleteButton = document.getElementById('flashcard-delete');

    let currentIndex = 0;

    // =========================================
    // RENDER
    // =========================================

    function renderCard()
    {
        const card = cards[currentIndex];

        if (! card)
        {
            return;
        }

        if (counterElement)
        {
            counterElement.textContent = `Carte ${currentIndex + 1} / ${cards.length}`;
        }

        if (titreElement)
        {
            titreElement.textContent = card.titre;
        }

        if (structureElement)
        {
            structureElement.textContent = card.structure;
        }

        if (phraseElement)
        {
            phraseElement.textContent = card.phrase;
        }

        if (pinyinElement)
        {
            pinyinElement.textContent = card.pinyin ?? '';
        }

        if (traductionElement)
        {
            traductionElement.textContent = card.traduction;
        }

        if (explicationElement)
        {
            explicationElement.textContent = card.explication ?? '';
            explicationElement.hidden = ! card.hasExplication;
        }

        if (editElement instanceof HTMLAnchorElement)
        {
            editElement.href = `${baseUri}chinois/grammaire/${card.niveau.toLowerCase()}/modifier/${card.id}`;
        }

        if (deleteButton instanceof HTMLButtonElement)
        {
            deleteButton.dataset.id = String(card.id);
        }

        if (masteredButton instanceof HTMLButtonElement)
        {
            masteredButton.dataset.id = String(card.id);
            masteredButton.dataset.maitrise = '0';
            masteredButton.classList.remove('active');
            masteredButton.setAttribute('aria-pressed', 'false');
        }
    }

    // =========================================
    // NAVIGATION
    // =========================================

    previousButton?.addEventListener('click', () =>
    {
        currentIndex = (currentIndex - 1 + cards.length) % cards.length;

        renderCard();
    });

    nextButton?.addEventListener('click', () =>
    {
        currentIndex = (currentIndex + 1) % cards.length;

        renderCard();
    });

    // =========================================
    // VALIDATION
    // =========================================

    masteredButton?.addEventListener('click', async () =>
    {
        const card = cards[currentIndex];

        if (! card)
        {
            return;
        }

        try
        {
            const data = await post(
                `${baseUri}chinois/ajax/toggle-grammaire-maitrise`,
                {
                    id: card.id,
                }
            );

            if (! data?.success)
            {
                showToast('Erreur', 'error');

                return;
            }

            updateHeaderUser(data?.data?.level);
            invalidateGrammarPages();

            cards.splice(currentIndex, 1);

            if (cards.length === 0)
            {
                location.reload();

                return;
            }

            if (currentIndex >= cards.length)
            {
                currentIndex = 0;
            }

            renderCard();

            showToast('Carte validée', 'success');
        }
        catch
        {
            showToast('Erreur réseau', 'error');
        }
    });

    // =========================================
    // START
    // =========================================

    renderCard();
}