<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use Livewire\WithPagination;

name('live-event.show');

new class extends Component {

        use WithPagination;
       public $id;
    public $name;
    public $date;
    public $images;

        public function mount($id): void
    {
        $this->id = $id;
        $liveEvent = LiveEventGallery::find($this->id);
        $this->images = $liveEvent->getMedia('default');

        if ($liveEvent) {
            $this->name = $liveEvent->name;
            $this->date = $liveEvent->date;
        }
    }

        public function with(): array
    {
        return [
            'live_event' => LiveEventGallery::query()->find($this->id),
            'name' => $this->name
        ];
    }

};


#with(fn () => ['posts' => 'adicchans']);

?>

<style>
    .dark img[alt="Kewlor Logo"] {
        filter: invert(1);
    }
</style>

<x-layouts.marketing>

    @volt('live-event.show')

    <div>

    <x-ui.marketing.breadcrumbs :crumbs="[['text' => $name]]" />
    <div class="flex max-w-6xl mx-auto  justify-start px-8">
        <h1 class="mt-8 font-bold text-primary-700 text-2xl">{{ $name }}</h1>
    </div>

    <div class="relative max-w-6xl mx-auto items-center  w-full h-auto overflow-hidden" x-cloak
         x-data="{ isOpen: false, currentImage: '' }">




        <div class="grid w-full lg:grid-cols-6 sm:grid-cols-2 gap-2 mt-8 max-w-6xl px-8">
            @if ($images->count() > 0)
            @foreach ($images as $image)
                    <x-ui.card-image :showComment="true" :key="$image->id" :id="$image->id" :detailsUrl="route('public.image.show', ['id' => $image->id])" :image="$image->getUrl()" />
            @endforeach
                @else

                <p>No images found..</p>

                @endif

        </div>



            <div x-show="isOpen" class="lightbox-overlay" >
                <div class="lightbox-content" @click.outside="isOpen = false">
                    <button class="close-btn" @click="isOpen = false">×</button>
                    <img x-bind:src="currentImage" alt="Full size image" class="lightbox-image">
                </div>
            </div>

    </div>
    @endvolt
</div>
</x-layouts.marketing>
