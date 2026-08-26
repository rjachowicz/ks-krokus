export function initAdminUi() {
    document.querySelectorAll('[data-trainer-toggle]').forEach((toggle) => {
        const form = toggle.closest('form');
        const bio = form?.querySelector('[data-trainer-bio]');

        if (!bio) {
            return;
        }

        const syncTrainerBio = () => {
            bio.disabled = !toggle.checked;
        };

        toggle.addEventListener('change', syncTrainerBio);
        syncTrainerBio();
    });

    document.querySelectorAll('[data-event-start]').forEach((start) => {
        const form = start.closest('form');
        const end = form?.querySelector('[data-event-end]');

        if (!end) {
            return;
        }

        const syncMinimumEnd = () => {
            end.min = start.value;
        };

        start.addEventListener('change', syncMinimumEnd);
        syncMinimumEnd();
    });

    document.querySelectorAll('[data-result-user]').forEach((userSelect) => {
        const form = userSelect.closest('form');
        const participant = form?.querySelector('[data-result-participant]');

        if (!participant) {
            return;
        }

        const syncParticipant = () => {
            participant.disabled = userSelect.value !== '';
        };

        userSelect.addEventListener('change', syncParticipant);
        syncParticipant();
    });

    const sidebarToggle = document.querySelector('[data-admin-menu-toggle]');
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const sidebarBackdrop = document.querySelector('[data-admin-menu-backdrop]');
    const adminContent = document.querySelector('[data-admin-content]');
    const adminUser = document.querySelector('[data-admin-user]');
    const adminSkipLink = document.querySelector('[data-admin-skip-link]');
    const mobileSidebar = window.matchMedia('(max-width: 860px)');

    const setSidebarState = (open) => {
        const isOpen = mobileSidebar.matches && open;

        document.body.classList.toggle('admin-menu-open', isOpen);
        sidebarToggle?.setAttribute('aria-expanded', String(isOpen));
        sidebarToggle?.setAttribute('aria-label', isOpen ? 'Zamknij menu panelu' : 'Otwórz menu panelu');
        if (sidebar) {
            const hidden = mobileSidebar.matches && !isOpen;
            sidebar.inert = hidden;

            if (mobileSidebar.matches) {
                sidebar.setAttribute('aria-hidden', String(hidden));
            } else {
                sidebar.removeAttribute('aria-hidden');
            }
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.inert = !isOpen;
            sidebarBackdrop.setAttribute('aria-hidden', String(!isOpen));
        }

        if (adminContent) {
            adminContent.inert = isOpen;
        }

        if (adminUser) {
            adminUser.inert = isOpen;
        }

        if (adminSkipLink) {
            adminSkipLink.inert = isOpen;
        }

        if (isOpen) {
            sidebar?.querySelector('a')?.focus();
        }
    };

    sidebarToggle?.addEventListener('click', () => {
        setSidebarState(!document.body.classList.contains('admin-menu-open'));
    });

    sidebar?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setSidebarState(false));
    });
    sidebarBackdrop?.addEventListener('click', () => {
        setSidebarState(false);
        sidebarToggle?.focus();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.body.classList.contains('admin-menu-open')) {
            setSidebarState(false);
            sidebarToggle?.focus();
        }
    });
    mobileSidebar.addEventListener('change', () => setSidebarState(false));
    setSidebarState(false);

}
