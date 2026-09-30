export async function testPageStyles()
{
    const base = new URL('.', location.href).pathname;
    window.appConfig = {baseUri: base};
    const {navigateTo} = await import('./js/router/router-navigation.js');
    const {navigationState} = await import('./js/router/router-state.js');
    const {initNavigationLoading} = await import('./js/router/ui/navigation-loading.js');
    const main = document.createElement('main');
    main.className = 'app-content';
    main.textContent = 'Original';
    document.body.append(main);
    history.replaceState({}, '', base + 'manga');
    initNavigationLoading();
    const original = window.fetch;
    const pending = [];
    window.fetch = () => new Promise(resolve => pending.push(() => resolve(new Response(JSON.stringify({
        type: 'page', page: {html: '<p>Destination</p>', format: 'fragment', title: 'Destination', stylesheets: [], bodyData: {}},
    }), {headers: {'Content-Type': 'application/json'}}))));
    const check = (ok, text) => { if (!ok) throw new Error(text); };
    try
    {
        const first = navigateTo(base + 'figurine', {fallback: false});
        const controller = navigationState.controller;
        await navigateTo(base + 'manga');
        check(controller.signal.aborted && !navigationState.locked && !navigationState.controller, 'Current-page click did not cancel immediately');
        const second = navigateTo(base + 'chinois', {fallback: false});
        await new Promise(resolve => setTimeout(resolve, 100));
        pending[0](); await first;
        check(location.pathname.endsWith('/manga') && main.textContent === 'Original', 'Cancelled response rendered');
        check(navigationState.locked && document.body.classList.contains('is-routing'), 'Old response disrupted newer navigation');
        pending[1](); await second;
        check(location.pathname.endsWith('/chinois') && !navigationState.locked && !document.body.classList.contains('is-routing'), 'Next navigation failed');
        return ['Current-page click cancels pending navigation', 'Late cancelled response cannot render or stop newer loading', 'Following navigation succeeds'];
    }
    finally { window.fetch = original; }
}
