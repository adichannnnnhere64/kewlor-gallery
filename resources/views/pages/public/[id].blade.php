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

new class extends Component {
    use WithPagination;

    public $id;
    public $name;
    public $date;
    #[Url]
    public $sortBy = 'newest';

    public function mount()
    {
        $liveEvent = LiveEventGallery::findOrFail($this->id);
        $this->name = $liveEvent->name;
    }

    #[Computed]
    public function images()
    {
        $liveEvent = LiveEventGallery::findOrFail($this->id);

        $bargo = $liveEvent->media()
            ->withLikeCounts()
            ->withPivot('tag')
            ->withCount('comments')
            ->where('tag', 'default')
            ->when($this->sortBy === 'newest', function ($query) {
                return $query->reorder()->orderBy('created_at', 'desc');
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


    public function refresh()
    {
        dd('ad');
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

<x-layouts.marketing>
    @volt('live-event.show')
    <div>
        <x-ui.marketing.breadcrumbs :crumbs="[['text' => $name]]" />
        <div class="flex max-w-6xl mx-auto justify-between items-center px-8 py-4">
            <div class="w-20">
    <x-ui.button tag="a" type="secondary"  href="{{ route('home') }}" class="mb-8 inline">
    <div class="flex items-center space-x-1 ">
    <svg  xmlns="http://www.w3.org/2000/svg" class="h-4"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>

    <span>Back</span>
    </div>
    </x-ui.button>
</div>

            <div class="">
            <select wire:model.live="sortBy"
                wire:change="resetPage"
            class="border border-gray-300 dark:text-white  rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="likes">Most Likes</option>
                    <option value="comments">Most Comments</option>
                </select>
            </div>
        </div>


             <div class="mx-auto max-w-6xl">

            <h1 class=" px-8 font-bold text-primary-700 text-2xl">{{ $name }}</h1>
        <div class="grid w-full lg:grid-cols-5 sm:grid-cols-2 gap-2 mt-8  px-8">

        @foreach ($this->images ?? [] as $key => $image)
            <div>
            <x-ui.card
            :liveEventId="$id"
            :sortBy="$sortBy"
            :likesCount="$image->likes_count"
            :currentVote="$image->current_vote"
            :dislikesCount="$image->dislikes_count"
            wire:key="img-{{ $image->id }}-{{ $sortBy }}-{{ now()->timestamp }}"
            :id="$image->id" :commentsCount="$image->comments_count" :image="$image?->findVariant('thumbnail')?->getUrl() ?? $image->video_thumbnail" :showComment="true" :title="$name" :description="$date" :detailsUrl="route('public.image.show', ['id' => $image->id])"  />
            </div>

    @endforeach
</div>

    @if($this?->images?->hasPages())
    <div class="mt-4 px-8">
        {{ $this->images->links() }}
    </div>
    @endif


    </div>

    </div>
    @endvolt
</x-layouts.marketing>
