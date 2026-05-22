import { showToast } from '../../core/toast.js';

/**
 * Récupère le CSRF token depuis le meta tag.
 */
function getCsrfToken() {
    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';
}

/**
 * Met à jour l'état visuel du bouton et son label.
 */
function updateButtonState(button, readStatus) {
    button.dataset.readStatus = String(readStatus);

    const isRead = readStatus === 1;

    button.classList.toggle('active', isRead);

    const label = isRead
        ? 'Marquer comme non lu'
        : 'Marquer comme lu';

    button.title = label;
    button.setAttribute('aria-label', label);
}

/**
 * Envoie la requête AJAX pour mettre à jour le statut lu/non lu.
 */
async function sendReadStatusRequest(url, readStatus) {
    const formData = new FormData();
    formData.append('readStatus', String(readStatus));

    const csrfToken = getCsrfToken();
    if (csrfToken !== '') {
        formData.append('csrf_token', csrfToken);
    }

    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: formData,
    });

    const data = await response.json();
    return { response, data };
}

/**
 * Initialise le comportement des boutons de statut lu/non lu.
 */
export function initUpdateReadStatus() {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('.ajax-lu-button');

        if (!button || button.disabled) return;

        const url = button.dataset.url;
        if (!url) {
            showToast('URL de mise à jour manquante', 'error');
            return;
        }

        // Lecture de l'état actuel
        const currentReadStatus = button.dataset.readStatus === '1';
        const nextReadStatus = currentReadStatus ? 0 : 1;

        // Désactive le bouton pendant la requête
        button.disabled = true;

        try {
            const { response, data } = await sendReadStatusRequest(url, nextReadStatus);

            if (!response.ok || !data.success) {
                showToast(data.message ?? 'Erreur lors de la mise à jour', 'error');
                return;
            }

            // Assure que readStatus renvoyé est bien un nombre 0 ou 1
            const readStatus = Number(data.readStatus ?? nextReadStatus);

            updateButtonState(button, readStatus);

            showToast(data.message ?? 'Statut mis à jour', 'success');
        } catch (error) {
            console.error(error);
            showToast('Erreur réseau', 'error');
        } finally {
            button.disabled = false;
        }
    });
}