export function initMobileMenu() {
    const button = document.querySelector('[data-mobile-menu-toggle]');
    const navigation = document.querySelector('[data-main-navigation]');

    if (!button || !navigation) {
        return;
    }

    const mobileNavigation = window.matchMedia('(max-width: 959px)');

    const syncAvailability = () => {
        const hidden = mobileNavigation.matches && !navigation.classList.contains('is-open');
        navigation.inert = hidden;

        if (mobileNavigation.matches) {
            navigation.setAttribute('aria-hidden', String(hidden));
        } else {
            navigation.removeAttribute('aria-hidden');
        }
    };

    const closeMenu = () => {
        navigation.classList.remove('is-open');
        button.classList.remove('is-open');
        button.setAttribute('aria-expanded', 'false');
        button.setAttribute('aria-label', 'Otwórz menu');
        syncAvailability();
    };

    const openMenu = () => {
        navigation.classList.add('is-open');
        button.classList.add('is-open');
        button.setAttribute('aria-expanded', 'true');
        button.setAttribute('aria-label', 'Zamknij menu');
        syncAvailability();
        navigation.querySelector('a')?.focus();
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
        if (event.key === 'Escape' && navigation.classList.contains('is-open')) {
            closeMenu();
            button.focus();
        }
    });

    document.addEventListener('focusin', (event) => {
        const target = event.target;

        if (
            navigation.classList.contains('is-open')
            && target instanceof Node
            && !navigation.contains(target)
            && !button.contains(target)
        ) {
            closeMenu();
        }
    });

    mobileNavigation.addEventListener('change', closeMenu);
    closeMenu();
}
