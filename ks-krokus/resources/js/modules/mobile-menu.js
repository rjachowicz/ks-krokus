const DESKTOP_BREAKPOINT = 960;

export function initMobileMenu() {
    const button = document.querySelector('[data-mobile-menu-toggle]');
    const navigation = document.querySelector('[data-main-navigation]');

    if (!button || !navigation) {
        return;
    }

    const closeMenu = () => {
        navigation.classList.remove('is-open');
        button.classList.remove('is-open');
        button.setAttribute('aria-expanded', 'false');
        button.setAttribute('aria-label', 'Otwórz menu');
    };

    const openMenu = () => {
        navigation.classList.add('is-open');
        button.classList.add('is-open');
        button.setAttribute('aria-expanded', 'true');
        button.setAttribute('aria-label', 'Zamknij menu');
    };

    button.addEventListener('click', () => {
        navigation.classList.contains('is-open')
            ? closeMenu()
            : openMenu();
    });

    navigation.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMenu);
    });

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (
            target instanceof Node
            && !navigation.contains(target)
            && !button.contains(target)
        ) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
            button.focus();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= DESKTOP_BREAKPOINT) {
            closeMenu();
        }
    });
}
