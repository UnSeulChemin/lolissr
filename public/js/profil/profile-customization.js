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

    const titleId =
        await titleModal(
            data.data.titles,
        );

    if (
        ! titleId
    ) {

        return;
    }

    console.log(
        titleId,
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