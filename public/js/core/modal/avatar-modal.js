export function avatarModal(
    avatars,
)
{
    return new Promise(
        (
            resolve,
        ) =>
        {
            const overlay =
                document.createElement(
                    'div',
                );

            overlay.className =
                'confirm-modal-overlay';

            overlay.innerHTML = `
                <div class="confirm-modal">

                    <h3>
                        Choisir un avatar
                    </h3>

                    <div class="avatar-modal-grid">

                        ${avatars.map(
                            (
                                avatar,
                            ) => `
                                <button
                                    class="avatar-modal-item"
                                    data-avatar="${avatar.thumbnail}"
                                    type="button"
                                >

                                    <img
                                        src="/lolissr/images/avatars/thumbnail/${avatar.thumbnail}.${avatar.extension}"
                                        alt="${avatar.thumbnail}"
                                        draggable="false"
                                    >

                                </button>
                            `,
                        ).join('')}

                    </div>

                </div>
            `;

            document.body.append(
                overlay,
            );

            document.body.style.overflow =
                'hidden';

            const close =
                (
                    result = null,
                ) =>
                {
                    document.body.style.overflow =
                        '';

                    overlay.remove();

                    resolve(
                        result,
                    );
                };

            overlay
                .querySelectorAll(
                    '.avatar-modal-item',
                )
                .forEach(
                    (
                        button,
                    ) =>
                    {
                        button.addEventListener(
                            'click',
                            () =>
                                close(
                                    button.dataset.avatar,
                                ),
                        );
                    },
                );

            overlay.addEventListener(
                'click',
                (
                    event,
                ) =>
                {
                    if (
                        event.target
                        === overlay
                    )
                    {
                        close();
                    }
                },
            );
        },
    );
}
