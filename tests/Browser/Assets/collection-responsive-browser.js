export async function runBrowserScenario()
{
    const iframe = document.createElement('iframe');
    iframe.style.border = '0';
    iframe.style.height = '700px';
    document.body.append(iframe);
    try
    {
        const loaded = new Promise(resolve => iframe.addEventListener('load', resolve, {once: true}));
        iframe.srcdoc = `<link rel="stylesheet" href="${new URL('css/app.bundle.css', location.href).href}">
            <div class="collection-grid u-grid">${Array.from({length: 8}, () => '<a class="collection-card-link card"><div style="width:180px;height:240px">Cover</div><span>Fixture collection</span></a>').join('')}</div>`;
        await loaded;
        const results = [];
        for (const [width, expected] of [[320, 1], [375, 1], [768, 2], [1280, 4]])
        {
            iframe.style.width = width + 'px';
            await new Promise(resolve => setTimeout(resolve, 0));
            const doc = iframe.contentDocument;
            const grid = doc.querySelector('.collection-grid');
            const columns = iframe.contentWindow.getComputedStyle(grid).gridTemplateColumns.split(' ').filter(value => parseFloat(value) > 0);
            if (columns.length !== expected) throw new Error(`Wrong column count at ${width}: ${columns.length}`);
            if (doc.documentElement.scrollWidth > width) throw new Error(`Horizontal overflow at ${width}`);
            for (const card of grid.children)
            {
                const rect = card.getBoundingClientRect();
                if (rect.left < 0 || rect.right > width) throw new Error(`Card clipped at ${width}`);
            }
            results.push(`${width}px: ${expected} columns, cards visible without horizontal overflow`);
        }
        return results;
    }
    finally
    { iframe.remove(); }
}
