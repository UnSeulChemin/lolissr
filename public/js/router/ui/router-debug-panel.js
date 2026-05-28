// =========================================
// ROUTER DEBUG PANEL
// =========================================

import {
    config,
} from '../../core/config.js';

import {
    debug,
} from '../../core/debug.js';

// =========================================
// CONFIG
// =========================================

const PANEL_ID =
    'router-debug-panel';

// =========================================
// STATE
// =========================================

let initialized =
    false;

// =========================================
// HELPERS
// =========================================

function canDebug()
{
    return config.debug;
}

function createPanel()
{
    const panel =
        document.createElement(
            'div',
        );

    panel.id =
        PANEL_ID;

    panel.innerHTML =
    `
        <div class="router-debug-title">
            SPA DEBUG
        </div>

        <div class="router-debug-content">
        </div>
    `;

    document.body.appendChild(
        panel,
    );

    return panel;
}

function getPanel()
{
    return (
        document.getElementById(
            PANEL_ID,
        )
        || createPanel()
    );
}

function appendLog(
    message,
)
{
    if (
        !canDebug()
    ) {

        return;
    }

    const panel =
        getPanel();

    const content =
        panel.querySelector(
            '.router-debug-content',
        );

    if (!content) {

        return;
    }

    const line =
        document.createElement(
            'div',
        );

    line.textContent =
        `[${new Date()
            .toLocaleTimeString()}] ${message}`;

    content.prepend(
        line,
    );

    while (
        content.children.length
        > config.debugPanel.maxLogs
    )
    {
        content.lastChild?.remove();
    }
}

// =========================================
// INIT
// =========================================

export function initRouterDebugPanel()
{
    if (
        initialized
    ) {

        return;
    }

    initialized =
        true;

    if (
        !canDebug()
    ) {

        return;
    }

    [
        'navigation:start',
        'navigation:fetch',
        'navigation:render',
        'navigation:ready',
        'navigation:error',
        'navigation:abort',
    ]
    .forEach(
        (
            eventName,
        ) =>
        {
            document.addEventListener(
                eventName,
                (
                    event,
                ) =>
                {
                    appendLog(
                        `${eventName} → ${event.detail?.to || ''}`,
                    );
                },
            );
        },
    );

    debug(
        'DEBUG_PANEL',
        'initialized',
    );
}