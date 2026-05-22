import { showToast }
    from '../../core/toast.js';

/*
|------------------------------------------------------------------
| CSRF
|------------------------------------------------------------------
*/

function getCsrfToken()
{
    return document
        .querySelector(
            'meta[name="csrf-token"]',
        )
        ?.getAttribute(
            'content',
        )
        ?? '';
}

/*
|------------------------------------------------------------------
| Delete request
|------------------------------------------------------------------
*/

async function sendDeleteRequest(
    url,
)
{
    const formData =
        new FormData();

    const csrfToken =
        getCsrfToken();

    if (csrfToken !== '') {
        formData.append(
            'csrf_token',
            csrfToken,
        );
    }

    const response =
        await fetch(
            url,
            {
                method: 'POST',

                headers:
                {
                    'X-Requested-With':
                        'XMLHttpRequest',

                    'Accept':
                        'application/json',
                },

                body: formData,
            },
        );

    const data =
        await response.json();

    return {
        response,
        data,
    };
}

/*
|------------------------------------------------------------------
| Init
|------------------------------------------------------------------
*/

export function initDeleteManga()
{
    /*
    |--------------------------------------------------------------
    | Anti double init
    |--------------------------------------------------------------
    */

    if (
        document.body.dataset
            .deleteMangaInit
        === 'true'
    ) {
        return;
    }

    document.body.dataset
        .deleteMangaInit =
            'true';

    /*
    |--------------------------------------------------------------
    | Global delegation
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        async event =>
        {
            const deleteButton =
                event.target.closest(
                    '.js-delete-manga',
                );

            if (!deleteButton) {
                return;
            }

            /*
            |------------------------------------------------------
            | Prevent double click
            |------------------------------------------------------
            */

            if (
                deleteButton.disabled
            ) {
                return;
            }

            /*
            |------------------------------------------------------
            | Confirm
            |------------------------------------------------------
            */

            const confirmed =
                window.confirm(
                    `
                    Supprimer ce manga ?
                    Cette action est irréversible.
                    `,
                );

            if (!confirmed) {
                return;
            }

            /*
            |------------------------------------------------------
            | Data
            |------------------------------------------------------
            */

            const url =
                deleteButton.dataset.url;

            const redirectUrl =
                deleteButton.dataset.redirect;

            const originalText =
                deleteButton.textContent;

            if (!url) {

                showToast(
                    'URL de suppression introuvable.',
                    'error',
                );

                return;
            }

            /*
            |------------------------------------------------------
            | Loading state
            |------------------------------------------------------
            */

            deleteButton.disabled =
                true;

            deleteButton.textContent =
                'Suppression...';

            try {

                const {
                    response,
                    data,
                } =
                    await sendDeleteRequest(
                        url,
                    );

                /*
                |--------------------------------------------------
                | Server error
                |--------------------------------------------------
                */

                if (
                    !response.ok
                    || !data.success
                ) {
                    throw new Error(
                        data.message
                        ?? 'Erreur lors de la suppression.',
                    );
                }

                /*
                |--------------------------------------------------
                | Success
                |--------------------------------------------------
                */

                showToast(
                    data.message
                    ?? 'Manga supprimé avec succès.',
                    'success',
                );

                /*
                |--------------------------------------------------
                | Redirect
                |--------------------------------------------------
                */

                window.location.href =
                    data.redirect
                    || redirectUrl
                    || '/';

            } catch (error) {

                console.error(
                    error,
                );

                showToast(
                    error?.message
                    ?? 'Erreur réseau lors de la suppression.',
                    'error',
                );

                /*
                |--------------------------------------------------
                | Restore button
                |--------------------------------------------------
                */

                deleteButton.disabled =
                    false;

                deleteButton.textContent =
                    originalText;
            }
        },
    );
}