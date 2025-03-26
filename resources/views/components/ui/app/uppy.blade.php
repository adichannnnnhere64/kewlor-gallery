@props([
    'endpoint' => route('upload', 0)
])

@assets
    <link href="https://releases.transloadit.com/uppy/v4.13.3/uppy.min.css" rel="stylesheet" />
    <script src="https://releases.transloadit.com/uppy/v4.13.3/uppy.min.js"></script>
@endassets

<div
    x-cloak
    x-data
    x-init="
        uppy = new Uppy.Uppy({
            autoProceed: false,
            allowMultipleUploads: true,
            debug: false,
            restrictions: {
                maxFileSize: 1 * 20024 * 20024, // 1 MB
                minNumberOfFiles: 1,
                maxNumberOfFiles: 500,
                allowedFileTypes: ['image/*', 'image/svg+xml'],
            },
        })
        .use(Uppy.ImageEditor)
        .use(Uppy.Dashboard, {
            hideUploadButton: true,
            height: 320,
            width: '100%',
            inline: true,
            target: $refs.dropzone,
            replaceTargetContent: true,
            showProgressDetails: true,
            browserBackButtonClose: true,
            note: 'Images only, 2–3 files, up to 1 MB',
        })
        .use(Uppy.XHRUpload, {
            endpoint: '{{ $endpoint }}',
            formData: true,
            fieldName: 'file',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                Accept: 'Application/JSON',
            },
        });

        uppy.on('complete', (result) => {
            $dispatch('reload');
            setTimeout(() => uppy.reset(), 2500);
        });

        uppy.on('file-added', (file) => {
            if (!['image/jpeg', 'image/png', 'image/jpg', 'image/svg+xml'].includes(file.type)) {
                $dispatch('notice', { type: 'error', text: 'Image format invalid: jpg/png only' });
                uppy.removeFile(file.id);
            }
        });
    "
>
    <div id="{{ $id ?? 'drag-drop-area' }}" x-ref="dropzone"></div>

    <button type="button" class="mt-2 px-4 py-2 bg-primary-600 text-white rounded" x-on:click="uppy.upload()">
        Upload Files
    </button>
</div>
