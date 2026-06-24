// =========================================
// PROFILE CUSTOMIZATION
// =========================================

import {
    get,
    post,
} from '../core/http.js';

import {
    titleModal,
} from '../core/modal/modal.js';

import {
    showToast,
} from '../core/toast.js';

// =========================================
// OPEN TITLE MODAL
// =========================================

async function openTitleModal()
{
    const data =
        await get(
            '/lolissr/profil/ajax/titles',
        );

    const title =
        await titleModal(
            data.data.titles,
        );

    if (
        !title
    )
    {
        return;
    }

    await post(
        '/lolissr/profil/ajax/update-title',
        {
            title,
        },
    );

    document
        .querySelector(
            '.profile-customization-title',
        )
        ?.replaceChildren(
            document.createTextNode(
                title,
            ),
        );

    showToast(
        'Titre mis à jour',
        'success',
    );
}

// =========================================
// INIT
// =========================================

export function initProfileCustomization()
{
    document
        .querySelector(
            '.js-profile-title',
        )
        ?.addEventListener(
            'click',
            () =>
            {
                void openTitleModal();
            },
        );
}