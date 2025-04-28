@props(['image' => null])

<div>
    <!-- Drag and drop area -->
    <div
        x-data="{
            isDragging: false,
            handleDrop(e) {
                e.preventDefault();
                this.isDragging = false;
                if (e.dataTransfer.files.length) {
                    @this.upload('image', e.dataTransfer.files[0]);
                }
            }
        }"
        x-on:dragover.prevent="isDragging = true"
        x-on:dragleave.prevent="isDragging = false"
        x-on:drop="handleDrop($event)"
        class="h-40 border-2 border-dashed rounded-lg p-6 text-center transition-all duration-200"
        :class="isDragging ? 'border-primary bg-primary/10' : 'border-gray-300 hover:border-gray-400'"
    >
        <div class="space-y-2">
            <div class="flex justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" style="height:100px!important;" class=" text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="text-sm text-gray-600">
                <label for="image" class="relative cursor-pointer rounded-md font-medium text-primary hover:text-primary-dark focus-within:outline-none">
                    <span>Upload a file</span>
                    <input
                        wire:model="image"
                        id="image"
                        name="image"
                        type="file"
                        class="sr-only"
                        accept="image/*"
                    >
                </label>
                <p class="pl-1">or drag and drop</p>
            </div>
            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
        </div>
    </div>

    <!-- Error message -->
    @error('image')
        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
    @enderror

    <!-- Preview image -->
    @if ($image)
        <div class="mt-4">
            <div class="relative w-32 h-32 rounded overflow-hidden">
                <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview">
                <button
                    wire:click="$set('image', null)"
                    type="button"
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 focus:outline-none"
                    title="Remove image"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Loading indicator -->
    <div wire:loading wire:target="image" class="mt-2">
        <span class="text-sm text-gray-500">Uploading...</span>
    </div>
</div>
