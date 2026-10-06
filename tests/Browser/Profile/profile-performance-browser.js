export async function runBrowserScenario()
{
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    for (const type of ['avatar', 'banner', 'frame', 'title'])
    {
        const module = await import(`./js/profile/modals/${type}-modal.js`);
        const items = [
            {[type]: 'locked', [type + '_extension']: 'webp', unlocked: false, requirement: 'Locked'},
            {[type]: 'chosen', [type + '_extension']: 'webp', unlocked: true, requirement: 'Available', style: ''}
        ];
        const result = module[type + 'Modal'](items, '');
        const overlay = document.querySelector('.confirm-modal-overlay');
        overlay.querySelector(`.${type}-modal-item:disabled`).dispatchEvent(new MouseEvent('click', {bubbles: true}));
        check(overlay.isConnected, 'Locked choice selected');
        const button = overlay.querySelector(`.${type}-modal-item:not(:disabled)`);
        (button.querySelector('span') ?? button).dispatchEvent(new MouseEvent('click', {bubbles: true}));
        check(await result === 'chosen', 'Delegated selection failed: ' + type);
        check(!overlay.isConnected, 'Modal not cleaned: ' + type);
    }
    const {inFlight} = await import('./js/router/prefetch/prefetch-state.js');
    const {normalizeCacheKey} = await import('./js/core/navigation.js');
    const {resolvePage} = await import('./js/router/navigation/resolve-page.js');
    const key = normalizeCacheKey(new URL('shared-prefetch', location.href).href);
    let finish;
    const sharedController = new AbortController();
    const promise = new Promise(resolve =>
    {finish = resolve;});
    inFlight.set(key, {promise, controller: sharedController});
    const navigation = new AbortController();
    try
    {
        const waiting = resolvePage(key, false, navigation.signal);
        navigation.abort();
        const aborted = await Promise.race([
            waiting.then(() => false, error => error.name === 'AbortError'),
            new Promise(resolve => setTimeout(() => resolve(false), 100))
        ]);
        check(aborted, 'Navigation waited after abort');
        check(!sharedController.signal.aborted, 'Shared prefetch aborted');
        finish({type: 'page', page: {html: '<main>Shared</main>'}});
        const next = await resolvePage(key, false, new AbortController().signal);
        check(next.page.html.includes('Shared'), 'Shared response lost');
    }
    finally
    {
        finish(null);
        inFlight.delete(key);
    }
    return ['delegated selection for all four modals', 'locked choices ignored and overlays cleaned', 'immediate navigation cancellation', 'shared prefetch preserved'];
}
