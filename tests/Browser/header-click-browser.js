export async function runBrowserScenario()
{
    const base = new URL('.', location.href).pathname;
    window.appConfig = {baseUri: base};
    const check = (ok, message) => { if (!ok) throw new Error(message); };
    const header = document.createElement('header');
    header.innerHTML = `<nav><ul><li><a class="nav-link-icon" href="${base}manga">M</a></li><li><a class="nav-link-icon" href="${base}figurine">F</a></li><li><a class="nav-link-icon" href="${base}peluche">P</a></li></ul></nav>`;
    document.body.prepend(header);
    const links = [...header.querySelectorAll('a')];
    links.forEach(link => { link.style.transition = 'none'; });
    const rect = links[0].getBoundingClientRect();
    links[0].classList.add('active');
    const active = links[0].getBoundingClientRect();
    check(Math.abs(rect.x - active.x) < 0.1 && Math.abs(rect.y - active.y) < 0.1
        && Math.abs(rect.width - active.width) < 0.1 && Math.abs(rect.height - active.height) < 0.1,
        'Active header link moves or resizes its clickable area');
    links[0].classList.remove('active');
    const main = document.createElement('main');
    main.className = 'app-content';
    document.body.append(main);
    main.style.minHeight = '0';
    const shortPage = links[0].getBoundingClientRect();
    main.style.height = '200vh';
    const longPage = links[0].getBoundingClientRect();
    check(Math.abs(shortPage.x - longPage.x) < 0.1, 'Scrollbar appearance moves the header links');
    main.style.height = '';
    const {initRouter} = await import('./js/router/router.js');
    const {navigationState} = await import('./js/router/router-state.js');
    history.replaceState({}, '', base);
    initRouter();
    const original = window.fetch;
    const pending = [];
    window.fetch = url => new Promise(resolve => pending.push(() => resolve(new Response(JSON.stringify({
        type: 'page', page: {html: `<p>${new URL(url).pathname}</p>`, format: 'fragment', stylesheets: []},
    }), {headers: {'Content-Type': 'application/json'}}))));
    try
    {
        links.forEach(link => link.click());
        const ready = new Promise(resolve => document.addEventListener('navigation:ready', resolve, {once: true}));
        pending[2]();
        await ready;
        pending[1]();
        pending[0]();
        await new Promise(resolve => setTimeout(resolve, 0));
        check(location.pathname === base + 'peluche' && main.textContent === base + 'peluche'
            && links[2].classList.contains('active') && !navigationState.locked,
            'Rapid header clicks did not keep the last destination');
    }
    finally { window.fetch = original; }
    return ['Header clickable area stays fixed when active', 'Header stays fixed between short and long pages', 'Rapid header clicks keep the last destination despite late responses'];
}
