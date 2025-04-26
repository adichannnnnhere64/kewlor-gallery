<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

name('gallery-index');
middleware(['auth', 'verified', 'can:access-admin-panel']);

new class extends Component
{
    use WithPagination;

    #[Url]
    public $sortBy = 'newest';

    public $categoryFilters;

    public $currentFilters = [];

public function mount()
    {
        $this->categoryFilters = Category::query()->get()->pluck('name', 'id')->toArray();
    }

    public function addFilter($id)
    {

        if (in_array($id, $this->currentFilters)) {
            unset($this->currentFilters[array_search($id, $this->currentFilters)]);
        } else {
            $this->currentFilters[] = $id;
        }

    }

   #[Computed]
    public function liveEvents()
    {

//        $liveEvent = LiveEventGallery::findOrFail($this->id);

        $bargo = LiveEventGallery::with('media')
            ->withLikeCounts()
            ->withCount('comments')
            ->when($this->currentFilters, function ($query) {

                $query->whereHas('categories', function ($query) {
                    $query->whereIn('id', $this->currentFilters);
                });

            })
            ->when($this->sortBy === 'newest', function ($query) {
                return $query->reorder()->orderBy('date', 'desc');
            })
            ->when($this->sortBy === 'oldest', function ($query) {
                return $query->reorder()->orderBy('date', 'asc');
            })
                          ->paginate(20);


        return $bargo;

    }


};

?>

<x-layouts.app>



    @volt('gallery-index')

    <div>
    <div class="flex justify-between items-center max-w-6xl mx-auto ">
            <div>
        <h1 class="  mt-8 pb-4 font-bold text-primary-700 text-2xl">Gallery</h1>
                <div>
                    @if (isset($this->categoryFilters) && count($this->categoryFilters))

                        @foreach ($this->categoryFilters as $key => $category)
                            <button :key="{{ $key }}" wire:key="{{ $key }}" class=" {{ in_array($key, $this->currentFilters) ? 'bg-primary-700' : 'bg-gray-400'  }} rounded-full text-white px-3 py-2 "  wire:click="addFilter({{ $key }})"> {{ $category }}</button>
                        @endforeach

                    @endif
                </div>
</div>
            <select wire:model.live="sortBy"

                wire:change="resetPage"
            class="border border-gray-300 dark:text-white  rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>

    <div class="max-w-6xl mx-auto mb-10">
@if (isset($this->liveEvents) && $this->liveEvents->isNotEmpty())
    <div class="grid w-full lg:grid-cols-5 sm:grid-cols-2 gap-2 mt-8 max-w-6xl ">
    @foreach ($this->liveEvents as $key => $liveEvent)
                <div>
            <x-ui.card
                  :sortBy="$sortBy"
                 :model="$liveEvent"
                :categories="$liveEvent->categories->pluck('name', 'id')"
                :currentVote="$liveEvent->current_vote"
                :likesCount="$liveEvent->likes_count"
                :dislikesCount="$liveEvent->dislikes_count"
                :commentsCount="$liveEvent->comments_count"

                            :id="$liveEvent->id"
               :liveEventId="$liveEvent->id"
                 wire:key="imgx-{{ $liveEvent->id }}-{{ $sortBy }}-{{$key}}"
                :showComment="true" :key="$liveEvent->id"  :title="$liveEvent->name" :description="$liveEvent->date" :image="$liveEvent->getMedia('default')->first()?->getUrl()" :detailsUrl="route('live-event.show', ['id' => $liveEvent->id])" />
</div>
    @endforeach



</div>

@else
<div class="flex justify-center items-center h-64">
    <h2 class="font-bold text-primary-700 text-2xl">No gallery found</h2>


@endif

   @if($this?->liveEvents?->hasPages())
    <div class="mt-4 ">
        {{ $this->liveEvents->links() }}
    </div>


    @endif


</div>
</div>

    @endvolt


</x-layouts.app>



