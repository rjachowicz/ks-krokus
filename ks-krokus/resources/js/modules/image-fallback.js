const createPlaceholder = (image) => {
    const placeholder = document.createElement('span');
    const mark = document.createElement('span');
    const label = image.getAttribute('alt')?.trim();

    placeholder.className = 'image-placeholder image-placeholder--generated';
    placeholder.dataset.imageFallback = '';

    if (label) {
        placeholder.setAttribute('role', 'img');
        placeholder.setAttribute('aria-label', `Obraz niedostępny: ${label}`);
    } else {
        placeholder.setAttribute('aria-hidden', 'true');
    }

    mark.className = 'image-placeholder__mark';
    mark.setAttribute('aria-hidden', 'true');
    mark.textContent = 'KS';
    placeholder.append(mark);

    return placeholder;
};

const showFallback = (image) => {
    if (image.nextElementSibling?.matches('[data-image-fallback]')) {
        return;
    }

    image.hidden = true;
    image.insertAdjacentElement('afterend', createPlaceholder(image));
};

const hideFallback = (image) => {
    image.hidden = false;

    if (image.nextElementSibling?.matches('[data-image-fallback]')) {
        image.nextElementSibling.remove();
    }
};

export const initImageFallbacks = () => {
    document.querySelectorAll('img').forEach((image) => {
        image.addEventListener('error', () => showFallback(image));
        image.addEventListener('load', () => hideFallback(image));

        if (image.complete && image.naturalWidth === 0) {
            showFallback(image);
        }
    });
};
