import { initAdminCommands } from './js/admin/pages/commands.js';
export async function runBrowserScenario()
{
    const assert = (ok, message) =>
    { if (!ok) throw new Error(message); };
    const root = document.createElement('section');
    root.dataset.adminLive = ''; root.dataset.statusUrl = '/fixture-status';
    root.innerHTML = '<article data-job="recommendations"><p role="status">Ancien état</p><form action="/fixture-command" method="post"><input type="hidden" name="csrf_token" value="fixture-token"><button type="submit">Actualiser</button></form></article>';
    document.body.append(root);
    const originalFetch = window.fetch;
    const originalTimeout = window.setTimeout;
    window.setTimeout = (callback, delay, ...args) => originalTimeout(callback, delay === 2000 ? 50 : delay, ...args);
    const key = 'admin-journal:recommendations';
    const previous = sessionStorage.getItem(key);
    sessionStorage.removeItem(key);
    let calls = 0;
    let submissions = 0;
    window.fetch = async (url, options) =>
    {
        if (options.method === 'POST')
        {
            assert(new URL(url, location.href).pathname === '/fixture-command', 'Wrong command target');
            assert(JSON.parse(options.body).csrf_token === 'fixture-token', 'Missing form CSRF token');
            submissions++; calls = 0;
            return new Response(JSON.stringify({ success: true, message: 'Command accepted' }), { status: 200, headers: { 'Content-Type': 'application/json' } });
        }
        assert(url === '/fixture-status' && options.cache === 'no-store', 'Polling must bypass cached SPA responses');
        calls++;
        return { ok: true, status: 200, json: async () => ({ jobs: { recommendations: {
            status: { state: calls === 1 ? 'queued' : 'done', updated: 123 }, output: '<script>untrusted output<' + '/script>'
        } } }) };
    };
    try
    {
        initAdminCommands();
        await new Promise(resolve => setTimeout(resolve, 10));
        assert(root.querySelector('[role="status"]').textContent === 'En attente', 'Queued state not displayed');
        assert(root.querySelector('button[type="submit"]').disabled, 'Busy controls remain enabled');
        assert(root.querySelector('pre').textContent.includes('<script>') && !root.querySelector('script'), 'Journal output must remain plain text');
        await new Promise(resolve => setTimeout(resolve, 150));
        assert(root.querySelector('[role="status"]').textContent === 'Pr\u00eat', 'Finished state did not update automatically');
        assert(!root.querySelector('button[type="submit"]').disabled, 'Finished controls remain disabled');
        const finishedCalls = calls;
        await new Promise(resolve => setTimeout(resolve, 150));
        assert(calls === finishedCalls, 'Polling continued after the job finished');
        root.querySelector('[data-close-admin-journal]').click();
        assert(!root.querySelector('[data-admin-journal]'), 'Journal close failed');
        const cachedJournal = document.createElement('section');
        cachedJournal.dataset.adminJournal = 'recommendations';
        cachedJournal.dataset.journalVersion = '122';
        cachedJournal.innerHTML = '<pre>Cached journal</pre>';
        root.append(cachedJournal);
        initAdminCommands();
        assert(cachedJournal.hidden, 'Stale SPA journal flashed before fresh status arrived');
        await new Promise(resolve => setTimeout(resolve, 10));
        assert(!root.querySelector('[data-admin-journal]'), 'Polling restored a dismissed journal');
        const pageUrl = location.href;
        const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
        root.querySelector('form').dispatchEvent(submitEvent);
        assert(submitEvent.defaultPrevented, 'Form still performs native navigation');
        await new Promise(resolve => setTimeout(resolve, 150));
        assert(submissions === 1 && location.href === pageUrl, 'Command must run once without navigation');
        assert(root.querySelector('[role="status"]').textContent === 'Pr\u00eat', 'Polling did not restart after AJAX submission');
        root.remove();
        const disconnectedCalls = calls;
        await new Promise(resolve => setTimeout(resolve, 150));
        assert(calls === disconnectedCalls, 'Polling continued after leaving the page');
        return ['live queued/finished states', 'stop after completion', 'button state', 'safe output', 'persistent journal close', 'AJAX command with CSRF and polling restart', 'navigation cleanup'];
    }
    finally
    {
        root.remove(); initAdminCommands(); window.fetch = originalFetch; window.setTimeout = originalTimeout;
        if (previous === null) sessionStorage.removeItem(key); else sessionStorage.setItem(key, previous);
    }
}
