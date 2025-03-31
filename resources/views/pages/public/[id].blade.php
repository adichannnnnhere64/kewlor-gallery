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

    #[Computed]
    public function images()
    {
        $liveEvent = LiveEventGallery::findOrFail($this->id);

        $bargo = $liveEvent->media()
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
        <div class="flex max-w-6xl mx-auto justify-between items-center px-8">
            <h1 class="mt-8 font-bold text-primary-700 text-2xl">{{ $name }}</h1>

            <div class="mt-8">
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
        <div class="grid w-full lg:grid-cols-5 sm:grid-cols-2 gap-2 mt-8  px-8">
        @foreach ($this->images ?? [] as $key => $image)
            <div>

            <x-ui.card :sortBy="$sortBy"
              wire:key="img-{{ $image->id }}-{{ $sortBy }}-{{ now()->timestamp }}"
            :id="$image->id" :commentsCount="$image->comments_count" :image="$image?->findVariant('thumbnail')?->getUrl()" :showComment="true" :title="$name" :description="$date" :detailsUrl="route('public.image.show', ['id' => $image->id])"  />
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
