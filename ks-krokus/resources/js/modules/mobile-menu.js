export function initMobileMenu() {
    const button = document.querySelector('[data-mobile-menu-toggle]');
    const navigation = document.querySelector('[data-main-navigation]');

    if (!button || !navigation) {
        return;
    }

    const mobileNavigation = window.matchMedia('(max-width: 1080px)');

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

export function initAccountMenu() {
    const menu = document.querySelector('[data-account-menu]');
    const button = menu?.querySelector('[data-account-menu-toggle]');
    const panel = menu?.querySelector('[data-account-menu-panel]');

    if (!menu || !button || !panel) {
        return;
    }

    const closeMenu = ({ restoreFocus = false } = {}) => {
        panel.hidden = true;
        button.setAttribute('aria-expanded', 'false');

        if (restoreFocus) {
            button.focus();
        }
    };

    const openMenu = ({ moveFocus = false } = {}) => {
        panel.hidden = false;
        button.setAttribute('aria-expanded', 'true');

        if (moveFocus) {
            panel.querySelector('a, button')?.focus();
        }
    };

    button.addEventListener('click', () => {
        panel.hidden ? openMenu() : closeMenu();
    });

    button.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            openMenu({ moveFocus: true });
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target instanceof Node && !menu.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('focusin', (event) => {
        if (
            !panel.hidden
            && event.target instanceof Node
            && !menu.contains(event.target)
        ) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) {
            closeMenu({ restoreFocus: true });
        }
    });

    window.matchMedia('(max-width: 1080px)').addEventListener('change', () => closeMenu());
    closeMenu();
}
