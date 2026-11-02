import { useEffect, useRef, useState } from 'react';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import axios from 'axios';

function ToolButton({ active, label, onClick, children }) {
    return (
        <button
            type="button"
            title={label}
            aria-label={label}
            aria-pressed={active}
            onClick={onClick}
            className={`rounded-control px-2.5 py-1.5 text-sm font-medium transition-colors ${active ? 'bg-accent text-ink-inverse' : 'text-ink-muted hover:bg-surface-sunken hover:text-ink'}`}
        >
            {children}
        </button>
    );
}

export default function RichTextEditor({ value, onChange, error }) {
    const fileInput = useRef(null);
    const [uploading, setUploading] = useState(false);
    const editor = useEditor({
        extensions: [
            StarterKit.configure({ heading: { levels: [2, 3, 4] } }),
            Link.configure({ openOnClick: false, HTMLAttributes: { rel: 'noopener noreferrer' } }),
            Image.configure({ allowBase64: false }),
        ],
        content: value || '',
        editorProps: {
            attributes: {
                class: 'min-h-72 px-4 py-4 text-base leading-relaxed text-ink focus:outline-none',
            },
        },
        onUpdate: ({ editor: currentEditor }) => onChange({ json: currentEditor.getJSON(), html: currentEditor.getHTML() }),
    });

    useEffect(() => {
        if (editor && value && !editor.isFocused && editor.getText() === '') editor.commands.setContent(value);
    }, [editor, value]);

    if (!editor) return null;

    function addLink() {
        const href = window.prompt('URL del enlace');
        if (!href) return;
        editor.chain().focus().extendMarkRange('link').setLink({ href }).run();
    }

    async function addImage(event) {
        const image = event.target.files?.[0];
        event.target.value = '';
        if (!image) return;
        const alt = window.prompt('Describe la imagen para lectores de pantalla y buscadores');
        if (!alt?.trim()) return;

        const body = new FormData();
        body.append('image', image);
        body.append('alt', alt.trim());
        setUploading(true);
        try {
            const response = await axios.post(route('admin.blog.images.store'), body);
            editor.chain().focus().setImage({ src: response.data.url, alt: response.data.alt }).run();
        } catch (requestError) {
            window.alert(requestError.response?.data?.message || 'No pudimos subir la imagen.');
        } finally {
            setUploading(false);
        }
    }

    return (
        <div>
            <div className={`overflow-hidden rounded-control border bg-surface ${error ? 'border-danger-strong' : 'border-line-strong'}`}>
                <div className="flex flex-wrap gap-1 border-b border-line bg-surface-sunken p-2">
                    <ToolButton label="Negrita" active={editor.isActive('bold')} onClick={() => editor.chain().focus().toggleBold().run()}><strong>B</strong></ToolButton>
                    <ToolButton label="Cursiva" active={editor.isActive('italic')} onClick={() => editor.chain().focus().toggleItalic().run()}><em>I</em></ToolButton>
                    <ToolButton label="TÃ­tulo de secciÃ³n" active={editor.isActive('heading', { level: 2 })} onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()}>H2</ToolButton>
                    <ToolButton label="Pregunta frecuente" active={editor.isActive('heading', { level: 3 })} onClick={() => editor.chain().focus().toggleHeading({ level: 3 }).run()}>H3</ToolButton>
                    <ToolButton label="Lista" active={editor.isActive('bulletList')} onClick={() => editor.chain().focus().toggleBulletList().run()}>Lista</ToolButton>
                    <ToolButton label="Cita" active={editor.isActive('blockquote')} onClick={() => editor.chain().focus().toggleBlockquote().run()}>Cita</ToolButton>
                    <ToolButton label="Enlace" active={editor.isActive('link')} onClick={addLink}>Enlace</ToolButton>
                    <ToolButton label="Quitar enlace" active={false} onClick={() => editor.chain().focus().unsetLink().run()}>Quitar</ToolButton>
                    <ToolButton label="Insertar imagen" active={false} onClick={() => fileInput.current?.click()}>{uploading ? 'Subiendo…' : 'Imagen'}</ToolButton>
                </div>
                <EditorContent editor={editor} />
            </div>
            <input ref={fileInput} type="file" accept="image/jpeg,image/png,image/webp" className="sr-only" onChange={addImage} />
            <p className="mt-2 text-xs text-ink-muted">Usa H2 para secciones. Para FAQ, crea un H2 llamado “Preguntas frecuentes” y usa H3 con preguntas terminadas en “?”.</p>
            {error && <p className="mt-1.5 text-xs font-medium text-danger-strong">{error}</p>}
        </div>
    );
}
