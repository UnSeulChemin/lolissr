// =========================================
// PROFILE CUSTOMIZATION
// =========================================

import {
    get,
} from '../core/http.js';

import {
    titleModal,
} from '../core/modal/modal.js';

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
        ! title
    )
    {
        return;
    }

    console.log(
        title,
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