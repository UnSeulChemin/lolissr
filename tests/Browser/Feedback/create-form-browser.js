export async function runBrowserScenario()
{
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    const tick = () => new Promise(resolve => setTimeout(resolve, 0));
    const originalFetch = window.fetch;
    const toast = document.createElement('div');
    toast.id = 'toast';
    document.body.append(toast);
    let requests = [];
    window.fetch = (url, options) => new Promise((resolve, reject) => requests.push({url, options, resolve, reject}));
    const respond = (success, status = 200) => requests.shift().resolve(new Response(JSON.stringify({success, message: 'Fixture message'}),
        {status, headers: {'Content-Type': 'application/json'}}));
    const results = [];
    try
    {
        for (const [module, page] of [['manga', 'ajouter'], ['figurine', 'ajouter'], ['nendoroid', 'ajouter'], ['peluche', 'ajouter'],
            ['chinois', 'ajouter-vocabulaire'], ['chinois', 'ajouter-grammaire']])
        {
            const form = document.createElement('form');
            form.className = 'form-layout';
            form.dataset.formPage = page;
            form.action = '/fixture-create';
            form.innerHTML = '<input name="fixture" value=""><button type="submit">Ajouter</button>';
            document.body.append(form);
            (await import(`./js/${module}/pages/create.js`)).initCreatePage();
            const input = form.querySelector('input');
            const button = form.querySelector('button');
            const submit = () => form.dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
            input.value = 'KEEP_VALUE';
            submit(); submit();
            check(requests.length === 1 && button.disabled, module + ': duplicate submit');
            respond(false, 422);
            await tick(); await tick();
            check(!button.disabled && input.value === 'KEEP_VALUE', module + ': validation error lost values or blocked retry');
            submit();
            requests.shift().reject(new TypeError('Offline'));
            await tick(); await tick();
            check(!button.disabled && input.value === 'KEEP_VALUE', module + ': network error lost values or blocked retry');
            submit();
            respond(false);
            await tick(); await tick();
            check(!button.disabled && input.value === 'KEEP_VALUE', module + ': logical failure reset form');
            submit();
            respond(true);
            await tick(); await tick();
            check(!button.disabled && input.value === '', module + ': success failed to reset');
            input.value = 'DETACHED_VALUE';
            submit();
            form.remove();
            toast.textContent = 'NEW_PAGE';
            respond(true);
            await tick(); await tick();
            check(toast.textContent === 'NEW_PAGE' && input.value === 'DETACHED_VALUE', module + ': stale success changed UI');
            document.body.append(form);
            submit();
            form.remove();
            respond(false, 500);
            await tick(); await tick();
            check(toast.textContent === 'NEW_PAGE', module + ': stale failure displayed feedback');
            results.push(module + '/' + page + ': single submit, validation/network retry, reset and obsolete responses');
        }
        return results;
    }
    finally
    { window.fetch = originalFetch; toast.remove(); }
}
