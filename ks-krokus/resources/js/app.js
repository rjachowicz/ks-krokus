import { initMobileMenu } from './modules/mobile-menu';
import { initStickyHeader } from './modules/sticky-header';
import { initTheme } from './modules/theme';

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initMobileMenu();
    initStickyHeader();
});
