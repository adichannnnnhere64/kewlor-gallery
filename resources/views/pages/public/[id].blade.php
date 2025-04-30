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
    public $sortBy = 'newest';

    public function mount()
    {
        $this->liveEvent = LiveEventGallery::findOrFail($this->id);

        $this->name = $this->liveEvent->name;
        $this->description = $this->liveEvent->description;
    }

    #[Computed]
    public function images()
    {
        $liveEvent = LiveEventGallery::findOrFail($this->id);

        $bargo = $liveEvent
            ->media()
            ->withLikeCounts()
            ->withPivot('tag')
            ->withCount('comments')
            ->where('tag', 'default')
            ->when($this->sortBy === 'newest', function ($query) {
                return $query->reorder()->orderBy('created_at', 'desc');
            })
            ->when($this->sortBy === 'likes', function ($query) {
                return $query->reorder()->orderByDesc('likes_count');
            })
            ->when($this->sortBy === 'oldest', function ($query) {
                return $query->reorder()->orderBy('created_at', 'asc');
            })
            ->when($this->sortBy === 'comments', function ($query) {
                return $query->reorder()->orderBy('comments_count', 'desc');
            })
            ->paginate(20);

        return $bargo;

        //   $this->dispatch('$refresh');
    }

    public function deleteImage(int $id)
    {
        Media::find($id)->delete();
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
                    <select wire:model.live="sortBy" wire:change="resetPage"
                        class="border border-gray-300 dark:text-white  rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="likes">Most Likes</option>
                        <option value="comments">Most Comments</option>
                    </select>
                </div>
            </div>


            <div class="mx-auto max-w-6xl">

                <div class="flex justify-between items-center">
                    <div>
                        <h1 class=" font-bold text-primary-700 text-2xl">{{ $name }}</h1>
                        <p class="text-gray-400">{{ $description }}</p>
                    </div>

                    <button>+ Add image</button>
                </div>
                <div class="grid w-full lg:grid-cols-5 sm:grid-cols-2 gap-2 mt-8  ">

                    @foreach ($this->images ?? [] as $key => $image)
                        <div wire:key="{{ $image->id }}">
                            <div class="relative group"">
                                <div
                                    class="absolute z-20 top-0  group-hover:opacity-50 opacity-0 right-0 transition-opacity">
                                    <div>
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
                                </div>

                                <x-ui.card-image :model="$image" :liveEventId="$id" :sortBy="$sortBy" :likesCount="$image->likes_count"
                                    :currentVote="$image->current_vote" :dislikesCount="$image->dislikes_count" :key="$image->id" :id="$image->id"
                                    :commentsCount="$image->comments_count" :image="$image?->findVariant('thumbnail')?->getUrl() ?? $image->video_thumbnail" :showComment="true" :description="$date"
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
                    <livewire:comments :model="$this->liveEvent" />
                </div>
            </div>

        </div>
    @endvolt
</x-layouts.app>
