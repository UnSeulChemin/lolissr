export async function runBrowserScenario()
{
    const {runCleanup} = await import('./js/router/lifecycle/cleanup.js');
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    const tick = () => new Promise(resolve => setTimeout(resolve, 0));
    const originalFetch = window.fetch;
    const toast = document.createElement('div');
    toast.id = 'toast';
    document.body.append(toast);
    const results = [];
    let requests = [];
    window.fetch = (url, options) => new Promise(resolve => requests.push({url, options, resolve}));
    const respond = (request, success) => request.resolve(new Response(JSON.stringify({success, message: 'Fixture response', data: {redirect: '/obsolete'}}),
        {status: success ? 200 : 422, headers: {'Content-Type': 'application/json'}}));
    try
    {
        for (const [module, initializer, selector, cardClass] of [
            ['manga/delete-manga', 'initDeleteManga', 'js-delete-manga', ''],
            ['manga/delete-artbook', 'initDeleteArtbook', 'js-delete-artbook', ''],
            ['figurine/delete-figurine', 'initDeleteFigurine', 'js-delete-figurine', ''],
            ['nendoroid/delete-nendoroid', 'initDeleteNendoroid', 'js-delete-nendoroid', ''],
            ['peluche/delete-peluche', 'initDeletePeluche', 'js-delete-peluche', ''],
            ['chinois/delete-vocabulary', 'initDeleteVocabulary', 'vocabulaire-delete', 'chinois-vocab-card'],
            ['chinois/delete-grammar', 'initDeleteGrammar', 'grammaire-delete', 'grammar-item']
        ])
        {
            (await import(`./js/${module.split('/')[0]}/actions/${module.split('/')[1]}.js`))[initializer]();
            const card = document.createElement('div');
            card.className = cardClass;
            const button = document.createElement('button');
            button.className = selector;
            button.textContent = 'Supprimer';
            button.dataset.id = '1';
            button.dataset.url = '/fixture-delete';
            card.append(button);
            document.body.append(card);
            button.click(); button.click();
            check(button.disabled && document.querySelectorAll('.confirm-modal-overlay').length === 1, module + ': duplicate confirmation');
            document.querySelector('.confirm-modal-secondary').click();
            await tick();
            check(!button.disabled && requests.length === 0, module + ': cancellation failed');
            check(document.activeElement === button, module + ': cancellation lost focus');
            button.click(); runCleanup();
            await tick();
            check(!button.disabled && !document.querySelector('.confirm-modal-overlay') && requests.length === 0, module + ': stale confirmation');
            button.click();
            document.querySelector('.confirm-modal-danger').click();
            await tick();
            check(requests.length === 1, module + ': missing or duplicate POST');
            button.click();
            check(requests.length === 1, module + ': repeated POST');
            respond(requests.shift(), false);
            await tick(); await tick();
            check(!button.disabled && button.textContent === 'Supprimer', module + ': error did not restore control');
            toast.textContent = 'CURRENT_PAGE';
            button.click();
            document.querySelector('.confirm-modal-danger').click();
            await tick();
            card.remove();
            const before = location.href;
            respond(requests.shift(), true);
            await tick(); await tick();
            check(requests.length === 0 && location.href === before && toast.textContent === 'CURRENT_PAGE', module + ': late response affected new page');
            results.push(module + ': double click, cancellation, retry and stale response');
        }
        const level = document.createElement('span');
        level.className = 'js-user-level';
        level.textContent = 'CURRENT_LEVEL';
        document.body.append(level);
        for (const [module, initializer, selector] of [
            ['manga/update-read-status', 'initUpdateReadStatus', 'js-read-status-button'],
            ['figurine/update-collect-status', 'initUpdateCollectStatus', 'js-figurine-collect-status-button'],
            ['nendoroid/update-collect-status', 'initUpdateNendoroidCollectStatus', 'js-nendoroid-collect-status-button'],
            ['peluche/update-collect-status', 'initUpdatePelucheCollectStatus', 'js-peluche-collect-status-button'],
            ['chinois/toggle-vocabulary-mastery', 'initToggleVocabularyMastery', 'vocabulary-ajax'],
            ['chinois/toggle-grammar-mastery', 'initToggleGrammarMastery', 'grammar-ajax']
        ])
        {
            (await import(`./js/${module.split('/')[0]}/actions/${module.split('/')[1]}.js`))[initializer]();
            const button = document.createElement('button');
            button.className = selector;
            button.dataset.url = '/fixture-status';
            document.body.append(button);
            button.click();
            requests.shift().resolve(new Response(JSON.stringify({success: true, data: {level: 2, maitrise: true, readStatus: 1, collectStatus: 1}}),
                {headers: {'Content-Type': 'application/json'}}));
            await tick(); await tick();
            check(level.textContent === '2', module + ': successful status did not refresh header level');
            level.textContent = 'CURRENT_LEVEL';
            button.click(); button.click();
            check(requests.length === 1, module + ': duplicate status request');
            button.remove();
            toast.textContent = 'CURRENT_PAGE';
            requests.shift().resolve(new Response(JSON.stringify({success: true, message: 'OLD_STATUS', data: {level: 999, maitrise: 1}}),
                {headers: {'Content-Type': 'application/json'}}));
            await tick(); await tick();
            check(level.textContent === 'CURRENT_LEVEL' && toast.textContent === 'CURRENT_PAGE', module + ': stale status changed new page');
        }
        level.remove();
        results.push('six status actions reject double clicks and obsolete feedback');
        const {initUpdateNote} = await import('./js/manga/actions/update-note.js');
        initUpdateNote();
        const makeCard = slug =>
        {
            const card = document.createElement('div');
            card.className = 'js-detail-card';
            Object.assign(card.dataset, {basePath: '/', slug, numero: '1', jacquette: '0', livreNote: '0'});
            card.innerHTML = '<div class="js-note-group" data-field="jacquette"><button class="js-note-button" data-value="3">3</button></div><span id="js-note-total"></span>';
            document.body.append(card);
            return card;
        };
        const first = makeCard('first');
        document.dispatchEvent(new Event('router:loaded'));
        check(first.querySelector('#js-note-total').textContent === '0/10'
            && !first.querySelector('.active'), 'Absent notes must stay unselected with a zero total');
        first.querySelector('button').click();
        const oldRequest = requests.shift();
        check(oldRequest !== undefined, 'First note request missing');
        check(JSON.parse(oldRequest.options.body).livre_note === null, 'Saving the cover invented a book rating');
        first.remove();
        const second = makeCard('second');
        second.querySelector('button').click();
        const newRequest = requests.shift();
        check(newRequest !== undefined && String(newRequest.url).includes('/second/'), 'Old note request blocked new page');
        toast.textContent = 'CURRENT_PAGE';
        respond(oldRequest, true);
        await tick(); await tick();
        check(second.querySelector('button').disabled && toast.textContent === 'CURRENT_PAGE', 'Old note completion unlocked new save or displayed feedback');
        newRequest.resolve(new Response(JSON.stringify({success: true, data: {notes: {jacquette: 3, livreNote: 0, note: 3}}}),
            {headers: {'Content-Type': 'application/json'}}));
        await tick(); await tick();
        check(!second.querySelector('button').disabled, 'New save remained locked');
        check(second.dataset.livreNote === '0' && second.querySelector('#js-note-total').textContent === '3/10',
            'Partial note response invented a book rating or changed the total');
        second.dataset.jacquette = '1';
        second.querySelector('button').click();
        requests.shift().resolve(new Response(JSON.stringify({success: false, message: 'Note rejected'}),
            {headers: {'Content-Type': 'application/json'}}));
        await tick(); await tick();
        check(second.dataset.jacquette === '1' && !second.querySelector('button').disabled, 'Logical note failure did not roll back and unlock');
        check(toast.textContent.includes('Note rejected'), 'Logical note failure displayed success');
        second.remove();
        results.push('independent note saves across navigation');
        return results;
    }
    finally
    {
        runCleanup();
        window.fetch = originalFetch;
        toast.remove();
    }
}
