export async function runBrowserScenario()
{
    const {initProfileCustomization} = await import('./js/profile/pages/customization.js');
    const {runCleanup} = await import('./js/router/lifecycle/cleanup.js');
    const originalFetch = window.fetch;
    const originalConfig = window.appConfig;
    window.appConfig = {...originalConfig, profileImageVersion: 'fixture-current-version'};
    const root = document.createElement('div');
    root.innerHTML = '<button class="js-profile-avatar">Avatar</button><button class="js-profile-banner">Banner</button><button class="js-profile-frame">Frame</button><div class="profile-customization-avatar"><img><img class="profile-frame"></div><div class="profile-customization-banner"><img></div><img class="site-profile-avatar"><img class="site-profile-frame">';
    document.body.append(root);
    let requests = 0;
    window.fetch = (url, options) =>
    {
        requests++;
        const match = String(url).match(/\/(?:update-)?(avatars?|banners?|frames?)$/);
        const type = match[1].replace(/s$/, '');
        const data = options.method === 'POST' ? {[type]: 'chosen', [type + '_extension']: 'webp'}
            : {[type + 's']: [{[type]: 'chosen', [type + '_extension']: 'webp', unlocked: true, requirement: 'Available'}]};
        return Promise.resolve(new Response(JSON.stringify({success: true, data}), {headers: {'Content-Type': 'application/json'}}));
    };
    const tick = () => new Promise(resolve => setTimeout(resolve, 10));
    const results = [];
    try
    {
        initProfileCustomization();
        for (const [type, selector] of [['avatar', '.profile-customization-avatar img:not(.profile-frame), .site-profile-avatar'],
            ['banner', '.profile-customization-banner img'], ['frame', '.profile-frame, .site-profile-frame']])
        {
            root.querySelector('.js-profile-' + type).click();
            let choice;
            for (let attempt = 0; attempt < 100; attempt++)
            {
                choice = document.querySelector('.' + type + '-modal-item');
                if (choice) break;
                await tick();
            }
            if (!choice) throw new Error(type + ': modal did not open');
            const preview = choice.querySelector('img')?.src;
            choice.click();
            await tick(); await tick();
            for (const image of root.querySelectorAll(selector))
            {
                const url = new URL(image.src);
                if (url.searchParams.get('v') !== 'fixture-current-version') throw new Error(type + ': stale version');
                if (!url.pathname.endsWith('/chosen.webp')) throw new Error(type + ': wrong selected image');
                if (type !== 'frame' && preview !== image.src) throw new Error(type + ': preview and selected image differ');
            }
            results.push(type + ': selected image retains current cache version');
        }
        if (requests !== 6) throw new Error('Unexpected duplicate requests');
        return results;
    }
    finally
    {
        runCleanup();
        window.fetch = originalFetch;
        window.appConfig = originalConfig;
        root.remove();
    }
}
