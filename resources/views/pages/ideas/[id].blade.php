<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Category;

name('ideas.show');
middleware(['auth', 'verified', 'can:access-admin-panel']);


new class extends Component
{
    use WithPagination;

    #[Url]
    public $sortBy = 'newest';

    public $id;
    public $category;

    public function mount()
    {

        $this->category = Category::find($this->id);
}

   #[Computed]
    public function liveEvents()
    {
//        $liveEvent = LiveEventGallery::findOrFail($this->id);
        $this->category = Category::find($this->id);


        $bargo = LiveEventGallery::query()->whereHas('categories', function ($query) {
            $query->where('id', $this->id);
        })->with('media')
            ->when($this->sortBy === 'newest', function ($query) {
                return $query->reorder()->orderBy('created_at', 'desc');
            })
            ->when($this->sortBy === 'oldest', function ($query) {
                return $query->reorder()->orderBy('created_at', 'asc');
            })
                          ->paginate(20);


        return $bargo;

    }


};

?>

<x-layouts.app>



    @volt('ideas.show')

    <div>
    <div class="flex justify-between items-center max-w-6xl mx-auto ">
        <h1 class="  mt-8 font-bold text-primary-700 text-2xl">Gallery</h1>

            <select wire:model.live="sortBy"
                wire:change="resetPage"
            class="border border-gray-300 dark:text-white  rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>

    @livewire('wire-elements-modal')


        <div class="flex justify-between items-center ">
            <div class="">
            <h2>{{ $category->name }}</h2>
                <p>{{ $category->description }}</p>
</div>
    <button wire:click="$dispatch('openModal', { component: 'modals.create-idea', arguments: { categoryId: {{ $id }} } })">
    + Add concept
</button>
</div>


    <div class="max-w-6xl mx-auto mb-10">




@if (isset($this->liveEvents) && $this->liveEvents->isNotEmpty())
    <div class="grid w-full lg:grid-cols-5 sm:grid-cols-2 gap-2 mt-8 max-w-6xl ">

    @foreach ($this->liveEvents as $key => $liveEvent)
                <div>
            <x-ui.card
                  :sortBy="$sortBy"
               :liveEventId="$liveEvent->id"
                 wire:key="img-{{ $liveEvent->id }}-{{ $sortBy }}-{{$key}}"
                :showComment="false" :key="$liveEvent->id"  :title="$liveEvent->name" :description="$liveEvent->date" :image="$liveEvent->getMedia('default')->first()?->getUrl()" :detailsUrl="route('live-event.show', ['id' => $liveEvent->id])" />
</div>
    @endforeach



</div>
   @if($this?->liveEvents?->hasPages())
    <div class="mt-4 ">
        {{ $this->liveEvents->links() }}
    </div>
    @endif

@endif


        <div class="comments">
        <livewire:comments :model="$this->category" />

</div>
</div>
</div>

    @endvolt


</x-layouts.app>



