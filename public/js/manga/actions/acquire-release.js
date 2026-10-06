import { post } from '../../core/http.js';
import { showToast } from '../../core/toast.js';
import { confirmModal } from '../../core/modal/confirm-modal.js';
import { invalidateMangaPages } from '../cache.js';
import { navigateTo } from '../../router/navigation/navigation.js';
import { registerCleanup } from '../../router/lifecycle/cleanup.js';

export function initAcquireRelease()
{
    const controller = new AbortController();
    registerCleanup(() => controller.abort());
    document.addEventListener('submit', async event =>
    {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('.js-acquire-release')) return;
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (!button || button.disabled) return;
        button.disabled = true;
        const originalText = button.textContent;
        let submitted = false;
        try
        {
            const confirmed = await confirmModal({
                title: 'Ajouter ce tome ?',
                message: 'Ce tome sera ajouté à ta collection comme non lu, avec sa couverture.',
                confirmText: 'Oui, je le possède'
            });
            if (!confirmed || controller.signal.aborted || !form.isConnected) return;
            button.textContent = 'Ajout en cours…';
            submitted = true;
            const response = await post(form.action, {}, { signal: controller.signal });
            if (response?.success !== true) throw new Error(response?.message || 'Impossible d’ajouter ce tome');
            // Invalidate even if navigation happened while the server committed the write.
            invalidateMangaPages();
            if (controller.signal.aborted || !form.isConnected) return;
            showToast(response.message || 'Tome ajouté à ta collection', 'success');
            await navigateTo(window.location.href, { force: true });
        }
        catch (error)
        {
            if (!controller.signal.aborted) showToast(error?.data?.message || error.message || 'Impossible d’ajouter ce tome', 'error');
        }
        finally
        {
            if (submitted) invalidateMangaPages();
            button.disabled = false;
            button.textContent = originalText;
        }
    }, { signal: controller.signal });
}
