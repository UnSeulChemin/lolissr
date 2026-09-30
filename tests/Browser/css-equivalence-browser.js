// Compare every computed property against a CSS snapshot supplied to the runner.
export async function testPageStyles()
{
    const {before, after, cases} = window.cssComparison;
    const expand = (files, path) => files[path].replace(/@import\s+url\(["']([^"']+)["']\);/g, (_, href) =>
    {
        if (href.startsWith('https:')) return '';
        const resolved = new URL(href, `https://css.test/${path}`).pathname.slice(1);
        return expand(files, resolved);
    });
    const cssText = (files, paths) => paths.map(path => expand(files, path)).join('\n').replaceAll(':hover', '.test-hover');
    const markup = `
        <section class="home-grid home-grid-top card-grid-3"><article class="card"><h2 class="home-card-title">Résumé</h2></article></section>
        <h2 class="home-section-title">Collection</h2>
        <article class="detail-card"><div class="detail-image"><div class="detail-image-inner"><img alt=""></div></div>
        <div class="detail-content"><h1 class="detail-title">Exemple</h1><div class="detail-row"><span class="detail-label">Titre</span><span class="detail-value">Valeur</span></div>
        <div class="detail-actions"><div class="detail-actions-left">
        ${['js-read-status-button','js-collect-status-button'].map(type => ['', 'active', 'test-hover', 'active test-hover', 'disabled'].map(state => `
            <button class="status-toggle ${type} ${state}" ${state === 'disabled' ? 'disabled' : ''}>
            <svg class="status-toggle-icon ${type.includes('read') ? 'status-toggle-icon--read lu-icon' : 'collect-icon'}" viewBox="0 0 24 24"><path d="M2 2L20 20"></path></svg></button>`).join('')).join('')}
        </div><div class="detail-actions-right"><button class="form-submit">Modifier</button></div></div></div></article>
        <section class="profile-page"><div class="profile-avatar"><img class="profile-avatar-image" alt=""><img class="profile-frame" alt=""></div></section>
        <section class="profile-customization"><div class="profile-customization-avatar"><img class="profile-avatar-image" alt=""><img class="profile-frame" alt=""></div>
        <div class="profile-customization-grid"><div class="card profile-customization-card">Avatar</div></div></section>
        <div class="media-picker-grid media-picker-grid--avatars avatar-modal-grid">${['', 'test-hover'].map(state => `<div class="media-picker-item media-picker-item--avatar avatar-modal-item ${state}"><img alt=""></div>`).join('')}
        <div class="media-picker-item media-picker-item--avatar avatar-modal-item frame-modal-item test-hover"><div class="profile-customization-avatar"><img class="profile-avatar-image" alt=""><img class="profile-frame" alt=""></div></div></div>
        <div class="media-picker-grid banner-modal-grid">${['', 'test-hover'].map(state => `<div class="media-picker-item media-picker-item--banner banner-modal-item ${state}"><img alt=""></div>`).join('')}</div>
        <form class="form"><input class="form-input" value="Test"><button class="form-submit">Enregistrer</button></form>`;

    const frame = async (css, width) =>
    {
        const element = document.createElement('iframe');
        element.style.cssText = `width:${width}px;height:900px;border:0;`;
        const ready = new Promise(resolve => element.onload = resolve);
        element.srcdoc = '<!doctype html><html><head><style>' + css + '\n*,*::before,*::after{animation:none!important;transition:none!important;}</style></head><body>' + markup + '</body></html>';
        document.body.append(element);
        await ready;
        return element;
    };
    const results = [];
    for (const scenario of cases)
    {
        for (const width of [420, 768, 1440])
        {
            const oldFrame = await frame(cssText(before, scenario.before), width);
            const newFrame = await frame(cssText(after, scenario.after), width);
            for (const iframe of [oldFrame, newFrame])
            {
                const unused = scenario.name === 'manga' ? '.js-collect-status-button'
                    : scenario.name === 'collection' ? '.js-read-status-button' : null;
                if (unused) iframe.contentDocument.querySelectorAll(unused).forEach(node => node.remove());
            }
            const oldNodes = oldFrame.contentDocument.body.querySelectorAll('*');
            const newNodes = newFrame.contentDocument.body.querySelectorAll('*');
            for (let index = 0; index < oldNodes.length; index++)
            {
                for (const pseudo of [null, '::before', '::after'])
                {
                    const oldStyle = oldFrame.contentWindow.getComputedStyle(oldNodes[index], pseudo);
                    const newStyle = newFrame.contentWindow.getComputedStyle(newNodes[index], pseudo);
                    for (const property of oldStyle)
                    {
                        if (oldStyle.getPropertyValue(property) !== newStyle.getPropertyValue(property))
                        {
                            throw new Error(`${scenario.name} ${width}px ${oldNodes[index].className.baseVal ?? oldNodes[index].className} ${pseudo ?? ''} ${property}: ${oldStyle.getPropertyValue(property)} != ${newStyle.getPropertyValue(property)}`);
                        }
                    }
                }
            }
            results.push(`${scenario.name} ${width}px: identical computed styles (${oldNodes.length} elements and pseudo-elements)`);
            oldFrame.remove();
            newFrame.remove();
        }
    }
    return results;
}
