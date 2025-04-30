@props([
    'endpoint' => route('upload', 0),
])


<div x-cloak x-data x-init="uppy = new Uppy.Uppy({
        autoProceed: false,
        allowMultipleUploads: true,
        debug: false,
        restrictions: {
            maxFileSize: 1 * 20024 * 20024, // 1 MB
            minNumberOfFiles: 1,
            maxNumberOfFiles: 500,
            allowedFileTypes: [
                'image/jpeg',
                'image/png',
                'image/jpg',
                'image/webp',
                'image/svg+xml',
                'video/mp4',
                'video/webm',
                'video/quicktime'
            ],
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

    $dispatch('upload-complete', {
        liveEvent: result
    });
});

uppy.on('file-added', (file) => {
    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/webp',
        'image/svg+xml',
        'video/mp4',
        'video/webm',
        'video/quicktime' // for .mov files
    ];

    if (!allowedTypes.includes(file.type)) {
        $dispatch('notice', { type: 'error', text: 'Invalid file format: images (jpg/png/webp) and videos (mp4/webm/mov) only' });
        uppy.removeFile(file.id);
    }
});">
    <div id="{{ $id ?? 'drag-drop-area' }}" x-ref="dropzone"></div>

    <button type="button" class="mt-2 px-4 py-2 bg-primary-600 text-white rounded" x-on:click="uppy.upload()">
        Upload Files
    </button>
</div>
