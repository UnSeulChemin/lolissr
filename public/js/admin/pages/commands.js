import { delegate } from '../../core/dom.js';
import { post } from '../../core/http.js';
import { showToast } from '../../core/toast.js';
import { registerCleanup } from '../../router/lifecycle/cleanup.js';
import { navigateTo } from '../../router/navigation/navigation.js';
import { invalidatePage } from '../../router/pages/invalidation.js';
let stopPolling = () => {};
const labels = { idle: 'Prêt', queued: 'En attente', running: 'En cours', done: 'Prêt', failed: 'Échec', interrupted: 'Interrompu' };
const dismissed = new Map();

function isClosed(key, version)
{
    if (dismissed.get(key) === version) return true;
    try
    { return sessionStorage.getItem(`admin-journal:${key}`) === version; }
    catch { return false; }
}

function updateJob(root, key, job)
{
    const cards = root.querySelectorAll(`[data-job="${key}"]`);
    if (!cards.length) return;
    const busy = ['queued', 'running'].includes(job.status.state);
    for (const card of cards)
    {
        card.querySelector('[role="status"]').textContent = labels[job.status.state] || 'État inconnu';
        for (const button of card.querySelectorAll('button[type="submit"]')) button.disabled = busy;
    }
    const version = String(job.status.updated);
    let journal = root.querySelector(`[data-admin-journal="${key}"]`);
    if (!job.output || isClosed(key, version))
    { journal?.remove(); return; }
    if (!journal)
    {
        journal = document.createElement('section');
        journal.className = 'card admin-command-journal';
        journal.dataset.adminJournal = key;
        const close = document.createElement('button');
        close.type = 'button'; close.className = 'admin-journal-close';
        close.dataset.closeAdminJournal = ''; close.setAttribute('aria-label', 'Fermer le journal');
        close.textContent = '×';
        const output = document.createElement('pre'); output.className = 'admin-command-output';
        journal.append(close, output); root.append(journal);
    }
    journal.dataset.journalVersion = version;
    const output = journal.querySelector('pre');
    if (output.textContent !== job.output) output.textContent = job.output;
    journal.hidden = false;
}

export function initAdminCommands()
{
    stopPolling();
    for (const journal of document.querySelectorAll('[data-admin-journal]'))
    {
        // Cached SPA markup can contain an old job version. Wait for fresh status.
        journal.hidden = true;
    }
    delegate(document, 'click', '[data-close-admin-journal]', event =>
    {
        const journal = event.target.closest('[data-admin-journal]');
        if (!journal) return;
        dismissed.set(journal.dataset.adminJournal, journal.dataset.journalVersion);
        try
        { sessionStorage.setItem(`admin-journal:${journal.dataset.adminJournal}`, journal.dataset.journalVersion); }
        catch { /* Closing the current journal does not require storage. */ }
        journal.remove();
    });
    const root = document.querySelector('[data-admin-live]');
    if (!root) return;
    const controller = new AbortController();
    const submitting = new Set();
    let timer;
    stopPolling = () =>
    { controller.abort(); clearTimeout(timer); };
    registerCleanup(stopPolling);
    root.addEventListener('submit', async event =>
    {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        const card = form.closest('[data-job]');
        if (!card) return;
        event.preventDefault();
        const key = card.dataset.job;
        if (submitting.has(key) || form.querySelector('button[type="submit"]')?.disabled) return;
        submitting.add(key);
        for (const group of root.querySelectorAll(`[data-job="${key}"]`))
        {
            group.querySelector('[role="status"]').textContent = 'En attente';
            for (const button of group.querySelectorAll('button[type="submit"]')) button.disabled = true;
        }
        try
        {
            const response = await post(form.action, Object.fromEntries(new FormData(form)), { signal: controller.signal, timeout: form.action.endsWith('/reset') ? 60000 : 15000 });
            if (response?.success !== true) throw new Error(response?.message || 'Impossible de lancer la commande.');
            if (!root.isConnected || controller.signal.aborted) return;
            dismissed.delete(key);
            try
            { sessionStorage.removeItem(`admin-journal:${key}`); } catch { /* Storage is optional. */ }
            showToast(response.message, 'success');
            if (response.redirect)
            {
                stopPolling();
                invalidatePage(response.redirect);
                await navigateTo(response.redirect, { force: true });
                return;
            }
        }
        catch (error)
        {
            if (root.isConnected && !controller.signal.aborted) showToast(error?.data?.message || error.message, 'error');
        }
        finally
        {
            submitting.delete(key);
            if (root.isConnected && !controller.signal.aborted && submitting.size === 0) initAdminCommands();
        }
    }, { signal: controller.signal });
    async function poll()
    {
        if (!root.isConnected || controller.signal.aborted) return;
        try
        {
            const response = await fetch(root.dataset.statusUrl, { cache: 'no-store', headers: { Accept: 'application/json' }, signal: controller.signal });
            if (response.status === 401 || response.status === 403 || response.status === 404 || response.redirected) return;
            if (response.ok)
            {
                const data = await response.json();
                if (!root.isConnected || controller.signal.aborted) return;
                for (const key of ['releases', 'recommendations', 'maintenance'])
                    if (data.jobs?.[key] && !submitting.has(key)) updateJob(root, key, data.jobs[key]);
                const keys = [...new Set([...root.querySelectorAll('[data-job]')].map(card => card.dataset.job))];
                if (keys.every(key => data.jobs?.[key] && !['queued', 'running'].includes(data.jobs[key].status.state))) return;
            }
        }
        catch { /* Retry transient network failures while this page remains open. */ }
        if (root.isConnected && !controller.signal.aborted) timer = setTimeout(poll, 2000);
    }
    void poll();
}
