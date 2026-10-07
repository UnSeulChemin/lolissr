export async function runBrowserScenario()
{
    const {initCreatePage} = await import('./js/manga/pages/create.js');
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    for (const restored of [true, false])
    {
        const form = document.createElement('form');
        form.className = 'form-layout';
        form.dataset.formPage = 'ajouter';
        form.dataset.restored = String(restored);
        form.innerHTML = `<input data-slug-source name="livre" value="Existing series">
            <input data-slug-target name="slug" value="${restored ? 'custom-slug' : ''}">
            <input name="editeur" value=""><input name="numero" value="">
            <select name="statut"><option value="en_cours">En cours</option><option value="termine">Termine</option></select>
            <datalist id="manga-existing-series"><option value="Existing series" data-slug="catalog-slug" data-editeur="Publisher" data-numero="8" data-statut="termine"></option></datalist>`;
        document.body.append(form);
        try
        {
            initCreatePage();
            form.querySelector('[data-slug-source]').dispatchEvent(new Event('input'));
            check(form.elements.slug.value === (restored ? 'custom-slug' : 'catalog-slug'), 'Slug restoration/autofill failed');
            check(form.elements.statut.value === (restored ? 'en_cours' : 'termine'), 'Status restoration/autofill failed');
            check(form.elements.editeur.value === (restored ? '' : 'Publisher'), 'Publisher restoration/autofill failed');
            check(form.elements.numero.value === (restored ? '' : '8'), 'Number restoration/autofill failed');
            if (!restored)
            {
                const originalFetch = window.fetch;
                const tick = () => new Promise(resolve => setTimeout(resolve, 0));
                let complete;
                window.fetch = () => new Promise(resolve =>
                { complete = resolve; });
                const submit = () => form.dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
                const respond = success => complete(new Response(JSON.stringify({success, message: 'Fixture'}),
                    {headers: {'Content-Type': 'application/json'}}));
                try
                {
                    submit();
                    form.elements.numero.value = '90';
                    respond(true);
                    await tick(); await tick();
                    form.querySelector('[data-slug-source]').dispatchEvent(new Event('input'));
                    check(form.elements.numero.value === '9', 'Next volume did not follow submitted volume after success');
                    submit();
                    respond(false);
                    await tick(); await tick();
                    check(form.querySelector('option[data-numero]').dataset.numero === '9', 'Failed creation advanced next volume');
                }
                finally
                { window.fetch = originalFetch; }
            }
        }
        finally
        { form.remove(); }
    }
    return ['restored custom slug and status preserved', 'restored empty fields preserved', 'fresh series autofill retained', 'next volume refreshed only after successful creation'];
}
