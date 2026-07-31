import { initMobileMenu } from './modules/mobile-menu';
import { initStickyHeader } from './modules/sticky-header';
import { initTheme } from './modules/theme';

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initMobileMenu();
    initStickyHeader();

    document.querySelectorAll('.form-error').forEach((error, index) => {
        const field = error.closest('label')?.querySelector('input:not([type="hidden"]), select, textarea');

        if (!field) {
            return;
        }

        const errorId = error.id || `field-error-${index + 1}`;
        error.id = errorId;
        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), errorId].filter(Boolean).join(' '));
    });
});
