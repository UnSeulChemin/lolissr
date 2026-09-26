import { createFlashcardDeck } from './flashcard-deck.js';

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

const initializedContainers = new WeakSet();

export function initFlashcardsVocabulairePage()
{
    const container = document.querySelector('.chinois-vocab-panel');

    if (! container)
    {
        return;
    }

    if (initializedContainers.has(container)) return;
    initializedContainers.add(container);
    const deck = createFlashcardDeck(container, 'vocabulaire');
    const baseUri = container.dataset.baseUri ?? '/';

    if (deck.total === 0)
    {
        return;
    }

    const previousButton = document.getElementById('flashcard-previous');
    const nextButton = document.getElementById('flashcard-next');
    const counterElement = document.getElementById('flashcard-counter');
    const motElement = document.getElementById('flashcard-mot');
    const pinyinElement = document.getElementById('flashcard-pinyin');
    const traductionElement = document.getElementById('flashcard-traduction');
    const exempleElement = document.getElementById('flashcard-exemple');
    const editElement = document.getElementById('flashcard-edit');
    const masteredButton = document.getElementById('flashcard-mastered');
    const deleteButton = document.getElementById('flashcard-delete');

    let busy = false;
    function setBusy(value)
    {
        busy = value;
        for (const button of [previousButton, nextButton, masteredButton, deleteButton])
        {
            if (button) button.disabled = value;
        }
    }

    // =========================================
    // RENDER
    // =========================================

    function renderCard()
    {
        const card = deck.card;

        if (! card)
        {
            return;
        }

        if (counterElement)
        {
            counterElement.textContent = `Carte ${deck.index + 1} / ${deck.total}`;
        }

        if (motElement)
        {
            motElement.textContent = card.mot;
        }

        if (pinyinElement)
        {
            pinyinElement.textContent = card.pinyin;
        }

        if (traductionElement)
        {
            traductionElement.textContent = card.traduction;
        }

        if (exempleElement)
        {
            exempleElement.textContent = card.exemple ?? '';
            exempleElement.hidden = ! card.hasExemple;
        }

        if (editElement instanceof HTMLAnchorElement)
        {
            editElement.href = `${baseUri}chinois/vocabulaire/${card.langue}/modifier/${card.id}`;
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

    async function navigate(direction)
    {
        if (busy) return;
        setBusy(true);
        try
        {
            await deck.move(direction);
            if (! container.isConnected) return;
            if (! deck.total) { location.reload(); return; }
            renderCard();
        }
        catch
        {
            if (container.isConnected) showToast('Chargement impossible, réessaie.', 'error');
        }
        finally
        {
            setBusy(false);
        }
    }

    previousButton?.addEventListener('click', () => { void navigate(-1); });
    nextButton?.addEventListener('click', () => { void navigate(1); });

    // =========================================
    // VALIDATION
    // =========================================

    masteredButton?.addEventListener('click', async () =>
    {
        if (busy) return;
        const card = deck.card;

        if (! card)
        {
            return;
        }

        setBusy(true);
        let saved = false;
        try
        {
            const data = await post(
                `${baseUri}chinois/ajax/toggle-vocabulaire-maitrise`,
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
            invalidateVocabularyPages();

            saved = true;
            if (! container.isConnected) return;
            await deck.remove(card.id);
            if (! container.isConnected) return;

            if (deck.total === 0)
            {
                location.reload();

                return;
            }

            renderCard();

            showToast('Carte validée', 'success');
        }
        catch
        {
            if (container.isConnected)
            {
                if (saved) { location.reload(); return; }
                showToast('Erreur réseau', 'error');
            }
        }
        finally
        {
            setBusy(false);
        }
    });

    // =========================================
    // START
    // =========================================

    renderCard();
}