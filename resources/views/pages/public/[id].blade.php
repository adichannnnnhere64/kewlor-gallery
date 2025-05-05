<?php

use Illuminate\Support\Facades\Http;
use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use App\Models\Media;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\On;

name('live-event.show');
middleware(['auth', 'verified', 'can:access-admin-panel']);

new class extends Component {
    use WithPagination;

    public $id;
    public $name;
    public $date;
    public $description;

    public $liveEvent;
    #[Url]
    public $type = 'all';

    public function mount()
    {
        $this->liveEvent = LiveEventGallery::findOrFail($this->id);

        $this->name = $this->liveEvent->name;
        $this->description = $this->liveEvent->description;
    }

    #[On('idea-updated')]
    public function updatePostList($liveEvent)
    {
        $this->name = $liveEvent['liveEvent']['name'];
        $this->description = $liveEvent['liveEvent']['description'];
    }

    #[On('upload-complete')]
    public function uploadComplete($liveEvent)
    {
        $this->dispatch('$refresh');
    }

    public function fetchImages()
    {
        $liveEvent = LiveEventGallery::findOrFail($this->id);
        $this->images = $liveEvent->media()->withLikeCounts()->withPivot('tag')->where('tag', 'default')->reorder()->orderBy('order_column')->paginate(20);
    }

    #[Computed]
    public function images()
    {
        $liveEvent = LiveEventGallery::findOrFail($this->id);

        $bargo = $liveEvent->media()
            ->withLikeCounts()
            ->withPivot('tag')
            ->where('tag', 'default')
            ->reorder()
            ->when($this->type && $this->type != 'all', function ($query) {
                    $query->where('aggregate_type', $this->type);
            })
            ->orderBy('order_column')
            ->paginate(20);

        return $bargo;
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->dispatch('$refresh');
    }

    public function deleteImage(int $id)
    {
        Media::find($id)->delete();
    }

    public function updateOrder(Media $media, $item)
    {
        $originalIds = collect($this->images->items())->pluck('id');
        $updated = $originalIds->reject(fn($id) => $id == $media->id);
        $updated->splice($item, 0, [$media->id]);
        Media::setNewOrder($updated->toArray());
        $this->fetchImages();
    }
};
?>

<style>
    .dark img[alt="Kewlor Logo"] {
        filter: invert(1);
    }

    .lightbox-overlay {
        /* Your existing lightbox styles */
    }
</style>

<x-layouts.app>
    @volt('live-event.show')
        <div>

            @livewire('wire-elements-modal')
            <div class="flex max-w-6xl mx-auto justify-between items-center  py-4">
                <div class="w-20">
                    <x-ui.button tag="a" type="secondary" href="{{ route('gallery-index') }}" class="mb-8 inline">
                        <div class="flex items-center space-x-1 ">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M5 12l14 0" />
                                <path d="M5 12l6 6" />
                                <path d="M5 12l6 -6" />
                            </svg>

                            <span>Back</span>
                        </div>
                    </x-ui.button>
                </div>

                <div class="">

                </div>
            </div>


            <div x-data="{ handle: (item, position) => $wire.updateOrder(item, position) }" class="mx-auto max-w-6xl">

                <div class="flex md:flex-row flex-col space-y-2 justify-between items-center">

                    <div class="mr-8">
                        <h1 class=" font-bold text-primary-700 text-2xl">{{ $name }}</h1>
                        <p class="text-gray-400">{{ $description }}</p>
                    </div>

                    <div class="flex flex-col  space-x-2 space-y-4">
                <select wire:model.live="type"
                    class="border border-gray-300 dark:text-white -mt-10  rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">All</option>
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                    <option value="audio">Audio</option>
                </select>



                    <div class="flex space-x-2 items-center">
                        <button class="w-34 bg-primary-700 hover:bg-primary-800 text-white font-bold py-2 px-4 rounded"
                            wire:click="$dispatch('openModal', { component: 'modals.add-image-in-live-event', arguments: { liveEventId: {{ $id }} } })">
                            + Add Image
                        </button>
                        <a class="bg-orange-700 hover:bg-orange-800 text-white font-bold py-2 px-4 rounded" target="_blank"
                            href="{{ route('live-event.edit', ['id' => $id]) }}">Edit </a>

                    </div>
                    </div>


                </div>
                <div x-sort="handle" x-on:sorted="$wire.updateOrder($event)"
                    class="grid w-full lg:grid-cols-5 sm:grid-cols-2 gap-2 mt-8  ">

                    @foreach ($this->images ?? [] as $key => $image)
                        <div x-sort:item="{{ $image->id }}" class="" wire:key="{{ $image->id }}">
                            <div class="relative group"">
                                <div
                                    class="absolute z-20 top-0  group-hover:opacity-50 opacity-0 right-0 transition-opacity">
                                    <button x-data
                                        @click="confirm('Are you sure you want to delete this item?') && $wire.deleteImage({{ $image->id }})"
                                        class="px-1 py-1 rounded-md text-white bg-red-700 group-hover:opacity-30 hover:group-hover:opacity-100 transition-opacity">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M4 7l16 0" />
                                            <path d="M10 11l0 6" />
                                            <path d="M14 11l0 6" />
                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                        </svg>
                                    </button>
                                </div>

                                <x-ui.card-image :model="$image" :liveEventId="$id" :sortBy="$type" :likesCount="$image->likes_count"
                                    :currentVote="$image->current_vote" :dislikesCount="$image->dislikes_count" :key="$image->id" :id="$image->id"
                                     :image="$image?->findVariant('thumbnail')?->getUrl() ?? $image?->video_thumbnail" :showComment="true" :description="$date"
                                    :detailsUrl="route('public.image.show', ['id' => $image->id])" />

                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($this?->images?->hasPages())
                    <div class="mt-4 ">
                        {{ $this->images->links() }}
                    </div>
                @endif

                <div class="my-4"></div>


                <div class="comments">
            <x-notes.note
                    :model="$this->liveEvent"
                />
                </div>
            </div>

        </div>
    @endvolt
</x-layouts.app>
