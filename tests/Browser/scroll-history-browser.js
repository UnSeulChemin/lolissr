export async function testPageStyles()
{
    const base = new URL('.', location.href).pathname;
    window.appConfig = {baseUri: base};
    const {initRouter} = await import('./js/router/router.js');
    const {navigateTo} = await import('./js/router/router-navigation.js');
    const {navigationState} = await import('./js/router/router-state.js');
    const main = document.createElement('main');
    main.className = 'app-content';
    main.style.minHeight = '5000px';
    document.body.append(main);
    document.documentElement.style.scrollBehavior = 'auto';
    const originalFetch = window.fetch;
    const originalFrame = window.requestAnimationFrame;
    // Headless virtual time does not consistently advance compositor frames.
    window.requestAnimationFrame = callback => setTimeout(() => callback(performance.now()), 0);
    window.fetch = async () => new Response(JSON.stringify({success: true, type: 'page', page: {
        html: '<div style="height:5000px">Scroll fixture</div>', format: 'fragment',
        title: 'Scroll fixture', stylesheets: [], lang: 'fr', bodyData: {},
    }}), {headers: {'Content-Type': 'application/json'}});
    const frame = () => new Promise(resolve => requestAnimationFrame(resolve));
    const check = (ok, message) => { if (!ok) throw new Error(message); };
    const move = async (direction, expectedEntry) => {
        const event = new Promise(resolve => window.addEventListener('popstate', resolve, {once: true}));
        history[direction]();
        await event;
        for (let i = 0; i < 100 && navigationState.locked; i++) await new Promise(resolve => setTimeout(resolve, 10));
        await frame();
        await frame();
        check(!navigationState.locked && history.state.__appScrollEntry === expectedEntry, 'History traversal did not finish');
    };
    try
    {
        history.replaceState({fixture: true}, '', base + 'manga');
        initRouter();
        const first = history.state.__appScrollEntry;
        check(history.state.fixture === true, 'Existing history state lost');
        window.scrollTo(0, 300);
        await navigateTo(base + 'figurine');
        const second = history.state.__appScrollEntry;
        window.scrollTo(0, 700);
        await move('back', first);
        check(Math.abs(scrollY - 300) < 2, 'Back did not restore first page');
        await move('forward', second);
        check(Math.abs(scrollY - 700) < 2, 'Forward lost position of page left with Back');
        await navigateTo(base + 'manga');
        const third = history.state.__appScrollEntry;
        check(third !== first, 'Repeated URL reused history identity');
        window.scrollTo(0, 1100);
        await move('back', second);
        await move('back', first);
        check(Math.abs(scrollY - 300) < 2, 'Repeated URL overwrote original position');
        await move('forward', second);
        await move('forward', third);
        check(Math.abs(scrollY - 1100) < 2, 'Repeated URL lost its own position');
        return ['Back/Forward preserve scroll', 'Repeated URLs have independent positions', 'Existing history state preserved'];
    }
    finally { window.fetch = originalFetch; window.requestAnimationFrame = originalFrame; }
}
