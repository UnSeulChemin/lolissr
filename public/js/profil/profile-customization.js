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

import {
    invalidateProfilePages,
} from './profile-cache.js';

import {
    avatarModal,
} from '../core/modal/avatar-modal.js';

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

    await post(
        '/lolissr/profil/ajax/update-title',
        {
            title,
        },
    );

    const customizationTitle =
        document.querySelector(
            '.profile-customization-title',
        );

    if (
        customizationTitle
    )
    {
        customizationTitle.textContent =
            title;
    }

    const profileSubtitle =
        document.querySelector(
            '.profile-subtitle',
        );

    if (
        profileSubtitle
    )
    {
        profileSubtitle.textContent =
            title;
    }

    invalidateProfilePages();

    showToast(
        'Titre mis à jour',
        'success',
    );
}

async function openAvatarModal()
{
    const data =
        await get(
            '/lolissr/profil/ajax/avatars',
        );

    const thumbnail =
        await avatarModal(
            data.data.avatars,
        );

    if (
        ! thumbnail
    )
    {
        return;
    }

    const response =
        await post(
            '/lolissr/profil/ajax/update-avatar',
            {
                avatar: thumbnail,
            },
        );

    const avatarPath =
        `/lolissr/images/avatar/thumbnail/${response.data.thumbnail}.${response.data.extension}`;

    const customizationAvatar =
        document.querySelector(
            '.profile-customization-avatar img',
        );

    if (
        customizationAvatar
    )
    {
        customizationAvatar.src =
            avatarPath;
    }

    const profileAvatar =
        document.querySelector(
            '.profile-avatar img',
        );

    if (
        profileAvatar
    )
    {
        profileAvatar.src =
            avatarPath;
    }

    invalidateProfilePages();

    showToast(
        'Avatar mis à jour',
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

    document
        .querySelector(
            '.js-profile-avatar',
        )
        ?.addEventListener(
            'click',
            () =>
            {
                void openAvatarModal();
            },
        );
}
