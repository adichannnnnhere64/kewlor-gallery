<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

name('home');

new class extends Component
{
    use WithPagination;

    #[Url]
    public $sortBy = 'newest';

   #[Computed]
    public function liveEvents()
    {
//        $liveEvent = LiveEventGallery::findOrFail($this->id);

        $bargo = LiveEventGallery::query()->with('media')
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

<x-layouts.marketing>


    <x-ui.marketing.breadcrumbs :crumbs="[]" />


    @volt('home')

    <div>
    <div class="flex justify-between items-center max-w-6xl mx-auto px-8">
        <h1 class="  mt-8 font-bold text-primary-700 text-2xl">Gallery</h1>
            <select wire:model.live="sortBy"
                wire:change="resetPage"
            class="border border-gray-300 dark:text-white  rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>

    <div class="max-w-6xl mx-auto mb-10">
@if (isset($this->liveEvents) && $this->liveEvents->isNotEmpty())
    <div class="grid w-full lg:grid-cols-5 sm:grid-cols-2 gap-2 mt-8 max-w-6xl px-8">
    @foreach ($this->liveEvents as $key => $liveEvent)
                <div>
            <x-ui.card
                  :sortBy="$sortBy"
                 wire:key="img-{{ $liveEvent->id }}-{{ $sortBy }}-{{$key}}"
                :showComment="false" :key="$liveEvent->id"  :title="$liveEvent->name" :description="$liveEvent->date" :image="$liveEvent->getMedia('default')->first()?->getUrl()" :detailsUrl="route('live-event.show', ['id' => $liveEvent->id])" />
</div>
    @endforeach



</div>
   @if($this?->liveEvents?->hasPages())
    <div class="mt-4 px-8">
        {{ $this->liveEvents->links() }}
    </div>
    @endif

@endif
</div>
</div>

    @endvolt


</x-layouts.marketing>



