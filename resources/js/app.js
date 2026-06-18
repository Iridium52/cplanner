import './bootstrap';
import Sortable from 'sortablejs';

document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status === 419) {
                preventDefault();
                window.location.href = '/login';
            }
        });
    });
});

document.addEventListener('alpine:init', () => {
    // Kanban board columns (cross-column drag)
    window.Alpine.directive('sortable', (el, { expression }, { cleanup }) => {
        const sortable = Sortable.create(el, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'opacity-30',
            dragClass: 'rotate-1',
            handle: '[x-sortable-handle]',
            onEnd() {
                el.dispatchEvent(new CustomEvent('sortable-update', { bubbles: true }));
            },
        });
        cleanup(() => sortable.destroy());
    });

    window.Alpine.directive('sortable-item', () => {});

    // Generic single-list reorder (handle-based)
    window.Alpine.directive('sortable-list', (el, {}, { cleanup }) => {
        const sortable = Sortable.create(el, {
            handle: '[x-sortable-handle]',
            animation: 150,
            ghostClass: 'opacity-40',
            onEnd() {
                el.dispatchEvent(new CustomEvent('sortable-update', { bubbles: true }));
            },
        });
        cleanup(() => sortable.destroy());
    });

    window.Alpine.directive('sortable-handle', () => {});

    // Alpine component for status list reordering
    window.statusSorter = (wire) => ({
        save() {
            const ids = [...this.$el.querySelectorAll('[data-id]')]
                .map(el => parseInt(el.dataset.id));
            wire.call('reorderStatuses', ids);
        },
    });

    // Clipboard paste + drag-and-drop file uploader for the task modal.
    // Handles images (converted to JPEG) and any other file type (PDF, Word, Excel, etc.)
    window.clipboardUploader = () => ({
        uploading: false,
        preview: null,
        dragging: false,
        _dragCounter: 0,

        // --- Clipboard paste ---
        async handlePaste(e) {
            const tag = e.target.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA') return;

            const clipData = e.clipboardData;
            if (!clipData) return;

            // Images from screenshot tools come as items, not files
            const imageItems = [...(clipData.items || [])].filter(i => i.type.startsWith('image/'));
            // Actual files copied from Explorer / Finder come in .files
            const pastedFiles = [...(clipData.files || [])].filter(f => !f.type.startsWith('image/') || imageItems.length === 0);

            if (imageItems.length) {
                e.preventDefault();
                const jpeg = await this.toJpeg(imageItems[0].getAsFile());
                await this.uploadOne(jpeg);
            } else if (pastedFiles.length) {
                e.preventDefault();
                for (const file of pastedFiles) {
                    await this.uploadOne(file);
                }
            }
        },

        // --- Drag and drop ---
        handleDragEnter(e) {
            if (!e.dataTransfer.types.includes('Files')) return;
            e.preventDefault();
            this._dragCounter++;
            this.dragging = true;
        },

        handleDragOver(e) {
            if (!e.dataTransfer.types.includes('Files')) return;
            e.preventDefault();
        },

        handleDragLeave(e) {
            this._dragCounter--;
            if (this._dragCounter <= 0) {
                this._dragCounter = 0;
                this.dragging = false;
            }
        },

        async handleDrop(e) {
            e.preventDefault();
            this._dragCounter = 0;
            this.dragging = false;

            const files = [...(e.dataTransfer.files || [])];
            if (!files.length) return;

            for (const file of files) {
                const upload = file.type.startsWith('image/')
                    ? await this.toJpeg(file)
                    : file;
                await this.uploadOne(upload);
            }
        },

        // --- Core upload: returns a Promise that resolves after the server saves the file ---
        uploadOne(file) {
            this.uploading = true;
            return new Promise((resolve) => {
                this.$wire.upload(
                    'attachment',
                    file,
                    async () => {
                        await this.$wire.call('uploadAttachment');
                        this.uploading = false;
                        resolve();
                    },
                    () => { this.uploading = false; resolve(); },
                    () => {}
                );
            });
        },

        // --- Convert any image to JPEG (for screenshots) ---
        async toJpeg(file) {
            const bitmap = await createImageBitmap(file);
            const canvas = document.createElement('canvas');
            canvas.width  = bitmap.width;
            canvas.height = bitmap.height;
            canvas.getContext('2d').drawImage(bitmap, 0, 0);
            const ts = new Date().toISOString().slice(0, 19).replace(/[T:]/g, '-');
            return new Promise(resolve =>
                canvas.toBlob(
                    blob => resolve(new File([blob], `screenshot-${ts}.jpg`, { type: 'image/jpeg' })),
                    'image/jpeg', 0.92
                )
            );
        },
    });
});
