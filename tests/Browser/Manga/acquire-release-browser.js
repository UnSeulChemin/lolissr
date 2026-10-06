export async function runBrowserScenario()
{
    const {initAcquireRelease} = await import('./js/manga/actions/acquire-release.js');
    const {runCleanup} = await import('./js/router/lifecycle/cleanup.js');
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    const tick = () => new Promise(resolve => setTimeout(resolve, 0));
    const form = document.createElement('form');
    form.className = 'js-acquire-release';
    form.action = new URL('manga/series/fixture/posseder/2', location.href).href;
    form.innerHTML = '<button type="submit">Je possède ce tome</button>';
    document.body.append(form);
    const button = form.querySelector('button');
    const originalFetch = window.fetch;
    let requests = 0;
    let complete;
    window.fetch = () =>
    {
        requests++;
        return new Promise(resolve =>
        { complete = resolve; });
    };
    const submit = () => form.dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
    try
    {
        initAcquireRelease();
        submit();
        check(button.disabled && requests === 0, 'Confirmation must precede AJAX');
        submit();
        check(document.querySelectorAll('.confirm-modal-overlay').length === 1, 'Double submit created multiple confirmations');
        document.querySelector('.confirm-modal-secondary').click();
        await tick();
        check(!button.disabled && requests === 0, 'Cancel sent a request or kept button disabled');
        submit();
        document.querySelector('.confirm-modal-primary').click();
        await tick();
        check(requests === 1 && button.disabled, 'Confirmation did not send exactly one AJAX request');
        submit();
        check(requests === 1, 'Repeated submit sent duplicate AJAX');
        complete(new Response(JSON.stringify({success: false, message: 'Tome indisponible'}), {status: 422, headers: {'Content-Type': 'application/json'}}));
        await tick(); await tick();
        check(!button.disabled && button.textContent === 'Je possède ce tome', 'Failure did not restore button');
        runCleanup();
        initAcquireRelease();
        submit();
        check(document.querySelectorAll('.confirm-modal-overlay').length === 1, 'SPA reinitialization duplicated handlers');
        runCleanup();
        document.querySelector('.confirm-modal-primary').click();
        await tick();
        check(requests === 1, 'Leaving page during confirmation submitted stale form');
        return ['cancel without POST', 'single confirmation and AJAX request', 'error restores control', 'SPA cleanup prevents stale submission'];
    }
    finally
    {
        runCleanup();
        window.fetch = originalFetch;
        form.remove();
        document.querySelector('.confirm-modal-secondary')?.click();
    }
}
