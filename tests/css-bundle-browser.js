export async function testPageStyles()
{
    const read = async (path) =>
    {
        const response = await fetch(path, {cache: 'no-store'});
        if (!response.ok) throw new Error(`Cannot read ${path}: ${response.status}`);
        return response.text();
    };
    const source = await read('css/app.css');
    const matches = [...source.matchAll(/@import\s+url\(['"]\.\/([^'"]+)['"]\);/g)];
    let expanded = source;
    for (const match of matches)
    {
        expanded = expanded.replace(match[0], await read(`css/${match[1]}`));
    }
    const rules = (css) =>
    {
        const sheet = new CSSStyleSheet();
        sheet.replaceSync(css.replace(/@import\s+url\([^)]+\);/g, ''));
        return [...sheet.cssRules].map(rule => rule.cssText);
    };
    const originalRules = rules(expanded);
    const bundledRules = rules(await read('css/app.bundle.css'));
    if (JSON.stringify(originalRules) !== JSON.stringify(bundledRules))
    {
        const index = originalRules.findIndex((rule, i) => rule !== bundledRules[i]);
        throw new Error(`CSS bundle changes browser-parsed rule ${index}`);
    }
    return [`${matches.length} local imports bundled; ${originalRules.length} browser-parsed CSS rules identical`];
}
