export async function testPageStyles()
{
    const {runInitializers} = await import('./js/routes/run-initializers.js');
    const {ROUTE_INITIALIZERS} = await import('./js/routes/route-initializers.js');
    const assert = (condition, message) => { if (!condition) throw new Error(message); };
    const note = ROUTE_INITIALIZERS.flatMap(route => route.initializers)
        .find(([label]) => label === 'UpdateNote')[1];
    assert(!note.isRelevant(), 'Absent controls must not load actions');
    const button = document.createElement('button');
    button.className = 'js-note-button';
    document.body.append(button);
    assert(note.isRelevant(), 'Present controls must load actions');
    button.remove();

    const calls = [];
    const errors = [];
    let release;
    const first = () => calls.push('first');
    first.preload = () => new Promise(resolve => { release = resolve; });
    const second = () => calls.push('second');
    second.preload = () => { calls.push('load-second'); };
    const skipped = () => { throw new Error('Should be skipped'); };
    skipped.isRelevant = () => false;
    skipped.preload = skipped;
    const failed = () => { throw new Error('Should not initialize'); };
    failed.preload = () => Promise.reject(new Error('load failed'));
    const safe = async (label, init) => { try { await init(); } catch (e) { errors.push(e.message); } };
    const work = runInitializers([['first',first],['skip',skipped],['fail',failed],['second',second]],safe);
    assert(calls.join() === 'load-second', 'Imports must start concurrently');
    release();
    await work;
    assert(calls.join() === 'load-second,first,second', 'Initialization order changed');
    assert(errors.join() === 'load failed', 'Import failure must be isolated');
    calls.length = 0;
    await runInitializers([['second',second]],safe,() => false);
    assert(calls.join() === 'load-second', 'Obsolete route initialized');
    return ['selective actions', 'concurrent imports', 'ordered initialization', 'isolated failures', 'obsolete routes'];
}
