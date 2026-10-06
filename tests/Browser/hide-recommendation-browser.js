export async function runBrowserScenario()
{
    const {initHideRecommendation} = await import('./js/manga/actions/hide-recommendation.js');
    const {runCleanup} = await import('./js/router/lifecycle/cleanup.js');
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    const tick = () => new Promise(resolve => setTimeout(resolve, 0));
    const grid = document.createElement('section');
    grid.className = 'collection-grid';
    grid.innerHTML = [1, 2].map(rank => `<div class="collection-release-item"><span class="recommendation-rank">${rank}</span><form class="js-hide-recommendation" action="${location.href}"><button type="submit">Masquer</button></form></div>`).join('');
    document.body.append(grid);
    const originalFetch = window.fetch;
    let requests = 0;
    let complete;
    window.fetch = () =>
    { requests++; return new Promise(resolve =>
    { complete = resolve; }); };
    const submit = form => form.dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
    try
    {
        initHideRecommendation();
        const first = grid.querySelector('form');
        submit(first); submit(first);
        check(requests === 0 && document.querySelectorAll('.confirm-modal-overlay').length === 1, 'Confirmation did not precede AJAX');
        document.querySelector('.confirm-modal-secondary').click();
        await tick();
        check(requests === 0 && !first.querySelector('button').disabled, 'Cancel sent a request');
        submit(first);
        document.querySelector('.confirm-modal-primary').click();
        await tick();
        check(requests === 1 && first.querySelector('button').disabled, 'Confirm did not send one request');
        complete(new Response(JSON.stringify({success: true, message: 'Masquée'}), {headers: {'Content-Type': 'application/json'}}));
        await tick(); await tick();
        check(grid.children.length === 1 && grid.querySelector('.recommendation-rank').textContent === '1', 'Hide did not remove card and renumber ranks');
        const second = grid.querySelector('form');
        submit(second);
        document.querySelector('.confirm-modal-primary').click();
        await tick();
        complete(new Response(JSON.stringify({success: false, message: 'Erreur'}), {status: 500, headers: {'Content-Type': 'application/json'}}));
        await tick(); await tick();
        check(grid.children.length === 1 && !second.querySelector('button').disabled, 'Error removed card or left control disabled');
        const favorite = document.createElement('form');
        favorite.className = 'js-favorite-recommendation';
        favorite.action = `${location.origin}/favorites-test/favoris`;
        favorite.innerHTML = '<button type="submit">Ajouter</button>';
        grid.firstElementChild.append(favorite);
        submit(favorite); submit(favorite);
        check(requests === 3, 'Favorite double submit sent duplicate requests');
        complete(new Response(JSON.stringify({success: true, message: 'Saved'}), {headers: {'Content-Type': 'application/json'}}));
        await tick(); await tick();
        check(favorite.action.endsWith('/retirer') && favorite.querySelector('button').getAttribute('aria-pressed') === 'true', 'Favorite add did not update control');
        favorite.dataset.favoritesPage = 'true';
        submit(favorite);
        complete(new Response(JSON.stringify({success: true, message: 'Removed'}), {headers: {'Content-Type': 'application/json'}}));
        await tick(); await tick();
        check(!grid.isConnected, 'Favorite removal did not empty wishlist');
        runCleanup();
        submit(second);
        check(requests === 4, 'SPA cleanup left a live submit handler');
        return ['one AJAX request', 'card removal and ranking', 'error preserves card', 'favorite add and duplicate prevention', 'favorite removal and empty wishlist', 'SPA cleanup'];
    }
    finally
    {
        runCleanup();
        window.fetch = originalFetch;
        grid.remove();
    }
}
