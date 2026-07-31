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
        shell.className = 'rich-text';
        toolbar.className = 'rich-text__toolbar';
        toolbar.setAttribute('role', 'toolbar');
        toolbar.setAttribute('aria-label', 'Formatowanie treści');
        surface.className = 'rich-text__surface';

        textarea.before(shell);
        shell.append(toolbar, surface);

        const editor = new Editor({
            element: surface,
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
                    'aria-label': 'Treść aktualności',
                    'aria-multiline': 'true',
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

        actions.forEach(([name, label, execute]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'rich-text__button';
            button.textContent = label;
            if (!['undo', 'redo'].includes(name)) {
                button.setAttribute('aria-pressed', 'false');
            }
            button.addEventListener('click', () => execute(editor));
            toolbar.append(button);

            editor.on('transaction', () => {
                const active = editor.isActive(name);
                button.classList.toggle('is-active', active);
                if (button.hasAttribute('aria-pressed')) {
                    button.setAttribute('aria-pressed', String(active));
                }
            });
        });

        if (textarea.getAttribute('aria-invalid') === 'true') {
            shell.setAttribute('aria-invalid', 'true');
        }

        textarea.classList.add('rich-text__fallback--enhanced');
    });
}
