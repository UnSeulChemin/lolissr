export async function runBrowserScenario()
{
    const base = new URL('.', location.href).pathname;
    window.appConfig = {baseUri: base};
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    const until = async predicate =>
    {
        for (let i = 0; i < 200; i++)
        { if (predicate()) return; await new Promise(resolve => setTimeout(resolve, 10)); }
        throw new Error('Bundle test timed out');
    };
    const main = document.createElement('main');
    main.className = 'app-content';
    main.innerHTML = '<button class="js-profile-avatar">Avatar</button>';
    const toast = document.createElement('div');
    toast.id = 'toast';
    document.body.append(main, toast);
    const link = document.createElement('a');
    link.href = base + 'manga';
    link.dataset.prefetch = '';
    link.textContent = 'Manga';
    document.body.append(link);
    const connection = {saveData: true};
    Object.defineProperty(navigator, 'connection', {configurable: true, value: connection});
    history.replaceState({}, '', base + 'profil/personnalisation');
    window.flashToast = {message: 'Bundle ready'};
    let avatars = 0;
    let pages = 0;
    const originalFetch = window.fetch;
    window.fetch = async (url, options) =>
    {
        const isAvatar = String(url).includes('/ajax/avatars');
        if (isAvatar) avatars++; else pages++;
        const profile = String(url).includes('personnalisation');
        return new Response(JSON.stringify(isAvatar
            ? {success: true, data: {avatars: [{avatar: 'default', avatar_extension: 'webp'}]}}
            : {success: true, type: 'page', page: {html: profile ? '<button class="js-profile-avatar">Avatar</button>' : '<p id="bundle-navigation">Loaded</p>', format: 'fragment', title: 'Bundle', lang: 'fr', bodyData: {}, stylesheets: []}}),
            {headers: {'Content-Type': 'application/json'}});
    };
    try
    {
        await import(new URL(base + window.browserTestData.entry, location.origin).href);
        await until(() => toast.textContent.includes('Bundle ready'));
        check(!window.flashToast, 'Boot did not consume flash');
        main.querySelector('button').click();
        await until(() => document.querySelector('.avatar-modal-item'));
        check(avatars === 1, 'Lazy profile module did not initialize exactly once');
        link.dispatchEvent(new PointerEvent('pointerenter'));
        await new Promise(resolve => setTimeout(resolve, 400));
        check(pages === 0, 'Data saver still prefetched a page');
        link.click();
        await until(() => main.querySelector('#bundle-navigation'));
        check(!document.querySelector('.confirm-modal-overlay'), 'Lazy module cleanup used a different router instance');
        check(pages === 1, 'Data saver prevented explicit navigation');
        link.href = base + 'profil/personnalisation';
        link.click();
        await until(() => main.querySelector('.js-profile-avatar') && !document.body.classList.contains('is-navigating'));
        await new Promise(resolve => setTimeout(resolve, 100));
        main.querySelector('button').click();
        await until(() => document.querySelector('.avatar-modal-item'));
        check(avatars === 2, 'Lazy profile module failed to reinitialize');
        document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape'}));
        connection.saveData = false;
        link.href = base + 'figurine';
        link.dispatchEvent(new PointerEvent('pointerenter'));
        await until(() => pages === 3);
        return ['production bundle boots', 'lazy profile module opens its modal', 'shared router cleanup and route reinitialization', 'saveData skips prefetch but permits navigation', 'prefetch resumes when saveData is disabled'];
    }
    finally
    { window.fetch = originalFetch; }
}
