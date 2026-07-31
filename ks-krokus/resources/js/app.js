import { initMobileMenu } from './modules/mobile-menu';
import { initStickyHeader } from './modules/sticky-header';
import { initTheme } from './modules/theme';
import { initToasts } from './modules/toasts';
import { initAdminUi } from './modules/admin-ui';
import { initFileUploads } from './modules/file-upload';
import { initFormStates } from './modules/form-state';

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initMobileMenu();
    initStickyHeader();
    initToasts();
    initAdminUi();
    initFileUploads();
    initFormStates();
    if (document.querySelector('[data-rich-text]')) {
        import('./modules/rich-text').then(({ initRichTextEditors }) => {
            initRichTextEditors();
        });
    }

    document.querySelectorAll('.form-error').forEach((error, index) => {
        const field = error.closest('label')?.querySelector('input:not([type="hidden"]), select, textarea');

        if (!field) {
            return;
        }

        const errorId = error.id || `field-error-${index + 1}`;
        error.id = errorId;
        error.setAttribute('role', 'alert');
        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), errorId].filter(Boolean).join(' '));
    });

    const invalidField = [...document.querySelectorAll('[aria-invalid="true"]')]
        .find((field) => !field.disabled && field.getClientRects().length > 0);

    if (invalidField) {
        invalidField.focus({ preventScroll: false });
    } else {
        document.querySelector('[data-error-summary]')?.focus({ preventScroll: false });
    }
});
