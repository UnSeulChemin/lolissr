// ==================================================
// AJAX Transitions
// ==================================================

function delay(
    duration,
)
{
    return new Promise(
        (resolve) =>
        {
            window.setTimeout(
                resolve,
                duration,
            );
        },
    );
}

/*
|------------------------------------------------------------------
| Animate Out
|------------------------------------------------------------------
*/

export async function animateContentOut(
    content,
)
{
    content.classList.remove(
        'page-transition-in',
        'page-transition-visible',
    );

    content.classList.add(
        'page-transition-out',
    );

    await delay(
        90,
    );
}

/*
|------------------------------------------------------------------
| Animate In
|------------------------------------------------------------------
*/

export async function animateContentIn(
    content,
)
{
    content.classList.remove(
        'page-transition-out',
    );

    content.classList.add(
        'page-transition-in',
    );

    requestAnimationFrame(
        () =>
        {
            content.classList.add(
                'page-transition-visible',
            );
        },
    );

    await delay(
        140,
    );

    content.classList.remove(
        'page-transition-in',
        'page-transition-visible',
    );
}