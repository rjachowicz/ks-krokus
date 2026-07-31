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

    document.querySelectorAll('.admin-table').forEach((table) => {
        const labels = [...table.querySelectorAll('thead th')].map((header) => header.textContent.trim());

        table.querySelectorAll('tbody tr').forEach((row) => {
            row.querySelectorAll('td').forEach((cell, index) => {
                cell.dataset.label = labels[index] || '';
            });
        });
    });

    const sidebarToggle = document.querySelector('[data-admin-menu-toggle]');
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const sidebarBackdrop = document.querySelector('[data-admin-menu-backdrop]');
    const mobileSidebar = window.matchMedia('(max-width: 860px)');

    const setSidebarState = (open) => {
        document.body.classList.toggle('admin-menu-open', open);
        sidebarToggle?.setAttribute('aria-expanded', String(open));
        sidebarToggle?.setAttribute('aria-label', open ? 'Zamknij menu panelu' : 'Otwórz menu panelu');
        if (sidebar) {
            sidebar.inert = mobileSidebar.matches && !open;
            sidebar.setAttribute('aria-hidden', String(mobileSidebar.matches && !open));
        }
    };

    sidebarToggle?.addEventListener('click', () => {
        setSidebarState(!document.body.classList.contains('admin-menu-open'));
    });

    sidebar?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setSidebarState(false));
    });
    sidebarBackdrop?.addEventListener('click', () => setSidebarState(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.body.classList.contains('admin-menu-open')) {
            setSidebarState(false);
            sidebarToggle?.focus();
        }
    });
    mobileSidebar.addEventListener('change', () => setSidebarState(false));
    setSidebarState(false);

    const dialog = document.querySelector('[data-confirm-dialog]');
    const dialogMessage = dialog?.querySelector('[data-confirm-message]');
    let pendingForm = null;

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true' || !dialog) {
                return;
            }

            event.preventDefault();
            pendingForm = form;
            dialogMessage.textContent = form.dataset.confirm;
            dialog.showModal();
        });
    });

    dialog?.querySelector('[data-confirm-accept]')?.addEventListener('click', () => {
        if (!pendingForm) {
            return;
        }

        pendingForm.dataset.confirmed = 'true';
        pendingForm.requestSubmit();
        dialog.close();
    });

    dialog?.addEventListener('close', () => {
        pendingForm = null;
    });
}
