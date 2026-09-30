// Called by an HTML fixture served from public/, with the real stylesheet loader.
export async function testPageStyles(preparePageStyles)
{
    const results = [];
    const css = file => new URL(`css/${file}`, location.href).href;
    const active = () => [...document.querySelectorAll('link[data-page-style]')];
    const check = (condition, label) =>
    {
        if (!condition) throw new Error(label);
        results.push(label);
    };
    const navigate = async files => (await preparePageStyles(files.map(css), new AbortController().signal))();

    await navigate(['components/summary.css']);
    check(active().length === 1 && active()[0].sheet !== null, 'Real CSS loaded');
    const home = active()[0];
    await navigate(['components/summary.css', 'page/sql.css']);
    check(active().length === 2 && active()[0] === home, 'Shared CSS reused without duplicate');
    await navigate(['components/detail.css', 'components/status-toggle.css']);
    check(active().length === 2 && !home.isConnected, 'Obsolete CSS removed');
    const detail = active()[0];
    await navigate(['components/detail.css', 'page/manga/note-rating.css', 'components/status-toggle.css']);
    check(active()[0] === detail && active().length === 3, 'Shared detail stylesheet reused');
    await navigate(['components/summary.css']);
    check(active().length === 1 && active()[0].href === css('components/summary.css'), 'Back navigation restores styles');

    const previous = active()[0];
    const canceled = new AbortController();
    const pending = preparePageStyles([css('page/profil/profil.css')], canceled.signal);
    canceled.abort();
    try { await pending; throw new Error('Canceled navigation resolved'); }
    catch (error) { if (error.name !== 'AbortError') throw error; }
    check(active()[0] === previous && document.querySelectorAll('link[media="not all"]').length === 0, 'Cancellation keeps current styles and removes pending links');

    const late = new AbortController();
    const commit = await preparePageStyles([css('page/sql.css')], late.signal);
    late.abort();
    try { commit(); throw new Error('Stale commit accepted'); }
    catch (error) { if (error.name !== 'AbortError') throw error; }
    check(active()[0] === previous, 'Cancellation after loading prevents stale commit');

    try { await preparePageStyles([css('missing-page-style.css')], new AbortController().signal); throw new Error('Missing CSS accepted'); }
    catch (error) { if (!error.message.includes('Unable to load stylesheet')) throw error; }
    check(active()[0] === previous, 'Load failure preserves current page styles');

    const a = new AbortController();
    const b = new AbortController();
    const first = preparePageStyles([css('page/chinois/grammaire.css')], a.signal);
    const second = preparePageStyles([css('page/chinois/grammaire.css')], b.signal);
    a.abort();
    await first.catch(error => { if (error.name !== 'AbortError') throw error; });
    (await second)();
    check(active().length === 1 && active()[0].href === css('page/chinois/grammaire.css'), 'Overlapping navigation survives earlier cancellation');

    await navigate(['page/sql.css', 'components/summary.css']);
    await navigate(['components/summary.css', 'page/sql.css']);
    check(active().map(link => link.href).join() === ['components/summary.css', 'page/sql.css'].map(css).join(), 'Manifest order preserved for reused styles');
    await navigate([]);
    check(active().length === 0 && document.querySelector('link[data-test-common]') !== null, 'Page without specific CSS keeps common styles');

    const append = document.head.append;
    const setTimer = window.setTimeout;
    try
    {
        // Simulate a request that never fires load/error, without waiting 10 seconds.
        document.head.append = () => {};
        window.setTimeout = callback => setTimer(callback, 10);
        try { await preparePageStyles([css('page/sql.css')], new AbortController().signal); throw new Error('Hanging CSS accepted'); }
        catch (error) { if (!error.message.includes('timed out')) throw error; }
        check(document.querySelectorAll('link[media="not all"]').length === 0, 'Hung request times out and cleans pending styles');
    }
    finally
    {
        document.head.append = append;
        window.setTimeout = setTimer;
    }
    return results;
}
