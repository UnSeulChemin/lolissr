// ======================================================
// update-note.js
// ======================================================
import { showToast } from '../../core/toast.js';

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

export function initUpdateNote() {
    if (document.body.dataset.updateNoteInit === 'true') return;
    document.body.dataset.updateNoteInit = 'true';

    let isSavingNotes = false;

    const getDetailCard = () => document.querySelector('.js-detail-card');

    const refreshNoteButtonsState = () => {
        const card = getDetailCard();
        if (!card) return;
        card.querySelectorAll('.ajax-note-group').forEach(group => {
            const fieldName = group.dataset.field;
            const currentValue = Number(card.dataset[fieldName] ?? '');
            group.querySelectorAll('.ajax-note-button').forEach(button => {
                const btnValue = Number(button.dataset.value);
                button.classList.toggle('active', currentValue === btnValue);
                button.disabled = isSavingNotes;
            });
        });
    };

    const saveNotes = async (fieldName, value) => {
        const card = getDetailCard();
        if (!card) return;

        const slug = card.dataset.slug;
        const numero = card.dataset.numero;
        const basePath = card.dataset.basePath;
        const totalNoteEl = document.getElementById('ajax-note-total');

        const formData = new FormData();
        formData.append('jacquette', fieldName === 'jacquette' ? value : card.dataset.jacquette || '0');
        formData.append('livre_note', fieldName === 'livreNote' ? value : card.dataset.livreNote || '0');

        const csrf = getCsrfToken();
        if (csrf) formData.append('csrf_token', csrf);

        try {
            isSavingNotes = true;
            refreshNoteButtonsState();

            const res = await fetch(`${basePath}manga/ajax/update-note/${slug}/${numero}`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await res.json();

            if (!res.ok || !data.success) throw new Error(data.message ?? 'Erreur');

            const notes = data.data?.notes ?? {};
            if (notes.jacquette !== undefined) card.dataset.jacquette = String(notes.jacquette);
            if (notes.livreNote !== undefined) card.dataset.livreNote = String(notes.livreNote);

            if (totalNoteEl) totalNoteEl.textContent = (notes.note !== undefined && notes.note !== null) ? `${notes.note}/10` : 'Non calculée';

            refreshNoteButtonsState();
            showToast(data.message ?? '✓ Sauvegardé', 'success');

        } catch (err) {
            console.error(err);
            showToast(err?.message ?? 'Erreur', 'error');
        } finally {
            isSavingNotes = false;
            refreshNoteButtonsState();
        }
    };

    document.addEventListener('click', async e => {
        const button = e.target.closest('.ajax-note-button');
        if (!button) return;
        const card = getDetailCard();
        if (!card || isSavingNotes) return;

        const group = button.closest('.ajax-note-group');
        if (!group) return;

        const fieldName = group.dataset.field;
        if (!fieldName) return;

        const value = Number(button.dataset.value);

        if (fieldName === 'jacquette') card.dataset.jacquette = String(value);
        if (fieldName === 'livreNote') card.dataset.livreNote = String(value);

        refreshNoteButtonsState();
        await saveNotes(fieldName, value);
    });

    refreshNoteButtonsState();
    document.addEventListener('ajax:series-loaded', refreshNoteButtonsState);
}

// Auto-init
document.addEventListener('DOMContentLoaded', () => initUpdateNote());