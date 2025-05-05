@props([
    'label' => null,
    'id' => null,
    'name' => null,
    'rows' => 3,
    'required' => false,
    'placeholder' => '',
    'toolbar' => 'basic',
])
@php
    $wireModel = $attributes->get('wire:model');
    $id = $id ?? 'quill-' . uniqid();
    $height = 150; // Base height calculation
@endphp

<div x-data="{
    quill: null,
    content: @entangle($wireModel),
    toolbarOptions: {
        basic: [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link'],
            ['clean']
        ],
        full: [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'script': 'sub'}, { 'script': 'super' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            [{ 'direction': 'rtl' }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            [{ 'header': 1 }, { 'header': 2 }],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'font': [] }],
            [{ 'align': [] }],
            ['link', 'image', 'video'],
            ['clean']
        ]
    },
    initQuill() {
        // Clean up any existing instance
        if (this.quill) {
            this.quill = null;
            const editor = document.getElementById('{{ $id }}-editor');
            if (editor) {
                editor.innerHTML = '';
            }
        }

        // Initialize new Quill instance
        this.quill = new Quill(`#{{ $id }}-editor`, {
            theme: 'snow',
            placeholder: '{{ $placeholder }}',
            modules: {
                toolbar: this.toolbarOptions['{{ $toolbar }}']
            },
            bounds: `#{{ $id }}-editor-container`
        });

        // Set initial content
        if (this.content) {
            this.quill.root.innerHTML = this.content;
        }

        // Update Livewire model
        this.quill.on('text-change', () => {
            this.content = this.quill.root.innerHTML;
        });

        this.$watch('content', (value) => {
            if (value === '') {
                this.quill.root.innerHTML = '';
            }
        });

        // Set initial height
        this.setEditorHeight();
    },
    setEditorHeight() {
        const editor = document.querySelector(`#{{ $id }}-editor .ql-editor`);
        if (editor) {
            editor.style.minHeight = '{{ $height }}px';
            editor.style.maxHeight = '{{ $height * 3 }}px';
            editor.style.overflowY = 'auto';
        }
    },
    init() {
        this.initQuill();

        // Handle Livewire updates
        Livewire.hook('message.processed', () => {
            this.initQuill();
        });
    }
}"
x-init="init"
wire:ignore
>
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium leading-5 text-gray-700 dark:text-gray-300">
            {{ $label }}
        </label>
    @endif

    <div id="{{ $id }}-editor-container" class="mt-1.5 rounded-md border-1 shadow-sm">
        <!-- Hidden textarea for form submission -->
        <textarea
            id="{{ $id }}"
            name="{{ $name ?? '' }}"
            x-model="content"
            class="hidden"
            {{ $required ? 'required' : '' }}
        ></textarea>

        <!-- Quill editor container -->
        <div
            id="{{ $id }}-editor"
            class="bg-white dark:text-gray-300 dark:bg-white/[4%] border rounded-md border-gray-300 dark:border-white/10 focus:border-gray-300 dark:focus:border-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200/60 dark:focus:ring-white/20 @error($wireModel) border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red @enderror"
        ></div>
    </div>

    @error($wireModel)
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
