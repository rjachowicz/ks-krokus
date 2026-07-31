import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';

const actions = [
    ['bold', 'Pogrubienie', (editor) => editor.chain().focus().toggleBold().run()],
    ['italic', 'Kursywa', (editor) => editor.chain().focus().toggleItalic().run()],
    ['heading', 'Nagłówek', (editor) => editor.chain().focus().toggleHeading({ level: 2 }).run()],
    ['bulletList', 'Lista punktowana', (editor) => editor.chain().focus().toggleBulletList().run()],
    ['orderedList', 'Lista numerowana', (editor) => editor.chain().focus().toggleOrderedList().run()],
    ['blockquote', 'Cytat', (editor) => editor.chain().focus().toggleBlockquote().run()],
    ['undo', 'Cofnij', (editor) => editor.chain().focus().undo().run()],
    ['redo', 'Ponów', (editor) => editor.chain().focus().redo().run()],
];

export function initRichTextEditors() {
    document.querySelectorAll('textarea[data-rich-text]').forEach((textarea) => {
        const shell = document.createElement('div');
        const toolbar = document.createElement('div');
        const surface = document.createElement('div');
        const fieldLabel = textarea.labels?.[0] || null;
        const wasRequired = textarea.required;
        const shouldRestoreFocus = document.activeElement === textarea;
        const cspNonce = document.querySelector('meta[property="csp-nonce"]')?.nonce;
        const labelId = fieldLabel
            ? fieldLabel.id || `${textarea.id || 'rich-text'}-label`
            : null;

        if (fieldLabel && !fieldLabel.id) {
            fieldLabel.id = labelId;
        }

        shell.className = 'rich-text';
        toolbar.className = 'rich-text__toolbar';
        toolbar.setAttribute('role', 'toolbar');
        toolbar.setAttribute('aria-label', 'Formatowanie treści');
        surface.className = 'rich-text__surface';

        textarea.before(shell);
        shell.append(toolbar, surface);

        const editor = new Editor({
            element: surface,
            injectNonce: cspNonce || undefined,
            extensions: [
                StarterKit.configure({
                    code: false,
                    codeBlock: false,
                    heading: { levels: [2, 3] },
                }),
            ],
            content: textarea.value,
            editorProps: {
                attributes: {
                    'role': 'textbox',
                    'aria-multiline': 'true',
                    ...(labelId
                        ? { 'aria-labelledby': labelId }
                        : { 'aria-label': 'Treść aktualności' }),
                    ...(wasRequired ? { 'aria-required': 'true' } : {}),
                    ...(textarea.getAttribute('aria-describedby')
                        ? { 'aria-describedby': textarea.getAttribute('aria-describedby') }
                        : {}),
                    ...(textarea.getAttribute('aria-invalid') === 'true'
                        ? { 'aria-invalid': 'true' }
                        : {}),
                },
            },
            onUpdate: ({ editor: currentEditor }) => {
                textarea.value = currentEditor.getHTML();
            },
        });

        textarea.required = false;

        fieldLabel?.addEventListener('click', (event) => {
            if (event.target === fieldLabel) {
                event.preventDefault();
                editor.commands.focus();
            }
        });

        if (wasRequired) {
            const form = textarea.closest('form');
            let clientError = null;

            const clearRequiredError = () => {
                if (editor.getText().trim() === '') {
                    return;
                }

                clientError?.setAttribute('hidden', '');

                if (textarea.getAttribute('aria-invalid') !== 'true') {
                    shell.removeAttribute('aria-invalid');
                    editor.view.dom.removeAttribute('aria-invalid');
                }
            };

            editor.on('update', clearRequiredError);
            form?.addEventListener('submit', (event) => {
                if (editor.getText().trim() !== '') {
                    return;
                }

                event.preventDefault();

                if (!clientError) {
                    clientError = document.createElement('span');
                    clientError.id = `${textarea.id || 'rich-text'}-client-error`;
                    clientError.className = 'form-error';
                    clientError.setAttribute('role', 'alert');
                    clientError.textContent = 'Wpisz treść aktualności.';
                    shell.after(clientError);

                    const describedBy = editor.view.dom.getAttribute('aria-describedby');
                    editor.view.dom.setAttribute(
                        'aria-describedby',
                        [describedBy, clientError.id].filter(Boolean).join(' '),
                    );
                }

                clientError.hidden = false;
                shell.setAttribute('aria-invalid', 'true');
                editor.view.dom.setAttribute('aria-invalid', 'true');
                editor.commands.focus();
            }, true);
        }

        actions.forEach(([name, label, execute]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'rich-text__button';
            button.textContent = label;
            if (!['undo', 'redo'].includes(name)) {
                button.setAttribute('aria-pressed', 'false');
            }
            button.addEventListener('click', () => execute(editor));
            button.addEventListener('focus', () => {
                [...toolbar.querySelectorAll('button')].forEach((toolbarButton) => {
                    toolbarButton.tabIndex = toolbarButton === button ? 0 : -1;
                });
            });
            toolbar.append(button);

            const updateButtonState = () => {
                const active = editor.isActive(name);
                button.classList.toggle('is-active', active);
                if (button.hasAttribute('aria-pressed')) {
                    button.setAttribute('aria-pressed', String(active));
                }

                if (name === 'undo') {
                    button.disabled = !editor.can().chain().undo().run();
                }

                if (name === 'redo') {
                    button.disabled = !editor.can().chain().redo().run();
                }
            };

            editor.on('transaction', updateButtonState);
            updateButtonState();
        });

        const toolbarButtons = [...toolbar.querySelectorAll('button')];
        toolbarButtons.forEach((button, index) => {
            button.tabIndex = index === 0 ? 0 : -1;
        });
        toolbar.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
                return;
            }

            const enabledButtons = toolbarButtons.filter((button) => !button.disabled);
            const currentIndex = enabledButtons.indexOf(document.activeElement);
            let nextIndex = currentIndex;

            if (event.key === 'Home') {
                nextIndex = 0;
            } else if (event.key === 'End') {
                nextIndex = enabledButtons.length - 1;
            } else if (event.key === 'ArrowRight') {
                nextIndex = (currentIndex + 1) % enabledButtons.length;
            } else {
                nextIndex = (currentIndex - 1 + enabledButtons.length) % enabledButtons.length;
            }

            event.preventDefault();
            enabledButtons[nextIndex]?.focus();
        });

        if (textarea.getAttribute('aria-invalid') === 'true') {
            shell.setAttribute('aria-invalid', 'true');
        }

        textarea.classList.add('rich-text__fallback--enhanced');

        if (shouldRestoreFocus) {
            editor.commands.focus();
        }
    });
}
