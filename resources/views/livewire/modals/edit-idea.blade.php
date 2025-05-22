<?php

use function Livewire\Volt\{state};
use function Livewire\Volt\rules;
use function Livewire\Volt\{mount};
use function Livewire\Volt\{computed};
use App\Models\Category;
use App\Models\LiveEventGallery;


state([
    'category_list'  => [],
    'liveEvent' => null,
    'categories' => [],
    'name' => '',
    'modelId' => null,
    'description' => '',
    'category' => '',
    'date' => '2024-10-10'
]);


mount(function () {
    $this->category_list = Category::pluck('name', 'id')->toArray();
    $this->description = $this->liveEvent->description;
    $this->name = $this->liveEvent->name;
});

rules([
    'name' => 'required|min:2',
    'description' => 'nullable',
    'categories' => 'required',
]);

$update = function () {
    $this->liveEvent->update($this->only(['name', 'description']));
    $this->dispatch('idea-updated', id: $this->liveEvent->id);
};

?>

<div>
@volt('edit-idea')



<div class="">
    <div class="bg-white w-full p-4">
        <div class="flex items-center justify-between border-b pb-4 mb-4">
            <h2 class="text-xl font-bold">Edit Idea for {{ $this->liveEvent->name ?? '' }}</h2>
            <button wire:click="$dispatch('closeModal')" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>

        <div class="mb-6">

                <a href="/live-event/{{ $this->liveEvent->id }}" class="text-blue-500 hover:text-blue-700"> Edit </a>



    <form wire:submit.prevent="update">
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" id="name" wire:model="name" class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            @error('name')
                <span class="text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <x-ui.textarea
                label="Description"
                id="description"
                name="description"
                wire:model="description"
                rows="5"
                placeholder="Enter your description here..."
                />
            @error('description')
                <span class="text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>



         <div>
            <button type="submit" class="w-full px-4 py-2 text-white transition duration-200 bg-indigo-600 rounded-md hover:bg-indigo-700">
                Save
            </button>
        </div>

        @if ($this->liveEvent->id)
            <div wire:ignore>
                <x-ui.app.uppy :model-id="$this->liveEvent->id" :endpoint="route('upload', $this->liveEvent->id)">
                </x-ui.app.uppy>
            </div>

            <x-gallery-thumbnail :thumbnails="$this->liveEvent->gallery" :liveEventId="$this->liveEvent->id" />
        @endif

    </form>

        </div>

        <div class="flex justify-end space-x-2">
           <button wire:click="$dispatch('closeModal')">
                Close
            </button>
        </div>
    </div>
</div>
@endvolt
</div>
