<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Actions\VoteToggle;

name('gallery-index');
middleware(['auth', 'verified', 'can:access-admin-panel']);

new class extends Component {
    use WithPagination;

    #[Url]
    public $sortBy = 'newest';

    #[Url]
    public $categoryFilter = null;

    public $currentFilters = [];

    public function mount()
    {
    }

    public function filt($id)
    {
        if (in_array($id, $this->currentFilters)) {
            unset($this->currentFilters[array_search($id, $this->currentFilters)]);
        } else {
            $this->currentFilters[] = $id;
        }
    }

    #[Computed]
    public function categoryFilters()
    {
        return Category::query()->get()->pluck('name', 'id')->toArray();
    }

    public function deleteLiveEvent(int $id)
    {
        $liveEvent = LiveEventGallery::find($id);
        if ($liveEvent) {
            $liveEvent->delete();
        }

        return redirect()->route('gallery-index');
    }

    #[Computed]
    public function liveEvents()
    {
        $bargo = LiveEventGallery::with('media')
            ->withLikeCounts()
            ->when($this->categoryFilter && $this->categoryFilter != '0', function ($query) {
                return $query->whereHas('categories', function ($query) {
                    $query->where('id', $this->categoryFilter);
                });
            })
            ->when($this->sortBy === 'newest', function ($query) {
                return $query->reorder()->orderBy('created_at', 'desc');
            })
            ->when($this->sortBy === 'oldest', function ($query) {
                return $query->reorder()->orderBy('created_at', 'asc');
            })
            ->reorder()
            ->orderBy('order_column')
            ->paginate(20);

        return $bargo;
    }

    public function like(LiveEventGallery $model,  VoteToggle $action)
    {
        $action->handle($model, 'like');
    }

    public function dislike(LiveEventGallery $model,  VoteToggle $action)
    {
        $action->handle($model, 'dislike');
    }
};

?>

<x-layouts.app>



    @volt('gallery-index')

        <div>
            <div
                class="flex justify-between items-center max-w-6xl mx-auto ">
                <div>
                    <h1 class="  mt-8 pb-4 font-bold text-primary-700 text-2xl">Gallery</h1>
                    <div>
                        <div>

                            Category:
                            <select wire:model="categoryFilter" wire:change="resetPage"
                                class="border border-gray-300 dark:text-white  rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">

                                <option value="0">All</option>
                                @foreach ($this->categoryFilters as $key => $category)
                                    <option value="{{ $key }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>



                <select wire:model.live="sortBy" wire:change="resetPage"
                    class="border border-gray-300 dark:text-white  rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>


            @livewire('wire-elements-modal')
            <div class="max-w-6xl mx-auto mb-10">
                @if (isset($this->liveEvents) && $this->liveEvents->isNotEmpty())
                    <div class="grid w-full lg:grid-cols-5 sm:grid-cols-2 gap-2 mt-8 max-w-6xl ">
                        @foreach ($this->liveEvents as $key => $liveEvent)
                            <div class="relative group" wire:key="mcard-{{ $liveEvent->id }}-{{ $this->sortBy }}-{{ $this->categoryFilter }}-{{ now()->timestamp }}">
                                <div
                                    class="absolute z-20 top-0  group-hover:opacity-50 opacity-0 right-0 transition-opacity">


                                    <button x-data="{ showAlert: false }" @idea-updated.window="$wire.$refresh()"
                                        class="px-1 py-1 rounded-md text-white bg-primary-700  group-hover:opacity-30 hover:group-hover:opacity-100 transition-opacity"
                                        wire:click="$dispatch('openModal', { component: 'modals.edit-idea', arguments: { liveEvent: {{ $liveEvent }} } })">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-edit">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                                            <path
                                                d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                                            <path d="M16 5l3 3" />
                                        </svg>
                                    </button>
                                    <div>
                                        <button x-data
                                            @click="confirm('Are you sure you want to delete this item?') && $wire.deleteLiveEvent({{ $liveEvent->id }})"
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
                                <x-ui.card
                                    :sortBy="$sortBy" :categoryFilter="$categoryFilter" :model="$liveEvent" :categories="$liveEvent->categories->pluck('name', 'id')"
                                    :currentVote="$liveEvent->current_vote" :likesCount="$liveEvent->likes_count" :dislikesCount="$liveEvent->dislikes_count"
                                    :id="$liveEvent->id" :liveEventId="$liveEvent->id" :showComment="true" :title="$liveEvent->name"
                                    :description="$liveEvent->date" :image="$liveEvent
                                        ->getMedia('default')
                                        ->sortBy('order_column')
                                        ->first()
                                        ?->findVariant('thumbnail')
                                        ?->getUrl() ??
                                        $liveEvent->getMedia('default')->sortBy('order_column')->first()
                                            ?->video_thumbnail" :detailsUrl="route('live-event.show', ['id' => $liveEvent->id])" />
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex justify-center items-center h-64">
                        <h2 class="font-bold text-primary-700 text-2xl">No gallery found</h2>
                @endif

                @if ($this?->liveEvents?->hasPages())
                    <div class="mt-4" wire:key="pagination-{{ $this->sortBy }}">
                        {{ $this->liveEvents->links() }}
                    </div>
                @endif

            </div>
        </div>

        @script
            <script>
        document.addEventListener('livewire:initialized', () => {
    if (window.galleryRefreshHandler) {
        Livewire.off('refresh-gallery', window.galleryRefreshHandler);
    }

    window.galleryRefreshHandler = () => {
        setTimeout(() => {
            Livewire.dispatch('$refresh');
        }, 200);
    };

    Livewire.on('refresh-gallery', window.galleryRefreshHandler);
});

            </script>
        @endscript

    @endvolt


</x-layouts.app>
