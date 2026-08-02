function formatBytes(bytes) {
    return bytes < 1024 * 1024
        ? `${Math.ceil(bytes / 1024)} KB`
        : `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}

function fileKey(file) {
    return `${file.name}:${file.size}:${file.lastModified}`;
}

export function initListingGallery() {
    document.querySelectorAll('[data-listing-gallery]').forEach((gallery) => {
        const mainLink = gallery.querySelector('[data-listing-gallery-open]');
        const mainImage = gallery.querySelector('[data-listing-gallery-main]');
        const mainCaption = gallery.querySelector('[data-listing-gallery-caption]');
        const thumbnails = [...gallery.querySelectorAll('[data-listing-gallery-thumbnail]')];
        const lightbox = document.querySelector('[data-listing-lightbox]');
        const lightboxImage = lightbox?.querySelector('[data-listing-lightbox-image]');
        const lightboxCaption = lightbox?.querySelector('[data-listing-lightbox-caption]');
        const closeButton = lightbox?.querySelector('[data-listing-lightbox-close]');

        if (!mainLink || !mainImage) {
            return;
        }

        const setCaption = (element, caption) => {
            if (!element) return;
            element.textContent = caption;
            element.hidden = caption === '';
        };

        const selectImage = (thumbnail) => {
            const { src = '', alt = '', caption = '' } = thumbnail.dataset;
            mainLink.href = src;
            mainLink.setAttribute('aria-label', `Powiększ zdjęcie: ${alt}`);
            mainImage.src = src;
            mainImage.alt = alt;
            setCaption(mainCaption, caption);
            thumbnails.forEach((candidate) => {
                if (candidate === thumbnail) {
                    candidate.setAttribute('aria-current', 'true');
                } else {
                    candidate.removeAttribute('aria-current');
                }
            });
        };

        thumbnails.forEach((thumbnail) => {
            thumbnail.addEventListener('click', (event) => {
                event.preventDefault();
                selectImage(thumbnail);
            });
        });

        mainLink.addEventListener('click', (event) => {
            if (!lightbox || !lightboxImage || typeof lightbox.showModal !== 'function') {
                return;
            }

            event.preventDefault();
            lightboxImage.src = mainLink.href;
            lightboxImage.alt = mainImage.alt;
            setCaption(lightboxCaption, mainCaption?.textContent.trim() || '');
            lightbox.showModal();
            closeButton?.focus();
        });

        closeButton?.addEventListener('click', () => lightbox.close());
        lightbox?.addEventListener('click', (event) => {
            if (event.target === lightbox) lightbox.close();
        });
    });
}

export function initListingImages() {
    document.querySelectorAll('[data-listing-images]').forEach((upload) => {
        const input = upload.querySelector('input[type="file"]');
        const preview = upload.querySelector('[data-listing-image-preview]');
        const error = upload.querySelector('[data-listing-image-error]');
        const form = upload.closest('form');
        const deletionInputs = [...(form?.querySelectorAll('[data-existing-listing-image-delete]') || [])];
        const maxFiles = Number.parseInt(upload.dataset.maxFiles || '10', 10);
        const maxSizeKb = Number.parseInt(upload.dataset.maxSizeKb || '6144', 10);
        const existingFiles = Number.parseInt(upload.dataset.existingFiles || '0', 10);
        let items = [];
        let draggedIndex = null;

        if (!input || !preview) {
            return;
        }

        const syncInput = () => {
            const transfer = new DataTransfer();
            items.forEach((item) => transfer.items.add(item.file));
            input.files = transfer.files;
        };

        const validate = () => {
            const remainingExisting = Math.max(0, existingFiles - deletionInputs.filter((field) => field.checked).length);
            let message = '';

            if (remainingExisting + items.length > maxFiles) {
                message = `Ogłoszenie może zawierać łącznie maksymalnie ${maxFiles} zdjęć.`;
            } else if (items.some(({ file }) => file.size > maxSizeKb * 1024)) {
                message = `Każde zdjęcie może mieć maksymalnie ${formatBytes(maxSizeKb * 1024)}.`;
            } else if (items.some(({ file }) => {
                const mimeOk = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type);
                const extensionOk = /\.(?:jpe?g|png|webp)$/i.test(file.name);
                return file.type ? !mimeOk : !extensionOk;
            })) {
                message = 'Zdjęcie musi być w formacie JPG, PNG lub WEBP.';
            }

            input.setCustomValidity(message);
            upload.classList.toggle('is-invalid', message !== '');
            if (message) {
                input.setAttribute('aria-invalid', 'true');
            } else {
                input.removeAttribute('aria-invalid');
            }
            if (error) {
                error.textContent = message;
                error.hidden = message === '';
            }
        };

        const render = () => {
            preview.replaceChildren();
            syncInput();
            validate();

            items.forEach((item, index) => {
                const card = document.createElement('article');
                card.className = 'listing-upload-item';
                card.draggable = true;
                card.dataset.index = String(index);

                const image = document.createElement('img');
                const objectUrl = URL.createObjectURL(item.file);
                image.src = objectUrl;
                image.alt = '';
                image.addEventListener('load', () => URL.revokeObjectURL(objectUrl), { once: true });
                image.addEventListener('error', () => URL.revokeObjectURL(objectUrl), { once: true });

                const previewWrap = document.createElement('div');
                previewWrap.className = 'listing-upload-item__preview';
                previewWrap.append(image);
                if (item.primary) {
                    const primaryBadge = document.createElement('span');
                    primaryBadge.className = 'listing-image-primary';
                    primaryBadge.textContent = 'Zdjęcie główne';
                    previewWrap.append(primaryBadge);
                }

                const details = document.createElement('div');
                details.className = 'listing-upload-item__details';
                const fileName = document.createElement('strong');
                fileName.textContent = item.file.name;
                const fileSize = document.createElement('span');
                fileSize.textContent = formatBytes(item.file.size);
                details.append(fileName, fileSize);

                const altLabel = document.createElement('label');
                altLabel.textContent = 'Tekst alternatywny';
                const altInput = document.createElement('input');
                altInput.type = 'text';
                altInput.name = `new_image_alt[${index}]`;
                altInput.maxLength = 255;
                altInput.value = item.alt;
                altInput.addEventListener('input', () => { item.alt = altInput.value; });
                altLabel.append(altInput);

                const captionLabel = document.createElement('label');
                captionLabel.textContent = 'Podpis';
                const captionInput = document.createElement('textarea');
                captionInput.name = `new_image_caption[${index}]`;
                captionInput.rows = 2;
                captionInput.maxLength = 1000;
                captionInput.value = item.caption;
                captionInput.addEventListener('input', () => { item.caption = captionInput.value; });
                captionLabel.append(captionInput);

                const primaryLabel = document.createElement('label');
                primaryLabel.className = 'form-check';
                const primary = document.createElement('input');
                primary.type = 'radio';
                primary.name = 'primary_new_index';
                primary.value = String(index);
                primary.checked = item.primary;
                primary.addEventListener('change', () => {
                    items.forEach((candidate) => { candidate.primary = candidate === item; });
                    render();
                });
                primaryLabel.append(primary, document.createTextNode(' Ustaw jako zdjęcie główne'));

                const actions = document.createElement('div');
                actions.className = 'listing-upload-item__actions';
                const up = document.createElement('button');
                up.type = 'button';
                up.className = 'btn btn-secondary';
                up.textContent = '↑ W górę';
                up.setAttribute('aria-label', `Przesuń zdjęcie ${item.file.name} w górę`);
                up.disabled = index === 0;
                up.addEventListener('click', () => {
                    [items[index - 1], items[index]] = [items[index], items[index - 1]];
                    render();
                });
                const down = document.createElement('button');
                down.type = 'button';
                down.className = 'btn btn-secondary';
                down.textContent = '↓ W dół';
                down.setAttribute('aria-label', `Przesuń zdjęcie ${item.file.name} w dół`);
                down.disabled = index === items.length - 1;
                down.addEventListener('click', () => {
                    [items[index + 1], items[index]] = [items[index], items[index + 1]];
                    render();
                });
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'btn btn-danger-outline';
                remove.textContent = 'Usuń';
                remove.setAttribute('aria-label', `Usuń zdjęcie ${item.file.name}`);
                remove.addEventListener('click', () => {
                    const removed = items.splice(index, 1)[0];
                    if (removed?.primary && items[0]) {
                        items[0].primary = true;
                    }
                    render();
                });
                actions.append(up, down, remove);
                card.append(previewWrap, details, altLabel, captionLabel, primaryLabel, actions);

                card.addEventListener('dragstart', () => { draggedIndex = index; });
                card.addEventListener('dragover', (event) => event.preventDefault());
                card.addEventListener('drop', (event) => {
                    event.preventDefault();
                    if (draggedIndex === null || draggedIndex === index) return;
                    const [moved] = items.splice(draggedIndex, 1);
                    items.splice(index, 0, moved);
                    draggedIndex = null;
                    render();
                });
                preview.append(card);
            });
        };

        const addFiles = (files, replace = false) => {
            const previous = new Map(items.map((item) => [fileKey(item.file), item]));
            const candidates = [...files].map((file) => previous.get(fileKey(file)) || {
                file,
                alt: '',
                caption: '',
                primary: false,
            });
            items = replace ? candidates : [...items, ...candidates];
            if (items.length && !items.some((item) => item.primary)) {
                items[0].primary = true;
            }
            render();
        };

        input.addEventListener('change', () => addFiles(input.files, true));
        deletionInputs.forEach((field) => field.addEventListener('change', validate));
        upload.addEventListener('dragover', (event) => {
            event.preventDefault();
            upload.classList.add('is-dragging');
        });
        upload.addEventListener('dragleave', () => upload.classList.remove('is-dragging'));
        upload.addEventListener('drop', (event) => {
            event.preventDefault();
            upload.classList.remove('is-dragging');
            if (event.dataTransfer) addFiles(event.dataTransfer.files);
        });
    });
}
