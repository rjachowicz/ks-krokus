import { initAccountMenu, initMobileMenu } from './modules/mobile-menu';
import { initStickyHeader } from './modules/sticky-header';
import { initTheme } from './modules/theme';
import { initToasts } from './modules/toasts';
import { initAdminUi } from './modules/admin-ui';
import { initFormStates } from './modules/form-state';
import { initImageFallbacks } from './modules/image-fallback';
import { initConfirmations } from './modules/confirmation';
import { initNotificationSelection } from './modules/notification-selection';
import { initEventReminders } from './modules/event-reminders';

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initMobileMenu();
    initAccountMenu();
    initStickyHeader();
    initToasts();
    initImageFallbacks();
    initConfirmations();
    initNotificationSelection();
    initEventReminders();

    if (document.querySelector('[data-admin-sidebar]')) {
        initAdminUi();
    }

    initFormStates();

    if (document.querySelector('[data-file-upload]')) {
        import('./modules/file-upload').then(({ initFileUploads }) => {
            initFileUploads();
        });
    }

    if (document.querySelector('[data-media-cropper-dialog]')) {
        import('./modules/media-cropper').then(({ initMediaCropper }) => {
            initMediaCropper();
        });
    }

    if (document.querySelector('[data-listing-images], [data-listing-gallery]')) {
        import('./modules/listing-images').then(({ initListingGallery, initListingImages }) => {
            initListingGallery();
            initListingImages();
        });
    }

    if (document.querySelector('[data-rich-text]')) {
        import('./modules/rich-text').then(({ initRichTextEditors }) => {
            initRichTextEditors();
        });
    }

    if (document.querySelector('[data-event-dialog]')) {
        import('./modules/event-dialog').then(({ initEventDialogs }) => {
            initEventDialogs();
        });
    }

    if (document.querySelector('[data-result-user-combobox]')) {
        import('./modules/result-user-combobox').then(({ initResultUserComboboxes }) => {
            initResultUserComboboxes();
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
