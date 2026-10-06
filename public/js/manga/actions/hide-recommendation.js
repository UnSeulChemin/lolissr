import { confirmModal } from '../../core/modal/confirm-modal.js';
import { post } from '../../core/http.js';
import { showToast } from '../../core/toast.js';
import { registerCleanup } from '../../router/lifecycle/cleanup.js';
import { invalidateMangaPages } from '../cache-invalidation.js';
import { navigateTo } from '../../router/navigation/navigate.js';

async function refreshRecommendations()
{
    if (!document.querySelector('[data-recommendation-url]')) return;
    const url = new URL(location.href);
    url.searchParams.set('reconcile', '1');
    await navigateTo(url.href, { force: true, updateHistory: false });
    const canonical = document.querySelector('[data-recommendation-url]')?.dataset.recommendationUrl;
    if (canonical) history.replaceState(history.state, '', canonical);
}

export function initHideRecommendation()
{
    const controller = new AbortController();
    registerCleanup(() => controller.abort());
    document.addEventListener('submit', async event =>
    {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('.js-favorite-recommendation, .js-restore-recommendation')) return;
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (!button || button.disabled) return;
        button.disabled = true;
        const removing = form.action.endsWith('/retirer');
        const restoring = form.matches('.js-restore-recommendation');
        let invalidated = false;
        try
        {
            const response = await post(form.action, {}, { signal: controller.signal });
            if (response?.success !== true) throw new Error(response?.message || 'Impossible de modifier les favoris');
            invalidateMangaPages();
            invalidated = true;
            if (controller.signal.aborted || !form.isConnected) return;
            if (restoring || (removing && form.dataset.favoritesPage === 'true'))
            {
                const grid = form.closest('.collection-grid');
                form.closest('.collection-release-item')?.remove();
                grid?.querySelectorAll('.recommendation-rank').forEach((badge, index) =>
                {
                    const rank = Number(grid.dataset.rankOffset || 0) + index + 1;
                    badge.textContent = rank;
                    badge.setAttribute('aria-label', `Rang ${rank}`);
                });
                if (grid && !grid.querySelector('.collection-release-item'))
                {
                    if (!restoring)
                    {
                        const page = grid.parentElement;
                        page?.querySelector('.collection-pagination-wrapper')?.remove();
                    }
                    grid.remove();
                }
            }
            else
            {
                form.action = removing ? form.action.slice(0, -8) : `${form.action}/retirer`;
                button.textContent = removing ? '♡ Ajouter aux favoris' : '♥ Retirer des favoris';
                button.setAttribute('aria-pressed', removing ? 'false' : 'true');
            }
            showToast(response.message, 'success');
            if (restoring || (removing && form.dataset.favoritesPage === 'true')) await refreshRecommendations();
        }
        catch (error)
        {
            if (!controller.signal.aborted) showToast(error?.data?.message || error.message, 'error');
        }
        finally
        { if (!invalidated) invalidateMangaPages(); button.disabled = false; }
    }, { signal: controller.signal });
    document.addEventListener('submit', async event =>
    {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('.js-hide-recommendation')) return;
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (!button || button.disabled) return;
        button.disabled = true;
        let submitted = false;
        let invalidated = false;
        try
        {
            const confirmed = await confirmModal({
                title: 'Masquer cette suggestion ?',
                message: 'Cette série sera masquée dans tes recommandations, sans modifier ta collection.',
                confirmText: 'Oui, masquer'
            });
            if (!confirmed || controller.signal.aborted || !form.isConnected) return;
            submitted = true;
            const response = await post(form.action, {}, { signal: controller.signal });
            if (response?.success !== true) throw new Error(response?.message || 'Impossible de masquer la suggestion');
            invalidateMangaPages();
            invalidated = true;
            if (!form.isConnected || controller.signal.aborted) return;
            const grid = form.closest('.collection-grid');
            form.closest('.collection-release-item')?.remove();
            grid?.querySelectorAll('.recommendation-rank').forEach((badge, index) =>
            {
                const rank = Number(grid.dataset.rankOffset || 0) + index + 1;
                badge.textContent = rank;
                badge.setAttribute('aria-label', `Rang ${rank}`);
            });
            if (grid && !grid.querySelector('.collection-release-item'))
            {
                const empty = document.createElement('p');
                empty.className = 'collection-empty';
                empty.textContent = 'Toutes les suggestions de cette liste ont été masquées.';
                grid.replaceWith(empty);
            }
            showToast(response.message, 'success');
            await refreshRecommendations();
        }
        catch (error)
        {
            if (!controller.signal.aborted) showToast(error?.data?.message || error.message, 'error');
        }
        finally
        {
            if (submitted && !invalidated) invalidateMangaPages();
            button.disabled = false;
        }
    }, { signal: controller.signal });
}
