export async function runBrowserScenario()
{
    const {initRouter} = await import('./js/router/router.js');
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    const originalFetch = window.fetch;
    const fixture = document.createElement('div');
    fixture.innerHTML = '<a href="deconnexion" data-confirm-logout>Logout</a><div id="toast"></div>';
    document.body.append(fixture);
    const link = fixture.querySelector('a');
    const toast = fixture.querySelector('#toast');
    let requests = 0;
    let rejectRequest;
    const until = async predicate =>
    {
        for (let i = 0; i < 100; i++)
        {
            if (predicate()) return;
            await new Promise(resolve => setTimeout(resolve, 10));
        }
        throw new Error('Logout timed out');
    };
    try
    {
        initRouter();
        window.fetch = () =>
        {
            requests++;
            return new Promise((resolve, reject) =>
            { rejectRequest = reject; });
        };
        link.click();
        const firstModal = document.querySelector('.confirm-modal-overlay');
        link.click();
        check(document.querySelector('.confirm-modal-overlay') === firstModal, 'Double click replaced confirmation');
        document.querySelector('.confirm-modal-secondary').click();
        await new Promise(resolve => setTimeout(resolve, 0));
        check(requests === 0, 'Cancellation sent logout request');
        link.click();
        document.querySelector('.confirm-modal-primary').click();
        await until(() => requests === 1);
        link.click();
        check(!document.querySelector('.confirm-modal-overlay') && requests === 1, 'Pending logout allowed another confirmation');
        rejectRequest(new TypeError('Offline'));
        await until(() => toast.classList.contains('toast-error'));
        toast.className = '';
        window.fetch = async () =>
        {
            requests++;
            return new Response(JSON.stringify({success: false}), {headers: {'Content-Type': 'application/json'}});
        };
        link.click();
        check(document.querySelector('.confirm-modal-overlay'), 'Failure blocked retry');
        document.querySelector('.confirm-modal-primary').click();
        await until(() => toast.classList.contains('toast-error'));
        check(requests === 2, 'Retry request missing');
        return ['Logout cancellation sends no request', 'Repeated clicks cannot duplicate logout', 'Network and unsuccessful responses show an error and permit retry'];
    }
    finally
    {
        window.fetch = originalFetch;
        fixture.remove();
    }
}
