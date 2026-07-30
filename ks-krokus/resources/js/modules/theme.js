const STORAGE_KEY = 'ks-krokus-theme';
const THEMES = new Set(['light', 'dark']);

function getPreferredTheme() {
    const savedTheme = localStorage.getItem(STORAGE_KEY);

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
    localStorage.setItem(STORAGE_KEY, normalizedTheme);

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
