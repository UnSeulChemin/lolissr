export async function runBrowserScenario()
{
    const {request} = await import('./js/core/http.js');
    const originalFetch = window.fetch;
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    window.fetch = (_, options) => Promise.resolve({status: 200, ok: true, headers: new Headers({'Content-Type': 'application/json'}),
        json: () => new Promise((resolve, reject) => options.signal.addEventListener('abort',
            () => reject(new DOMException('Body aborted', 'AbortError')), {once: true}))});
    try
    {
        let failure;
        try
        { await request('/fixture-timeout', {timeout: 10}); }
        catch (error)
        { failure = error; }
        check(failure?.code === 'REQUEST_TIMEOUT' && failure.status === 408, 'Body timeout was treated as invalid JSON or silent cancellation');
        const controller = new AbortController();
        const pending = request('/fixture-cancel', {timeout: 1000, signal: controller.signal});
        await Promise.resolve(); await Promise.resolve();
        controller.abort();
        try
        { await pending; }
        catch (error)
        { failure = error; }
        check(failure?.name === 'AbortError', 'Explicit cancellation was reported as invalid JSON or timeout');
        window.fetch = () => Promise.resolve({status: 200, ok: true, headers: new Headers({'Content-Type': 'application/json'}),
            json: () => Promise.reject(new SyntaxError('Malformed JSON'))});
        try
        { await request('/fixture-json'); }
        catch (error)
        { failure = error; }
        check(failure?.code === 'INVALID_JSON', 'Malformed JSON stopped reporting INVALID_JSON');
        return ['response body timeout reports REQUEST_TIMEOUT', 'explicit body cancellation retains AbortError', 'malformed JSON remains distinct'];
    }
    finally
    { window.fetch = originalFetch; }
}
