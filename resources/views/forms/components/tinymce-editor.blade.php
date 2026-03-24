<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $id = 'tinymce_' . str_replace('.', '_', $getId());
        $statePath = $getStatePath(); // e.g. "data.content" or "content"
    @endphp

    <div class="w-full">
        {{-- ✅ This hidden input is what Filament will actually dehydrate + persist --}}
        <input
            type="hidden"
            name="{{ $statePath }}"
            x-ref="mirror"
            data-tinymce-mirror="{{ $id }}"
            value="{{ $getState() }}"
        />

        {{-- ✅ TinyMCE UI --}}
        <div wire:ignore>
            <textarea
                id="{{ $id }}"
                class="block w-full rounded-lg border-gray-300 shadow-sm"
            >{{ $getState() }}</textarea>
        </div>
    </div>
</x-dynamic-component>

<script src="/js/tinymce/tinymce.min.js"></script>

<script>
(function () {
    const editorId = @js($id);

    function getToolbarAndPlugins(profile) {
        let toolbar = 'undo redo | blocks | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image media table | code fullscreen | help';
        let plugins = ['advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media', 'table', 'help', 'wordcount'];

        if (profile === 'simple') {
            toolbar = 'undo redo | bold italic | bullist numlist | link | code';
            plugins = ['link', 'lists', 'code'];
        } else if (profile === 'minimal') {
            toolbar = 'bold italic | link | code';
            plugins = ['link', 'code'];
        }

        return { toolbar, plugins };
    }

    function init() {
        if (typeof tinymce === 'undefined') return;
        if (tinymce.get(editorId)) return;

        const profile = @js(method_exists($field, 'getProfile') ? $field->getProfile() : null);
        const height  = @js(method_exists($field, 'getHeight') ? $field->getHeight() : 420);
        const uploadDir = @js(method_exists($field, 'getFileAttachmentsDirectory') ? $field->getFileAttachmentsDirectory() : null);

        const { toolbar, plugins } = getToolbarAndPlugins(profile);

        const mirror = document.querySelector(`[data-tinymce-mirror="${editorId}"]`);
        const textarea = document.getElementById(editorId);
        if (!mirror || !textarea) return;

        // Find the nearest form (Filament save is a real submit)
        const form = textarea.closest('form');

        // Helper: copy TinyMCE -> hidden input
        const syncToMirror = () => {
            const ed = tinymce.get(editorId);
            if (!ed) return;

            // Flush typing buffer
            ed.save();

            // Copy to mirror (this is what persists)
            mirror.value = ed.getContent();

            // Trigger input event so any listeners see it
            mirror.dispatchEvent(new Event('input', { bubbles: true }));
            mirror.dispatchEvent(new Event('change', { bubbles: true }));
        };

        tinymce.init({
            selector: '#' + editorId,
            license_key: 'gpl',
            height: height,
            menubar: true,
            plugins: plugins,
            toolbar: toolbar,
            promotion: false,
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',

            // Allow embeds (Wufoo, etc.)
            valid_elements: '*[*]',
            extended_valid_elements: 'script[src|async|defer|type],iframe[src|width|height|frameborder|allowfullscreen|allow]',
            verify_html: false,

            ...(uploadDir ? {
                automatic_uploads: true,
                file_picker_types: 'image',
                images_upload_handler: function (blobInfo) {
                    return new Promise((resolve, reject) => {
                        const formData = new FormData();
                        formData.append('file', blobInfo.blob(), blobInfo.filename());
                        formData.append('directory', uploadDir);

                        fetch('/tinymce/upload', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            },
                        })
                        .then(r => r.json())
                        .then(result => result.location ? resolve(result.location) : reject('Upload failed'))
                        .catch(() => reject('Upload failed'));
                    });
                },
            } : {}),

            setup(editor) {
                editor.on('init', () => {
                    // Ensure mirror matches initial state
                    mirror.value = editor.getContent();
                });

                // Optional: keep mirror updated as you type (not required for saving, but helps)
                editor.on('keyup change undo redo', () => {
                    syncToMirror();
                });

                // ✅ The only thing that truly matters:
                // Before submit, copy editor content into the hidden input.
                if (form && !form.dataset.tinymceMirrorBound) {
                    form.dataset.tinymceMirrorBound = '1';

                    form.addEventListener('submit', () => {
                        // Make absolutely sure we capture final content
                        syncToMirror();
                    }, true);
                }
            },
        });
    }

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('livewire:navigated', init);
})();
</script>