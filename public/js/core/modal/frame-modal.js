export function frameModal(
    frames,
    avatar,
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
                        Choisir un cadre
                    </h3>

                    <div class="avatar-modal-grid">

                        ${frames.map(
                            (
                                frame,
                            ) => `
                                <button
                                    class="avatar-modal-item"
                                    data-frame="${frame.frame}"
                                    type="button"
                                >

                                    <div class="frame-preview">

                                        <img
                                            class="frame-preview-avatar"
                                            src="${avatar}"
                                            alt=""
                                            draggable="false"
                                        >

                                        <img
                                            class="frame-preview-frame"
                                            src="/lolissr/images/frames/thumbnail/${frame.frame}.${frame.frame_extension}"
                                            alt="${frame.frame}"
                                            draggable="false"
                                        >

                                    </div>

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
                                    button.dataset.frame,
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