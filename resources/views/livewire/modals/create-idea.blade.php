<?php

use function Livewire\Volt\{state};
use function Livewire\Volt\rules;
use function Livewire\Volt\{mount};
use function Livewire\Volt\{computed};
use App\Models\Category;
use App\Models\LiveEventGallery;


state([
    'category_list'  => [],
    'categoryId' => '',
    'categories' => [],
    'name' => '',
    'modelId' => null,
    'description' => '',
    'category' => '',
    'date' => '2024-10-10'
]);


mount(function () {
    $this->category = Category::find($this->categoryId)->name;
    $this->category_list = Category::pluck('name', 'id')->toArray();
});

rules([
    'name' => 'required|min:2',
    'description' => 'nullable',
    'categories' => 'required',
]);

$save = function () {

    $liveEventGallery = LiveEventGallery::create($this->only(['name', 'description', 'date']));
    $liveEventGallery->categories()->sync([$this->categoryId]);
    if ($liveEventGallery) {
        $this->modelId = $liveEventGallery->id;
    }


//$data = $this->only(['name', 'description', 'date']);
//dd($data);

};

?>

<div>
@volt('create-idea')



<div class="">
    <div class="bg-white w-full p-4">
        <div class="flex items-center justify-between border-b pb-4 mb-4">
            <h2 class="text-xl font-bold">Create Idea for {{ $this->category }}</h2>
            <button wire:click="$dispatch('closeModal')" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>

        <div class="mb-6">



    <form wire:submit.prevent="save">
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

        @if ($this->modelId)
            <div wire:ignore>
                <x-ui.app.uppy :endpoint="route('upload', $this->modelId)">
                </x-ui.app.uppy>
            </div>
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
