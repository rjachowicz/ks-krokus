const STORAGE_KEY = 'ks-krokus-theme';
const THEMES = new Set(['light', 'dark']);

function getPreferredTheme() {
    let savedTheme = null;

    try {
        savedTheme = localStorage.getItem(STORAGE_KEY);
    } catch {
        // Preferencja systemowa pozostaje bezpiecznym ustawieniem awaryjnym.
    }

    if (THEMES.has(savedTheme)) {
        return savedTheme;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light';
}

function applyTheme(theme, button = null) {
    const normalizedTheme = THEMES.has(theme) ? theme : 'light';

    document.documentElement.dataset.theme = normalizedTheme;
    try {
        localStorage.setItem(STORAGE_KEY, normalizedTheme);
    } catch {
        // Motyw nadal działa w bieżącej karcie bez trwałego zapisu.
    }

    button?.setAttribute(
        'aria-label',
        normalizedTheme === 'dark'
            ? 'Włącz jasny motyw'
            : 'Włącz ciemny motyw',
    );
}

export function initTheme() {
    const button = document.querySelector('[data-theme-toggle]');

    applyTheme(
        document.documentElement.dataset.theme || getPreferredTheme(),
        button,
    );

    button?.addEventListener('click', () => {
        const nextTheme =
            document.documentElement.dataset.theme === 'dark'
                ? 'light'
                : 'dark';

        applyTheme(nextTheme, button);
    });
}
