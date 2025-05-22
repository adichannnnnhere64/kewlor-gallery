<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use App\Models\Category;
use App\Models\Media;
use Livewire\WithPagination;

name('live-event.edit');
middleware(['auth', 'verified', 'can:access-admin-panel']);

new class extends Component {
    use WithPagination;
    public $id;
    public $name;
    public $slug;
    public $categories;
    public $description;
    public $categoryForm;
    public $date;
    public $images;

    public function mount($id): void
    {
        $this->id = $id;
        $this->categoryForm = Category::get()->pluck('name', 'id')->toArray();
        $liveEvent = LiveEventGallery::with('categories')->find($this->id);
        $this->images = $liveEvent?->getMedia('default');

        if ($liveEvent) {
            $this->name = $liveEvent->name;
            $this->date = $liveEvent->date;
            $this->description = $liveEvent->description;
            $this->categories = $liveEvent->categories->pluck('id')->toArray();
            $this->slug = $liveEvent->slug;
        }
    }

    public function with(): array
    {
        return [
            'live_event' => LiveEventGallery::query()->find($this->id),
        ];
    }

    public function deleteImage(int $id): void
    {
        $image = Media::find($id);
        $image->delete();

        $liveEvent = LiveEventGallery::find($this->id);
        $this->images = $liveEvent->getMedia('default');

        session()->flash('message', 'Image deleted successfully!');
    }

    public function reload(): void
    {
        $liveEvent = LiveEventGallery::find($this->id);
        $this->images = $liveEvent->getMedia('default');
    }

    public function submit(): void
    {
        // Validate the input
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable',
            'date' => 'required|date',
            'slug' => 'nullable|min:1|max:100',
        ]);

        $liveEvent = LiveEventGallery::find($this->id);
        if ($liveEvent) {
            $liveEvent->update([
                'name' => $this->name,
                'date' => $this->date,
                'description' => $this->description,
                'slug' => $this->slug,
            ]);

            $liveEvent->categories()->sync($this->categories);

            session()->flash('message', 'Event updated successfully!');
            #$this->dispatch('eventUpdated');
        }
    }
};

#with(fn () => ['posts' => 'adicchans']);

?>

<style>
    .dark img[alt="Kewlor Logo"] {
        filter: invert(1);
    }
</style>

<x-layouts.app>

    <x-slot name="header">
        <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Edit Live Event') }}
        </h2>
    </x-slot>


    @volt('live-event.edit')

        <div>
            <form wire:submit.prevent="submit">

                @if (session()->has('message'))
                    <div class="p-4 mb-4 text-green-700 bg-green-100 rounded">
                        {{ session('message') }}
                    </div>
                @endif

                <x-ui.input wire:model="name" label="name" id="name" name="name" />
                <x-ui.input wire:model="date" label="Date" id="date" name="date" type="date" />
                <x-ui.textarea label="Description" id="description" name="description" wire:model="description"
                    rows="5" placeholder="Enter your description here..." />
                <x-ui.select :options="$categoryForm" :selected="$categories" wireModel="categories" label="Categories" id="tags"
                    name="tags" />
                <x-ui.input wire:model="slug" label="Slug" id="slug" name="slug" type="slug" />
                <div class="my-4 ">
                    <button type="submit" class="mt-2 px-4 py-2 bg-primary-600 text-white rounded">
                        Update
                    </button>
                </div>

                <h1>Add images</h1>

                <div @upload-complete.window="$wire.reload()">


                    <div wire:ignore>
                        <x-ui.app.uppy :modelId="$id" :endpoint="route('upload', $id)">
                        </x-ui.app.uppy>
                    </div>

                    <div>
                        @if (isset($images) && $images->isNotEmpty())
                            <div class="flex my-4 flex-row flex-wrap gap-4">
                                @foreach ($images as $image)
                                    <div class="flex justify-center flex-col items-center">
                                        <img

   src="{{ $image->findVariant('thumbnail')?->getUrl() ?? $image?->video_thumbnail }}"


                                            alt="{{ $image->name }}" class="w-34 h-34 rounded-lg object-cover" />
                                        <button wire:confirm="Are you sure you want to delete this image?"
                                            wire:click="deleteImage({{ $image->id }})"
                                            class="cursor-pointer text-red-600 bg-red-200 p-1 rounded-lg mt-2 ">delete</button>
                                    </div>
                                @endforeach

                            </div>
                        @endif

                    </div>
            </form>
        </div>

    @endvolt

</x-layouts.app>
